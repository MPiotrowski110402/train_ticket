<?php

namespace App\Services;

use App\Models\City;
use App\Models\Seat;
use App\Models\Train;
use App\Models\Trip;
use App\Models\Wagon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TripSchedulerService
{
    private const POOL_SIZE_PER_ROUTE = 5;

    private const DEPARTED_VISIBILITY_MINUTES = 60;

    private const INITIAL_OFFSETS_MINUTES = [84, 150, 255, 390, 480];

    private const NEXT_TRIP_OFFSET_MINUTES = 480;

    private const MINIMUM_GAP_MINUTES = 75;

    private const TRIP_DURATION_MINUTES = 300;

    private const BASE_PRICE = 89.99;

    private const ROUTE_PAIRS = [
        ['gdansk', 'poznan'],
        ['poznan', 'wroclaw'],
        ['wroclaw', 'krakow'],
        ['krakow', 'warszawa'],
        ['warszawa', 'olsztyn'],
    ];

    public function ensurePoolIsFull(): void
    {
        $lock = Cache::lock('rail-ticket:trip-pool-refresh', 15);

        if (! $lock->get()) {
            return;
        }

        try {
            DB::transaction(function (): void {
                $now = now();

                $this->markDepartedTrips($now);
                $this->removeExpiredDemoTrips($now);
                $this->ensureRoutePools($now);
            });
        } finally {
            $lock->release();
        }
    }

    public function rebuildDemoPool(): void
    {
        DB::transaction(function (): void {
            Trip::query()
                ->where('auto_generated', true)
                ->delete();

            $this->ensureRoutePools(now());
        });
    }

    private function ensureRoutePools(CarbonInterface $now): void
    {
        foreach ($this->directedRoutePairs() as [$originSlug, $destinationSlug]) {
            $origin = City::query()
                ->where('slug', $originSlug)
                ->where('is_active', true)
                ->first();

            $destination = City::query()
                ->where('slug', $destinationSlug)
                ->where('is_active', true)
                ->first();

            if (! $origin || ! $destination) {
                throw new RuntimeException(
                    "Brakuje aktywnych miast dla relacji {$originSlug} → {$destinationSlug}."
                );
            }

            $this->ensureSingleRoutePool($origin, $destination, $now);
        }
    }

    private function ensureSingleRoutePool(City $origin, City $destination, CarbonInterface $now): void
    {
        $upcomingTrips = Trip::query()
            ->where('departure_city_id', $origin->id)
            ->where('arrival_city_id', $destination->id)
            ->where('status', 'scheduled')
            ->where('departure_at', '>', $now)
            ->orderBy('departure_at')
            ->get();

        $missing = self::POOL_SIZE_PER_ROUTE - $upcomingTrips->count();

        if ($missing <= 0) {
            return;
        }

        if ($upcomingTrips->isEmpty()) {
            for ($slot = 0; $slot < $missing; $slot++) {
                $offset = self::INITIAL_OFFSETS_MINUTES[$slot]
                    ?? (
                        self::INITIAL_OFFSETS_MINUTES[array_key_last(self::INITIAL_OFFSETS_MINUTES)]
                        + (($slot - 4) * self::MINIMUM_GAP_MINUTES)
                    );

                $this->createTrip(
                    origin: $origin,
                    destination: $destination,
                    departure: $now->copy()->addMinutes($offset),
                );
            }

            return;
        }

        $latestDeparture = $upcomingTrips->last()->departure_at->copy();

        for ($slot = 0; $slot < $missing; $slot++) {
            $departure = $this->nextDepartureAfter($latestDeparture, $now);

            $trip = $this->createTrip(
                origin: $origin,
                destination: $destination,
                departure: $departure,
            );

            $latestDeparture = $trip->departure_at->copy();
        }
    }

    private function markDepartedTrips(CarbonInterface $now): void
    {
        Trip::query()
            ->where('auto_generated', true)
            ->whereIn('status', ['scheduled', 'boarding'])
            ->where('departure_at', '<=', $now)
            ->update([
                'status' => 'departed',
                'updated_at' => $now,
            ]);
    }

    private function removeExpiredDemoTrips(CarbonInterface $now): void
    {
        Trip::query()
            ->where('auto_generated', true)
            ->where('status', 'departed')
            ->where(
                'departure_at',
                '<=',
                $now->copy()->subMinutes(self::DEPARTED_VISIBILITY_MINUTES)
            )
            ->delete();
    }

    private function nextDepartureAfter(CarbonInterface $latestDeparture, CarbonInterface $now): CarbonInterface
    {
        $atLeastEightHoursFromNow = $now->copy()->addMinutes(self::NEXT_TRIP_OFFSET_MINUTES);
        $afterLatestScheduledTrip = $latestDeparture->copy()->addMinutes(self::MINIMUM_GAP_MINUTES);

        return $afterLatestScheduledTrip->greaterThan($atLeastEightHoursFromNow)
            ? $afterLatestScheduledTrip
            : $atLeastEightHoursFromNow;
    }

    private function createTrip(City $origin, City $destination, CarbonInterface $departure): Trip
    {
        $train = Train::query()->inRandomOrder()->first() ?? $this->createDefaultTrain();

        $trip = Trip::create([
            'train_id' => $train->id,
            'departure_city_id' => $origin->id,
            'arrival_city_id' => $destination->id,
            'origin_station' => "{$origin->name} Główne",
            'destination_station' => "{$destination->name} Główne",
            'departure_at' => $departure,
            'arrival_at' => $departure->copy()->addMinutes(self::TRIP_DURATION_MINUTES),
            'base_price' => self::BASE_PRICE,
            'status' => 'scheduled',
            'auto_generated' => true,
        ]);

        $this->generateWagonsAndSeats($trip, $train);

        return $trip;
    }

    /**
     * Z relacji bazowych robi kierunki tam i z powrotem.
     *
     * @return array<int, array{0: string, 1: string}>
     */
    private function directedRoutePairs(): array
    {
        $routes = [];

        foreach (self::ROUTE_PAIRS as [$firstCitySlug, $secondCitySlug]) {
            $routes[] = [$firstCitySlug, $secondCitySlug];
            $routes[] = [$secondCitySlug, $firstCitySlug];
        }

        return $routes;
    }

    private function generateWagonsAndSeats(Trip $trip, Train $train): void
    {
        $wagonClasses = $train->wagon_classes ?? [];

        for ($wagonNumber = 1; $wagonNumber <= $train->wagon_count; $wagonNumber++) {
            $class = $wagonClasses[$wagonNumber] ?? ($wagonNumber === 1 ? 'first' : 'second');
            $seatsPerRow = 4;
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
            for ($column = 0; $column < $seatsPerRow && $seatIndex < $totalSeats; $column++) {
                $position = match ($column) {
                    0, 3 => 'window',
                    default => 'aisle',
                };

                Seat::create([
                    'wagon_id' => $wagon->id,
                    'seat_number' => "{$row}{$letters[$column]}",
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
            'name' => 'RailTicket Express Demo',
            'code' => 'RT-DEMO-' . random_int(100, 999),
            'type' => 'express',
            'wagon_count' => 4,
            'seats_per_wagon' => 40,
            'wagon_classes' => [
                '1' => 'first',
                '2' => 'second',
                '3' => 'second',
                '4' => 'second',
            ],
        ]);
    }
}