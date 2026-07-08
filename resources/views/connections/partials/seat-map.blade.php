@php
    $passengersCount = (int) request('passengers', 1);
    $passengersCount = max(1, min($passengersCount, 4));

    $wagons = $trip->wagons
        ->sortBy('number')
        ->values();

    $firstAvailableWagonId = $wagons->first()?->id;

    $classLabels = [
        'first' => 'Klasa 1',
        'second' => 'Klasa 2',
    ];

    $positionLabels = [
        'window' => 'Okno',
        'aisle' => 'Przejście',
    ];
@endphp

<div
    class="train-seat-map"
    data-required-seats="{{ $passengersCount }}"
    data-trip-id="{{ $trip->id }}"
    data-seat-lock-url="{{ route('connections.seats.store', $trip) }}"
>
    <div class="seat-map-header">
        <div>
            <span class="seat-map-kicker">Wybór miejsc</span>

            <h4>
                Wybierz {{ $passengersCount }}
                {{ trans_choice('miejsce|miejsca|miejsc', $passengersCount) }}
            </h4>

            <p>
                Kliknij dokładnie {{ $passengersCount }}
                {{ trans_choice('dostępne miejsce|dostępne miejsca|dostępnych miejsc', $passengersCount) }}.
                W wersji demo wybór jest tylko wizualny.
            </p>
        </div>

        <div class="seat-map-counter">
            <strong class="selected-seats-count">0</strong>
            <span>/ {{ $passengersCount }}</span>
        </div>
    </div>

    @if ($wagons->isEmpty())
        <div class="seat-map-empty">
            Brak wagonów dla tego połączenia.
        </div>
    @else
        <div class="train-visual">
            <div class="train-locomotive">
                <span>🚄</span>
                <strong>Lokomotywa</strong>
            </div>

            <div class="wagon-strip" role="tablist" aria-label="Wagony pociągu">
                @foreach ($wagons as $wagon)
                    @php
                        $availableSeats = $wagon->seats
                            ->filter(fn ($seat) => $seat->effectiveStatus() === 'available')
                            ->count();

                        $isFirstWagon = $wagon->id === $firstAvailableWagonId;
                    @endphp

                    <button
                        type="button"
                        class="wagon-tab {{ $isFirstWagon ? 'active' : '' }}"
                        data-wagon-target="wagon-{{ $wagon->id }}"
                        data-wagon-class="{{ $wagon->class }}"
                        role="tab"
                        aria-selected="{{ $isFirstWagon ? 'true' : 'false' }}"
                    >
                        <span class="wagon-number">
                            Wagon {{ $wagon->number }}
                        </span>

                        <span class="wagon-class">
                            {{ $classLabels[$wagon->class] ?? 'Klasa' }}
                        </span>

                        <span class="wagon-free">
                            {{ $availableSeats }} wolnych
                        </span>
                    </button>
                @endforeach
            </div>
        </div>

        <div class="wagon-panels">
            @foreach ($wagons as $wagon)
                @php
                    $isFirstWagon = $wagon->id === $firstAvailableWagonId;

                    $seats = $wagon->seats
                        ->sortBy(function ($seat) {
                            preg_match('/^(\d+)([A-Z])$/', $seat->seat_number, $matches);

                            $row = (int) ($matches[1] ?? 0);
                            $letter = $matches[2] ?? '';

                            return sprintf('%03d-%s', $row, $letter);
                        })
                        ->values()
                        ->groupBy(function ($seat) {
                            preg_match('/^(\d+)/', $seat->seat_number, $matches);

                            return (int) ($matches[1] ?? 0);
                        });
                @endphp

                <div
                    class="wagon-panel {{ $isFirstWagon ? 'active' : '' }}"
                    id="wagon-{{ $wagon->id }}"
                    data-wagon-panel
                    data-wagon-class="{{ $wagon->class }}"
                    role="tabpanel"
                >
                    <div class="wagon-panel-header">
                        <div>
                            <h5>
                                Wagon {{ $wagon->number }}
                                <span>
                                    {{ $classLabels[$wagon->class] ?? 'Klasa' }}
                                </span>
                            </h5>

                            <p>
                                Układ demo: {{ $wagon->seats_per_row }} miejsca w rzędzie.
                            </p>
                        </div>

                        <div class="wagon-direction">
                            Kierunek jazdy →
                        </div>
                    </div>

                    <div class="wagon-body">
                        <div class="wagon-door front-door">Drzwi</div>

                        <div class="wagon-corridor">
                            <span>Przejście</span>
                        </div>

                        <div class="seats-layout">
                            @foreach ($seats as $rowNumber => $rowSeats)
                                <div class="seat-row">
                                    <div class="row-number">
                                        {{ $rowNumber }}
                                    </div>

                                    <div class="row-seats">
                                        @foreach ($rowSeats as $seat)
                                            @php
                                                $status = $seat->effectiveStatus();

                                                $isAvailable = $status === 'available';

                                                $statusLabel = match ($status) {
                                                    'sold' => 'Sprzedane',
                                                    'locked' => 'Zablokowane',
                                                    default => 'Dostępne',
                                                };
                                            @endphp

                                            <button
                                                type="button"
                                                class="seat seat-{{ $status }}"
                                                data-seat-id="{{ $seat->id }}"
                                                data-seat-number="{{ $seat->seat_number }}"
                                                data-wagon-number="{{ $wagon->number }}"
                                                data-position="{{ $seat->position }}"
                                                aria-pressed="false"
                                                title="Miejsce {{ $seat->seat_number }} · {{ $positionLabels[$seat->position] ?? $seat->position }} · {{ $statusLabel }}"
                                                @disabled(! $isAvailable)
                                            >
                                                {{ $seat->seat_number }}
                                            </button>

                                            @if ($loop->iteration === 2)
                                                <span class="seat-gap" aria-hidden="true"></span>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="wagon-door back-door">Drzwi</div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="seat-map-footer">
            <div class="seat-legend">
                <span>
                    <i class="legend-dot available"></i>
                    Dostępne
                </span>

                <span>
                    <i class="legend-dot selected"></i>
                    Wybrane
                </span>

                <span>
                    <i class="legend-dot unavailable"></i>
                    Zajęte
                </span>
            </div>

            <button
                type="button"
                class="continue-with-seats"
                disabled
            >
                Wybierz jeszcze {{ $passengersCount }}
                {{ trans_choice('miejsce|miejsca|miejsc', $passengersCount) }}
            </button>
        </div>
    @endif
</div>