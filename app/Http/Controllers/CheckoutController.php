<?php

namespace App\Http\Controllers;

use App\Models\Seat;
use App\Models\Ticket;
use App\Models\Trip;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function show(Request $request, Trip $trip): View|RedirectResponse
    {
        $selectionToken = $this->selectionToken($request, $trip);

        $seatIds = $this->selectedSeatIds($request, $trip, $selectionToken);

        if ($seatIds->isEmpty()) {
            return redirect()
                ->route('connections')
                ->with('error', 'Najpierw wybierz miejsca.');
        }

        $trip->load([
            'train',
            'departureCity',
            'arrivalCity',
        ]);

        $seats = Seat::query()
            ->with('wagon')
            ->whereIn('id', $seatIds)
            ->whereHas('wagon', function ($query) use ($trip) {
                $query->where('trip_id', $trip->id);
            })
            ->get()
            ->sortBy([
                ['wagon.number', 'asc'],
                ['seat_number', 'asc'],
            ])
            ->values();

        if ($seats->count() !== $seatIds->count()) {
            return redirect()
                ->route('connections')
                ->with('error', 'Wybrane miejsca nie należą do tego połączenia.');
        }

        $expiredSeat = $seats->first(function (Seat $seat) use ($request, $selectionToken) {
            return ! $this->seatLockedByCurrentSessionOrToken($seat, $request, $selectionToken);
        });

        if ($expiredSeat) {
            return redirect()
                ->route('connections')
                ->with('error', 'Blokada miejsc wygasła. Wybierz miejsca ponownie.');
        }

        $totalPrice = $seats->sum(
            fn (Seat $seat) => $this->priceForSeat($trip, $seat)
        );

        return view('checkout.show', [
            'trip' => $trip,
            'seats' => $seats,
            'totalPrice' => $totalPrice,
            'selectionToken' => $selectionToken,
        ]);
    }

    public function store(Request $request, Trip $trip): RedirectResponse
    {
        $validated = $request->validate([
            'guest_name' => ['required', 'string', 'min:3', 'max:120'],
            'guest_email' => ['required', 'email', 'max:160'],
            'guest_phone' => ['required', 'string', 'min:7', 'max:30'],
        ]);

        $selectionToken = $this->selectionToken($request, $trip);

        $seatIds = $this->selectedSeatIds($request, $trip, $selectionToken);

        if ($seatIds->isEmpty()) {
            return redirect()
                ->route('connections')
                ->with('error', 'Najpierw wybierz miejsca.');
        }

        if ($trip->status === 'departed' || $trip->departure_at->isPast()) {
            return redirect()
                ->route('connections')
                ->with('error', 'Ten pociąg już odjechał.');
        }

        $paymentReference = 'DEMO-' . strtoupper(Str::random(10));
        $ticketIds = [];
        $user = $request->user();

        DB::transaction(function () use (
            $request,
            $trip,
            $seatIds,
            $validated,
            $paymentReference,
            &$ticketIds,
            $user,
            $selectionToken
        ) {
            $seats = Seat::query()
                ->with('wagon')
                ->whereIn('id', $seatIds)
                ->whereHas('wagon', function ($query) use ($trip) {
                    $query->where('trip_id', $trip->id);
                })
                ->lockForUpdate()
                ->get();

            if ($seats->count() !== $seatIds->count()) {
                throw ValidationException::withMessages([
                    'seats' => 'Nieprawidłowy wybór miejsc.',
                ]);
            }

            foreach ($seats as $seat) {
                if ($seat->status !== 'available') {
                    throw ValidationException::withMessages([
                        'seats' => "Miejsce {$seat->seat_number} jest już sprzedane.",
                    ]);
                }

                if (! $this->seatLockedByCurrentSessionOrToken($seat, $request, $selectionToken)) {
                    throw ValidationException::withMessages([
                        'seats' => "Blokada miejsca {$seat->seat_number} wygasła.",
                    ]);
                }

                $ticket = Ticket::create([
                    'trip_id' => $trip->id,
                    'seat_id' => $seat->id,
                    'user_id' => $user?->id,
                    'guest_name' => $validated['guest_name'],
                    'guest_email' => $validated['guest_email'],
                    'price' => $this->priceForSeat($trip, $seat),
                    'payment_status' => 'paid',
                    'payment_reference' => $paymentReference,
                    'status' => 'confirmed',
                ]);

                $seat->update([
                    'status' => 'sold',
                ]);

                Cache::forget($seat->lockKey());

                $ticketIds[] = $ticket->id;
            }
        });

        $successToken = (string) Str::uuid();

        Cache::put(
            "checkout-success:{$successToken}",
            [
                'ticket_ids' => $ticketIds,
                'payment_reference' => $paymentReference,
            ],
            now()->addMinutes(30)
        );

        if ($selectionToken) {
            Cache::forget("checkout-selection:{$selectionToken}");
        }

        $request->session()->forget("selected_seats.{$trip->id}");
        $request->session()->forget("selected_checkout_token.{$trip->id}");
        $request->session()->put('last_ticket_ids', $ticketIds);

        return redirect()->route('checkout.success', [
            'purchase' => $successToken,
        ]);
    }

    public function success(Request $request): View|RedirectResponse
    {
        $successToken = $request->query('purchase');

        $ticketIds = collect(
            $request->session()->get('last_ticket_ids', [])
        );

        if ($successToken) {
            $successData = Cache::get("checkout-success:{$successToken}");

            if (
                is_array($successData)
                && isset($successData['ticket_ids'])
                && is_array($successData['ticket_ids'])
            ) {
                $ticketIds = collect($successData['ticket_ids']);
            }
        }

        $ticketIds = $ticketIds
            ->filter()
            ->unique()
            ->values();

        if ($ticketIds->isEmpty()) {
            return redirect()->route('connections');
        }

        $tickets = Ticket::query()
            ->with([
                'trip.train',
                'trip.departureCity',
                'trip.arrivalCity',
                'seat.wagon',
            ])
            ->whereIn('id', $ticketIds)
            ->get();

        if ($tickets->isEmpty()) {
            return redirect()->route('connections');
        }

        return view('checkout.success', [
            'tickets' => $tickets,
            'trip' => $tickets->first()->trip,
        ]);
    }

    private function selectionToken(Request $request, Trip $trip): ?string
    {
        $token = $request->input('selection')
            ?? $request->query('selection')
            ?? $request->session()->get("selected_checkout_token.{$trip->id}");

        return is_string($token) && $token !== ''
            ? $token
            : null;
    }

    private function selectedSeatIds(Request $request, Trip $trip, ?string $selectionToken): \Illuminate\Support\Collection
    {
        $seatIds = collect(
            $request->session()->get("selected_seats.{$trip->id}", [])
        );

        if ($selectionToken) {
            $selection = Cache::get("checkout-selection:{$selectionToken}");

            if (
                is_array($selection)
                && (int) ($selection['trip_id'] ?? 0) === (int) $trip->id
                && isset($selection['seat_ids'])
                && is_array($selection['seat_ids'])
            ) {
                $seatIds = collect($selection['seat_ids']);

                $request->session()->put(
                    "selected_seats.{$trip->id}",
                    $seatIds->values()->all()
                );

                $request->session()->put(
                    "selected_checkout_token.{$trip->id}",
                    $selectionToken
                );
            }
        }

        return $seatIds
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();
    }

    private function seatLockedByCurrentSessionOrToken(
        Seat $seat,
        Request $request,
        ?string $selectionToken
    ): bool {
        $lockData = Cache::get($seat->lockKey());

        if (! is_array($lockData)) {
            return false;
        }

        if (
            $selectionToken
            && ($lockData['selection_token'] ?? null) === $selectionToken
        ) {
            return true;
        }

        return ($lockData['session_id'] ?? null) === $request->session()->getId();
    }

    private function priceForSeat(Trip $trip, Seat $seat): float
    {
        $basePrice = (float) $trip->base_price;

        return $seat->wagon->class === 'first'
            ? round($basePrice * 1.5, 2)
            : round($basePrice, 2);
    }
}