<?php

namespace App\Services;

use App\Models\City;
use App\Models\Seat;
use App\Models\Train;
use App\Models\Trip;
use App\Models\Wagon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TripSchedulerService
{
    /** Liczba kursów, które można jeszcze kupić z każdego miasta startowego. */
    private const POOL_SIZE_PER_CITY = 5;

    /** Po tym czasie od odjazdu szary kurs znika z widoku i z demo-bazy. */
    private const DEPARTED_VISIBILITY_MINUTES = 60;

    /** Rozkład początkowy liczony od chwili seedowania: 1:24, 2:30, 4:15, 6:30, 8:00. */
    private const INITIAL_OFFSETS_MINUTES = [84, 150, 255, 390, 480];

    /** Nowy kurs pojawia się co najmniej około osiem godzin od chwili odświeżenia puli. */
    private const NEXT_TRIP_OFFSET_MINUTES = 480;

    /** Zachowuje odstęp między kolejnymi połączeniami z jednego miasta. */
    private const MINIMUM_GAP_MINUTES = 75;

    /**
     * Mechanizm self-healing: oznacza odjechane kursy, usuwa stare i utrzymuje
     * pięć przyszłych połączeń dla każdego aktywnego miasta startowego.
     */
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

                City::active()
                    ->orderBy('id')
                    ->get()
                    ->each(fn (City $city) => $this->ensureCityPool($city, $now));
            });
        } finally {
            $lock->release();
        }
    }

    /**
     * Używane wyłącznie przez db:seed. Czyści poprzednią syntetyczną pulę
     * i tworzy nowy, przewidywalny rozkład dla każdego miasta.
     */
    public function rebuildDemoPool(): void
    {
        DB::transaction(function (): void {
            Trip::query()
                ->where('auto_generated', true)
                ->delete();

            $now = now();

            City::active()
                ->orderBy('id')
                ->get()
                ->each(fn (City $city) => $this->ensureCityPool($city, $now));
        });
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
            ->where('departure_at', '<=', $now->copy()->subMinutes(self::DEPARTED_VISIBILITY_MINUTES))
            ->delete();
    }

    private function ensureCityPool(City $origin, CarbonInterface $now): void
    {
        $upcomingTrips = Trip::query()
            ->where('departure_city_id', $origin->id)
            ->where('status', 'scheduled')
            ->where('departure_at', '>', $now)
            ->orderBy('departure_at')
            ->get();

        $missing = self::POOL_SIZE_PER_CITY - $upcomingTrips->count();

        if ($missing <= 0) {
            return;
        }

        $destinations = $this->destinationSequence($origin, $missing);

        if ($upcomingTrips->isEmpty()) {
            for ($slot = 0; $slot < $missing; $slot++) {
                $offset = self::INITIAL_OFFSETS_MINUTES[$slot]
                    ?? (self::INITIAL_OFFSETS_MINUTES[array_key_last(self::INITIAL_OFFSETS_MINUTES)] + (($slot - 4) * self::MINIMUM_GAP_MINUTES));

                $this->createTrip(
                    origin: $origin,
                    destination: $destinations[$slot],
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
                destination: $destinations[$slot],
                departure: $departure,
            );

            $latestDeparture = $trip->departure_at->copy();
        }
    }

    /**
     * Zwraca różne miasta docelowe, a przy większym zapotrzebowaniu powtarza ich kolejność.
     * Pozwala to uniknąć kursów typu „Opole → Opole”.
     *
     * @return array<int, City>
     */
    private function destinationSequence(City $origin, int $count): array
    {
        /** @var Collection<int, City> $availableDestinations */
        $availableDestinations = City::active()
            ->whereKeyNot($origin->id)
            ->inRandomOrder()
            ->get()
            ->values();

        if ($availableDestinations->isEmpty()) {
            throw new RuntimeException('Do generowania połączeń potrzebne są co najmniej dwa aktywne miasta.');
        }

        $destinations = [];

        for ($slot = 0; $slot < $count; $slot++) {
            $destinations[] = $availableDestinations[$slot % $availableDestinations->count()];
        }

        return $destinations;
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
        $route = $this->routeDetails($origin, $destination);

        $trip = Trip::create([
            'train_id' => $train->id,
            'departure_city_id' => $origin->id,
            'arrival_city_id' => $destination->id,
            // Zostawiamy pola tekstowe dla obecnych widoków i kompatybilności wstecznej.
            'origin_station' => "{$origin->name} Główne",
            'destination_station' => "{$destination->name} Główne",
            'departure_at' => $departure,
            'arrival_at' => $departure->copy()->addMinutes($route['duration_min']),
            'base_price' => $route['price'],
            'status' => 'scheduled',
            'auto_generated' => true,
        ]);

        $this->generateWagonsAndSeats($trip, $train);

        return $trip;
    }

    /**
     * Deterministyczne parametry demonstracyjnej trasy — ta sama para miast
     * otrzyma ten sam czas przejazdu i cenę przy każdym seedowaniu.
     *
     * @return array{duration_min: int, price: float}
     */
    private function routeDetails(City $origin, City $destination): array
    {
        $profiles = [
            ['duration_min' => 75, 'price' => 29.99],
            ['duration_min' => 95, 'price' => 39.99],
            ['duration_min' => 120, 'price' => 49.99],
            ['duration_min' => 145, 'price' => 59.99],
            ['duration_min' => 175, 'price' => 74.99],
            ['duration_min' => 205, 'price' => 89.99],
        ];

        $index = abs(crc32("{$origin->slug}:{$destination->slug}")) % count($profiles);

        return $profiles[$index];
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
