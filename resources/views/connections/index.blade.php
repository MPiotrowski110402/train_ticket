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
            $fromCity => "Wybierz jeszcze miasto docelowe",
            $toCity => "Wybierz jeszcze miasto początkowe",
            default => 'Wybierz trasę demonstracyjną',
        };
    @endphp

    <section class="connections-page">
        <div class="container">

            @include('connections.partials.search-box')

            <div class="results-header">
                <h1>Dostępne połączenia</h1>

                <p>
                    {{ $routeLabel }}

                    @if ($hasSearched)
                        · znaleziono: {{ $trips->count() }}
                    @else
                        · najpierw wybierz trasę
                    @endif
                </p>
            </div>

            @if (! $hasSearched)
                <div class="route-guide-card">
                    <div class="route-guide-header">
                        <span>Demo rozkładu</span>

                        <h2>Wybierz jedną z uproszczonych tras</h2>

                        <p>
                            Wersja demonstracyjna korzysta z uproszczonej siatki połączeń.
                            Każde miasto komunikuje się tylko z sąsiednim miastem na trasie,
                            a połączenia działają w obie strony.
                        </p>
                    </div>

                    <div class="route-guide-line">
                        @foreach ($demoRoutePairs as [$from, $to])
                            <div class="route-guide-item">
                                <strong>{{ $from }}</strong>
                                <span>↔</span>
                                <strong>{{ $to }}</strong>
                            </div>
                        @endforeach
                    </div>

                    <div class="route-guide-note">
                        Przykład: <strong>Gdańsk → Poznań</strong> pokaże połączenia,
                        ale <strong>Gdańsk → Kraków</strong> nie, bo w demo nie ma połączenia bezpośredniego.
                    </div>
                </div>
            @else
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
                                        Ta relacja nie istnieje w uproszczonej siatce demo
                                        albo nie ma kursów dla wybranej daty.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforelse
                </div>
            @endif

        </div>
    </section>
@endsection