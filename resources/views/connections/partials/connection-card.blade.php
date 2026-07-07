@php
    $durationMinutes = (int) $trip->departure_at->diffInMinutes($trip->arrival_at);
    $durationHours = intdiv($durationMinutes, 60);
    $remainingMinutes = $durationMinutes % 60;

    $durationLabel = $durationHours > 0
        ? "{$durationHours} h" . ($remainingMinutes > 0 ? " {$remainingMinutes} min" : '')
        : "{$remainingMinutes} min";

    $isUnavailable = $trip->status === 'departed' || $trip->departure_at->isPast();

    $secondClassPrice = (float) $trip->base_price;
    $firstClassPrice = $secondClassPrice * 1.5;

    $hasFirstClass = $trip->train
        && collect($trip->train->wagon_classes ?? [])->contains('first');

    $trainLabel = $trip->train
        ? "{$trip->train->name} · {$trip->train->code}"
        : 'RailTicket Demo';
@endphp

<div @class([
    'connection-card',
    'is-unavailable' => $isUnavailable,
])>
    <div class="connection-top">

        <div>
            <span @class([
                'direct',
                'departed-status' => $isUnavailable,
            ])>
                {{ $isUnavailable ? 'Odjechał' : 'Bezpośredni' }}
            </span>

            <h2>
                {{ $trip->departure_at->format('H:i') }}
                <span>→</span>
                {{ $trip->arrival_at->format('H:i') }}
            </h2>

            <p>{{ $durationLabel }}</p>
        </div>

        <div class="route">
            <strong>{{ $trip->origin_station }}</strong>

            <div class="line"></div>

            <strong>{{ $trip->destination_station }}</strong>

            <p>🚄 {{ $trainLabel }}</p>
        </div>

        <div @class([
            'prices',
            'single-price' => ! $hasFirstClass,
        ])>
            @if ($hasFirstClass)
                <div>
                    <h4>Klasa 1</h4>

                    <strong>
                        {{ number_format($firstClassPrice, 2, ',', ' ') }} zł
                    </strong>

                    <button
                        type="button"
                        class="select-class-btn"
                        data-trip-id="{{ $trip->id }}"
                        data-travel-class="first"
                        @disabled($isUnavailable)
                    >
                        {{ $isUnavailable ? 'Niedostępne' : 'Wybierz' }}
                    </button>
                </div>
            @endif

            <div>
                <h4>Klasa 2</h4>

                <strong>
                    {{ number_format($secondClassPrice, 2, ',', ' ') }} zł
                </strong>

                <button
                    type="button"
                    class="select-class-btn"
                    data-trip-id="{{ $trip->id }}"
                    data-travel-class="second"
                    @disabled($isUnavailable)
                >
                    {{ $isUnavailable ? 'Niedostępne' : 'Wybierz' }}
                </button>
            </div>
        </div>

    </div>

    <button
        type="button"
        class="details-btn"
        aria-expanded="false"
    >
        ▼ Pokaż szczegóły połączenia
    </button>

    <div class="connection-details">
        <div class="timeline">
            <div>
                {{ $trip->departure_at->format('H:i') }}
            </div>

            <div class="station">
                {{ $trip->origin_station }}
                <br>
                <small>Planowany odjazd</small>
            </div>

            <div>
                {{ $trip->arrival_at->format('H:i') }}
            </div>

            <div class="station">
                {{ $trip->destination_station }}
                <br>
                <small>Planowany przyjazd</small>
            </div>
        </div>

        <div class="buy-section">
            @if ($isUnavailable)
                <h3>To połączenie jest już niedostępne</h3>
                <p>
                    Pociąg odjechał. Kurs pozostaje widoczny tylko przez godzinę w wersji demo.
                </p>
            @else
                <h3>Wybierz miejsce</h3>

                @include('connections.partials.seat-map', [
                    'trip' => $trip,
                ])
            @endif
        </div>
    </div>
</div>