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
        $seatIds = collect(
            $request->session()->get("selected_seats.{$trip->id}", [])
        )->unique()->values();

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

        $expiredSeat = $seats->first(function (Seat $seat) use ($request) {
            return ! $this->seatLockedByCurrentSession($seat, $request);
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
        ]);
    }

    public function store(Request $request, Trip $trip): RedirectResponse
    {
        $validated = $request->validate([
            'guest_name' => ['required', 'string', 'min:3', 'max:120'],
            'guest_email' => ['required', 'email', 'max:160'],
        ]);

        $seatIds = collect(
            $request->session()->get("selected_seats.{$trip->id}", [])
        )->unique()->values();

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

        DB::transaction(function () use ($request, $trip, $seatIds, $validated, $paymentReference, &$ticketIds) {
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

                if (! $this->seatLockedByCurrentSession($seat, $request)) {
                    throw ValidationException::withMessages([
                        'seats' => "Blokada miejsca {$seat->seat_number} wygasła.",
                    ]);
                }

                $ticket = Ticket::create([
                    'trip_id' => $trip->id,
                    'seat_id' => $seat->id,
                    'user_id' => null,
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

        $request->session()->forget("selected_seats.{$trip->id}");
        $request->session()->put('last_ticket_ids', $ticketIds);

        return redirect()->route('checkout.success');
    }

    public function success(Request $request): View|RedirectResponse
    {
        $ticketIds = $request->session()->get('last_ticket_ids', []);

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

    private function seatLockedByCurrentSession(Seat $seat, Request $request): bool
    {
        $lockData = Cache::get($seat->lockKey());

        return is_array($lockData)
            && ($lockData['session_id'] ?? null) === $request->session()->getId();
    }

    private function priceForSeat(Trip $trip, Seat $seat): float
    {
        $basePrice = (float) $trip->base_price;

        return $seat->wagon->class === 'first'
            ? round($basePrice * 1.5, 2)
            : round($basePrice, 2);
    }
}