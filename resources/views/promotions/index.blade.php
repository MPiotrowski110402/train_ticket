@extends('layouts.app')

@section('title', 'Promocje — RailTicket')
@section('meta-description', 'Demonstracyjne promocje i oferty w systemie RailTicket.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/promotions.css') }}">
@endpush

@section('content')
    <section class="promotions-page">
        <div class="container">
            <header class="page-intro reveal">
                <span class="page-eyebrow">Oferty RailTicket</span>
                <h1>Podróżuj korzystniej.</h1>
                <p>
                    Przykładowe oferty przygotowane dla wersji demonstracyjnej aplikacji.
                    Docelowe warunki i naliczanie rabatów możemy podłączyć później.
                </p>
            </header>

            <div class="promotion-grid">
                <article class="promotion-card promotion-card-featured reveal">
                    <div class="promotion-badge">-25%</div>
                    <div class="promotion-icon">🌆</div>
                    <span class="promotion-category">Weekend</span>
                    <h2>Weekendowa podróż</h2>
                    <p>Wybierz dowolne połączenie od piątku do niedzieli i odbierz przykładowy rabat na bilet.</p>
                    <ul>
                        <li>Podróż od piątku do niedzieli</li>
                        <li>Dowolna klasa przejazdu</li>
                        <li>Oferta demonstracyjna</li>
                    </ul>
                    <a href="{{ route('connections') }}" class="btn btn-primary promotion-action">Znajdź połączenie</a>
                </article>

                <article class="promotion-card reveal">
                    <div class="promotion-badge">-20%</div>
                    <div class="promotion-icon">📅</div>
                    <span class="promotion-category">Z wyprzedzeniem</span>
                    <h2>Planuj wcześniej</h2>
                    <p>Przykładowa oferta dla osób, które kupują bilet z odpowiednim wyprzedzeniem.</p>
                    <ul>
                        <li>Zakup minimum 14 dni przed podróżą</li>
                        <li>Wybrane relacje krajowe</li>
                        <li>Jedna promocja na bilet</li>
                    </ul>
                    <a href="{{ route('connections') }}" class="btn btn-outline promotion-action">Sprawdź terminy</a>
                </article>

                <article class="promotion-card reveal">
                    <div class="promotion-badge">-15%</div>
                    <div class="promotion-icon">👥</div>
                    <span class="promotion-category">Dla grup</span>
                    <h2>Razem w trasę</h2>
                    <p>Demonstracyjny wariant zniżki dla dwóch lub większej liczby podróżnych.</p>
                    <ul>
                        <li>Od dwóch osób na jednej rezerwacji</li>
                        <li>Wspólny kierunek podróży</li>
                        <li>Wybór miejsc obok siebie</li>
                    </ul>
                    <a href="{{ route('connections') }}" class="btn btn-outline promotion-action">Zobacz połączenia</a>
                </article>
            </div>

            <aside class="demo-note reveal">
                <span aria-hidden="true">ℹ️</span>
                <p><strong>Wersja demo:</strong> rabaty są obecnie elementem prezentacyjnym i nie zmieniają ceny biletu.</p>
            </aside>
        </div>
    </section>
@endsection
