@extends('layouts.app')

@section('title', 'Wyszukaj połączenie')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/connections.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('js/connections.js') }}"></script>
@endpush

@section('content')
    @php
        $fromCity = $cities->firstWhere('id', (int) ($filters['from'] ?? 0));
        $toCity = $cities->firstWhere('id', (int) ($filters['to'] ?? 0));

        $routeLabel = match (true) {
            $fromCity && $toCity => "{$fromCity->name} → {$toCity->name}",
            $fromCity => "Odjazdy z: {$fromCity->name}",
            $toCity => "Przyjazdy do: {$toCity->name}",
            default => 'Wszystkie kierunki demonstracyjne',
        };
    @endphp

    <section class="connections-page">
        <div class="container">

            @include('connections.partials.search-box')

            <div class="results-header">
                <h1>Dostępne połączenia</h1>

                <p>
                    {{ $routeLabel }}
                    · znaleziono: {{ $trips->count() }}
                </p>
            </div>

            <div class="connections-list">
                @forelse ($trips as $trip)
                    @include('connections.partials.connection-card', [
                        'trip' => $trip,
                    ])
                @empty
                    <div class="connection-card">
                        <div class="connection-top">
                            <div>
                                <span class="direct">Brak wyników</span>

                                <h2>Nie znaleziono połączeń</h2>

                                <p>
                                    Zmień miasto, kierunek lub datę wyszukiwania.
                                </p>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>

        </div>
    </section>
@endsection