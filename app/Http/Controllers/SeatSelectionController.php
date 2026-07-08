<?php

namespace App\Http\Controllers;

use App\Models\Seat;
use App\Models\Trip;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class SeatSelectionController extends Controller
{
    public function store(Request $request, Trip $trip): JsonResponse
    {
        $validated = $request->validate([
            'passengers' => ['required', 'integer', 'min:1', 'max:4'],
            'seat_ids' => ['required', 'array'],
            'seat_ids.*' => ['required', 'integer', 'distinct', 'exists:seats,id'],
        ]);

        $passengersCount = (int) $validated['passengers'];
        $seatIds = $validated['seat_ids'];

        if (count($seatIds) !== $passengersCount) {
            throw ValidationException::withMessages([
                'seat_ids' => "Musisz wybrać dokładnie {$passengersCount} miejsc.",
            ]);
        }

        if ($trip->status === 'departed' || $trip->departure_at->isPast()) {
            return response()->json([
                'message' => 'Ten pociąg już odjechał. Nie można wybrać miejsc.',
            ], 409);
        }

        $lock = Cache::lock("trip-seat-selection:{$trip->id}", 10);

        if (! $lock->get()) {
            return response()->json([
                'message' => 'Ktoś właśnie wybiera miejsca w tym pociągu. Spróbuj ponownie za chwilę.',
            ], 409);
        }

        try {
            $seats = Seat::query()
                ->with('wagon:id,trip_id,number,class')
                ->whereIn('id', $seatIds)
                ->whereHas('wagon', function ($query) use ($trip) {
                    $query->where('trip_id', $trip->id);
                })
                ->get();

            if ($seats->count() !== count($seatIds)) {
                return response()->json([
                    'message' => 'Wybrane miejsca nie należą do tego połączenia.',
                ], 422);
            }

            $unavailableSeat = $seats->first(function (Seat $seat) {
                return $seat->effectiveStatus() !== 'available';
            });

            if ($unavailableSeat) {
                return response()->json([
                    'message' => "Miejsce {$unavailableSeat->seat_number} jest już niedostępne.",
                ], 409);
            }

            $lockedUntil = now()->addMinutes(10);

            foreach ($seats as $seat) {
                Cache::put(
                    $seat->lockKey(),
                    [
                        'trip_id' => $trip->id,
                        'seat_id' => $seat->id,
                        'session_id' => $request->session()->getId(),
                        'locked_until' => $lockedUntil->toIso8601String(),
                    ],
                    $lockedUntil
                );
            }

            $request->session()->put(
                "selected_seats.{$trip->id}",
                $seats->pluck('id')->values()->all()
            );

            return response()->json([
                'message' => 'Miejsca zostały tymczasowo zablokowane.',
                'checkout_url' => route('checkout.show', $trip),
                'locked_until' => $lockedUntil->toIso8601String(),
                'selected_seats' => $seats
                    ->sortBy([
                        ['wagon.number', 'asc'],
                        ['seat_number', 'asc'],
                    ])
                    ->map(function (Seat $seat) {
                        return [
                            'id' => $seat->id,
                            'seat_number' => $seat->seat_number,
                            'wagon_number' => $seat->wagon->number,
                            'wagon_class' => $seat->wagon->class,
                        ];
                    })
                    ->values(),
            ]);
        } finally {
            $lock->release();
        }
    }
}