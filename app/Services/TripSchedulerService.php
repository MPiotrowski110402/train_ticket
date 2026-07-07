<?php

namespace App\Services;

use App\Models\Seat;
use App\Models\Train;
use App\Models\Trip;
use App\Models\Wagon;
use Illuminate\Support\Facades\DB;

class TripSchedulerService
{
    // Ile kursów w przyszłości chcemy zawsze mieć dostępnych
    private const POOL_SIZE = 5;

    // Stałe stacje na potrzeby portfolio (można rozbudować o wiele tras)
    private const ROUTES = [
        ['origin' => 'Warszawa Centralna', 'destination' => 'Kraków Główny', 'duration_min' => 150, 'price' => 89.99],
        ['origin' => 'Wrocław Główny', 'destination' => 'Poznań Główny', 'duration_min' => 100, 'price' => 59.99],
        ['origin' => 'Gdańsk Główny', 'destination' => 'Warszawa Centralna', 'duration_min' => 180, 'price' => 99.99],
    ];

    // Za ile godzin od "teraz" ma odjeżdżać kolejny dogenerowany kurs
    private const OFFSETS_HOURS = [1, 2, 4, 6, 8];

    /**
     * Główna metoda self-healing. Wołana przy wejściu na stronę i przez scheduler.
     */
    public function ensurePoolIsFull(): void
    {
        $upcomingCount = Trip::upcoming()->count();

        if ($upcomingCount >= self::POOL_SIZE) {
            return;
        }

        $missing = self::POOL_SIZE - $upcomingCount;

        for ($i = 0; $i < $missing; $i++) {
            $this->generateNextTrip($i);
        }
    }

    private function generateNextTrip(int $index): void
    {
        DB::transaction(function () use ($index) {
            $train = Train::inRandomOrder()->first() ?? $this->createDefaultTrain();
            $route = self::ROUTES[array_rand(self::ROUTES)];

            $offsetHours = self::OFFSETS_HOURS[$index] ?? (end(self::OFFSETS_HOURS) + $index);
            $departure = now()->addHours($offsetHours)->setMinute(0)->setSecond(0);
            $arrival = $departure->copy()->addMinutes($route['duration_min']);

            $trip = Trip::create([
                'train_id' => $train->id,
                'origin_station' => $route['origin'],
                'destination_station' => $route['destination'],
                'departure_at' => $departure,
                'arrival_at' => $arrival,
                'base_price' => $route['price'],
                'status' => 'scheduled',
                'auto_generated' => true,
            ]);

            $this->generateWagonsAndSeats($trip, $train);
        });
    }

    private function generateWagonsAndSeats(Trip $trip, Train $train): void
    {
        $wagonClasses = $train->wagon_classes ?? [];

        for ($wagonNumber = 1; $wagonNumber <= $train->wagon_count; $wagonNumber++) {
            $class = $wagonClasses[$wagonNumber] ?? ($wagonNumber === 1 ? 'first' : 'second');

            $seatsPerRow = 4; // np. układ 2+2
            $rows = (int) ceil($train->seats_per_wagon / $seatsPerRow);

            $wagon = Wagon::create([
                'trip_id' => $trip->id,
                'number' => $wagonNumber,
                'class' => $class,
                'rows' => $rows,
                'seats_per_row' => $seatsPerRow,
            ]);

            $this->generateSeatsForWagon($wagon, $train->seats_per_wagon, $seatsPerRow);
        }
    }

    private function generateSeatsForWagon(Wagon $wagon, int $totalSeats, int $seatsPerRow): void
    {
        $letters = ['A', 'B', 'C', 'D'];
        $seatIndex = 0;

        for ($row = 1; $seatIndex < $totalSeats; $row++) {
            for ($col = 0; $col < $seatsPerRow && $seatIndex < $totalSeats; $col++) {
                $position = match ($col) {
                    0, 3 => 'window',
                    default => 'aisle',
                };

                Seat::create([
                    'wagon_id' => $wagon->id,
                    'seat_number' => "{$row}{$letters[$col]}",
                    'position' => $position,
                    'status' => 'available',
                ]);

                $seatIndex++;
            }
        }
    }

    private function createDefaultTrain(): Train
    {
        return Train::create([
            'name' => 'Pendolino ED250',
            'code' => 'PKP-IC-' . random_int(100, 999),
            'type' => 'express',
            'wagon_count' => 4,
            'seats_per_wagon' => 40,
            'wagon_classes' => ['1' => 'first', '2' => 'second', '3' => 'second', '4' => 'second'],
        ]);
    }
}