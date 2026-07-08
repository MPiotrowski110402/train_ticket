@extends('layouts.app')

@section('title', 'Bilet opłacony')

@php
    $firstTicket = $tickets->first();
    $totalPrice = $tickets->sum(fn ($ticket) => (float) $ticket->price);
    $paymentReference = $firstTicket?->payment_reference;

    $qrUrl = route('qr.hire-me');
@endphp

@push('styles')
    <style>
        .success-page {
            min-height: 100vh;
            padding: 140px 0 100px;
            background:
                radial-gradient(circle at top left, rgba(34, 197, 94, 0.13), transparent 34%),
                radial-gradient(circle at bottom right, rgba(37, 99, 235, 0.10), transparent 36%),
                linear-gradient(180deg, #f8fafc, #ffffff);
        }

        .success-container {
            max-width: 1180px;
            margin: 0 auto;
            padding: 0 24px;
        }

        .success-hero {
            position: relative;
            overflow: hidden;
            padding: 44px;
            border-radius: 34px;
            background: rgba(255, 255, 255, 0.88);
            border: 1px solid rgba(226, 232, 240, 0.9);
            box-shadow: 0 28px 80px rgba(15, 23, 42, 0.10);
            backdrop-filter: blur(24px);
            text-align: center;
        }

        .success-hero::before {
            content: "";
            position: absolute;
            inset: -80px auto auto 50%;
            width: 360px;
            height: 360px;
            transform: translateX(-50%);
            border-radius: 50%;
            background: radial-gradient(circle, rgba(34, 197, 94, 0.18), transparent 65%);
            pointer-events: none;
        }

        .success-icon {
            position: relative;
            width: 92px;
            height: 92px;
            margin: 0 auto 22px;
            border-radius: 50%;
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: #ffffff;
            font-size: 3rem;
            font-weight: 950;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 18px 38px rgba(34, 197, 94, 0.32);
            animation: successPop 0.45s ease both;
        }

        @keyframes successPop {
            from {
                opacity: 0;
                transform: scale(0.82);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .success-hero span {
            position: relative;
            display: inline-flex;
            padding: 7px 14px;
            border-radius: 999px;
            background: rgba(34, 197, 94, 0.11);
            color: #16a34a;
            font-size: 0.78rem;
            font-weight: 900;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .success-hero h1 {
            position: relative;
            margin: 14px 0 10px;
            color: #0f172a;
            font-size: 44px;
            letter-spacing: -1.2px;
        }

        .success-hero p {
            position: relative;
            max-width: 720px;
            margin: 0 auto;
            color: #64748b;
            line-height: 1.65;
            font-size: 1rem;
        }

        .success-grid {
            margin-top: 28px;
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) 390px;
            gap: 28px;
            align-items: start;
        }

        .success-card {
            padding: 30px;
            border-radius: 30px;
            background: rgba(255, 255, 255, 0.88);
            border: 1px solid rgba(226, 232, 240, 0.9);
            box-shadow: 0 24px 70px rgba(15, 23, 42, 0.08);
            backdrop-filter: blur(24px);
        }

        .success-card h2 {
            margin: 0 0 22px;
            color: #0f172a;
            font-size: 1.34rem;
            font-weight: 950;
        }

        .route-summary {
            padding: 22px;
            border-radius: 24px;
            background:
                radial-gradient(circle at top right, rgba(37, 99, 235, 0.10), transparent 36%),
                linear-gradient(135deg, #eff6ff, #f8fafc);
            border: 1px solid rgba(191, 219, 254, 0.72);
        }

        .route-summary strong {
            display: block;
            margin-bottom: 12px;
            color: #0f172a;
            font-size: 1.25rem;
            font-weight: 950;
        }

        .route-summary p {
            margin: 8px 0;
            color: #475569;
            line-height: 1.5;
        }

        .tickets-list {
            margin-top: 22px;
            display: grid;
            gap: 14px;
        }

        .ticket-item {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 18px;
            align-items: center;
            padding: 18px;
            border-radius: 22px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.05);
        }

        .ticket-main {
            min-width: 0;
        }

        .ticket-main h3 {
            margin: 0 0 6px;
            color: #0f172a;
            font-size: 1.05rem;
            font-weight: 950;
        }

        .ticket-main p {
            margin: 4px 0;
            color: #64748b;
            font-size: 0.94rem;
        }

        .pnr-box {
            padding: 12px 14px;
            border-radius: 16px;
            background: #0f172a;
            color: #ffffff;
            text-align: center;
            min-width: 128px;
        }

        .pnr-box span {
            display: block;
            color: #94a3b8;
            font-size: 0.68rem;
            font-weight: 850;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .pnr-box strong {
            display: block;
            font-size: 1.1rem;
            letter-spacing: 0.08em;
        }

        .payment-card {
            position: sticky;
            top: 110px;
        }

        .paid-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 14px;
            border-radius: 999px;
            background: rgba(34, 197, 94, 0.11);
            color: #16a34a;
            font-weight: 950;
            margin-bottom: 20px;
        }

        .payment-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 14px 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .payment-row span {
            color: #64748b;
            font-weight: 750;
        }

        .payment-row strong {
            color: #0f172a;
            font-weight: 950;
            text-align: right;
        }

        .payment-total {
            margin-top: 20px;
            padding: 20px;
            border-radius: 22px;
            background: linear-gradient(135deg, #0f172a, #1e3a8a);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
        }

        .payment-total span {
            color: #cbd5e1;
            font-weight: 800;
        }

        .payment-total strong {
            font-size: 1.65rem;
            font-weight: 950;
        }

        .success-actions {
            margin-top: 24px;
            display: grid;
            gap: 12px;
        }

        .success-button {
            min-height: 52px;
            border: 0;
            border-radius: 16px;
            font-weight: 950;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease,
                background 0.2s ease;
        }

        .success-button.primary {
            background: linear-gradient(135deg, #2563eb, #06b6d4);
            color: #ffffff;
            box-shadow: 0 16px 32px rgba(37, 99, 235, 0.24);
        }

        .success-button.secondary {
            background: #ffffff;
            color: #1e3a8a;
            border: 1px solid #dbe5f2;
        }

        .success-button:hover {
            transform: translateY(-2px);
        }

        .demo-info {
            margin-top: 18px;
            padding: 15px 17px;
            border-radius: 18px;
            background: rgba(255, 247, 237, 0.92);
            color: #9a3412;
            font-size: 0.88rem;
            line-height: 1.55;
            font-weight: 650;
        }
        .print-ticket {
            display: none;
        }

        .print-ticket-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 24px;
            padding-bottom: 22px;
            border-bottom: 2px solid #e2e8f0;
        }

        .print-ticket-header span {
            display: inline-flex;
            margin-bottom: 8px;
            color: #2563eb;
            font-size: 0.78rem;
            font-weight: 950;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .print-ticket-header h1 {
            margin: 0;
            color: #0f172a;
            font-size: 34px;
        }

        .print-ticket-status {
            padding: 10px 16px;
            border-radius: 999px;
            background: #dcfce7;
            color: #16a34a;
            font-weight: 950;
        }

        .print-ticket-route {
            margin-top: 26px;
            padding: 24px;
            border-radius: 24px;
            background: #eff6ff;
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            gap: 22px;
            align-items: center;
        }

        .print-ticket-route small,
        .print-ticket-details small,
        .print-ticket-footer small {
            display: block;
            margin-bottom: 5px;
            color: #64748b;
            font-size: 0.76rem;
            font-weight: 850;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .print-ticket-route strong {
            color: #0f172a;
            font-size: 1.28rem;
        }

        .print-ticket-arrow {
            color: #2563eb;
            font-size: 2rem;
            font-weight: 950;
        }

        .print-ticket-details {
            margin-top: 22px;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
        }

        .print-ticket-details > div,
        .print-ticket-footer > div {
            padding: 16px;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            background: #ffffff;
        }

        .print-ticket-details strong,
        .print-ticket-footer strong {
            color: #0f172a;
            font-size: 1rem;
        }

        .print-ticket-body {
            margin-top: 26px;
            display: grid;
            grid-template-columns: 1fr 230px;
            gap: 26px;
            align-items: start;
        }

        .print-ticket-seats h2 {
            margin: 0 0 14px;
            color: #0f172a;
            font-size: 1.25rem;
        }

        .print-ticket-seat {
            display: flex;
            justify-content: space-between;
            gap: 18px;
            padding: 14px 0;
            border-bottom: 1px solid #e2e8f0;
            color: #334155;
        }

        .print-ticket-seat strong {
            color: #0f172a;
            letter-spacing: 0.06em;
        }

        .print-ticket-qr {
            padding: 18px;
            border: 2px solid #0f172a;
            border-radius: 22px;
            text-align: center;
            background: #ffffff;
        }

        .print-ticket-qr svg {
            width: 190px;
            height: 190px;
        }

        .print-ticket-qr p {
            margin: 10px 0 0;
            color: #475569;
            font-size: 0.82rem;
            font-weight: 850;
        }

        .print-ticket-footer {
            margin-top: 26px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .print-ticket-demo-note {
            margin-top: 24px;
            padding-top: 16px;
            border-top: 2px dashed #cbd5e1;
            color: #64748b;
            font-size: 0.9rem;
            text-align: center;
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 14mm;
            }

            body {
                background: #ffffff !important;
            }

            header,
            nav,
            .success-hero,
            .success-grid,
            .success-actions,
            .demo-info {
                display: none !important;
            }

            .success-page {
                min-height: auto;
                padding: 0;
                background: #ffffff !important;
            }

            .success-container {
                max-width: none;
                padding: 0;
                margin: 0;
            }

            .print-ticket {
                display: block !important;
                width: 100%;
                min-height: calc(297mm - 28mm);
                padding: 28px;
                border: 3px solid #0f172a;
                border-radius: 28px;
                background: #ffffff;
                color: #0f172a;
                page-break-inside: avoid;
            }

            .print-ticket * {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }

        @media (max-width: 980px) {
            .success-grid {
                grid-template-columns: 1fr;
            }

            .payment-card {
                position: static;
            }
        }

        @media (max-width: 640px) {
            .success-page {
                padding-top: 120px;
            }

            .success-hero {
                padding: 30px 22px;
                border-radius: 28px;
            }

            .success-hero h1 {
                font-size: 32px;
            }

            .success-card {
                padding: 22px;
                border-radius: 24px;
            }

            .ticket-item {
                grid-template-columns: 1fr;
            }

            .pnr-box {
                width: 100%;
            }

            .payment-total {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
@endpush

@section('content')
    <section class="success-page">
        <div class="success-container">

            <div class="success-hero">
                <div class="success-icon">✓</div>

                <span>Zapłacone</span>

                <h1>Bilet został kupiony</h1>

                <p>
                    Płatność demo została zaakceptowana, a wybrane miejsca zostały oznaczone jako sprzedane.
                    Poniżej znajdziesz dane rezerwacji i kody PNR.
                </p>
            </div>

            <div class="success-grid">

                <div class="success-card">
                    <h2>Szczegóły podróży</h2>

                    <div class="route-summary">
                        <strong>
                            {{ $trip->origin_station }}
                            →
                            {{ $trip->destination_station }}
                        </strong>

                        <p>
                            🚄
                            {{ $trip->train->name ?? 'RailTicket Demo' }}
                            @if ($trip->train?->code)
                                · {{ $trip->train->code }}
                            @endif
                        </p>

                        <p>
                            {{ $trip->departure_at->format('d.m.Y') }}
                            ·
                            {{ $trip->departure_at->format('H:i') }}
                            →
                            {{ $trip->arrival_at->format('H:i') }}
                        </p>

                        <p>
                            Pasażer:
                            <strong>{{ $firstTicket?->passengerName() }}</strong>
                        </p>
                    </div>

                    <div class="tickets-list">
                        @foreach ($tickets as $ticket)
                            <div class="ticket-item">
                                <div class="ticket-main">
                                    <h3>
                                        Wagon {{ $ticket->seat->wagon->number }},
                                        miejsce {{ $ticket->seat->seat_number }}
                                    </h3>

                                    <p>
                                        {{ $ticket->seat->wagon->class === 'first' ? 'Klasa 1' : 'Klasa 2' }}
                                        ·
                                        cena:
                                        {{ number_format((float) $ticket->price, 2, ',', ' ') }} zł
                                    </p>

                                    <p>
                                        Status biletu:
                                        <strong>potwierdzony</strong>
                                    </p>
                                </div>

                                <div class="pnr-box">
                                    <span>Kod PNR</span>
                                    <strong>{{ $ticket->pnr_code }}</strong>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <aside class="success-card payment-card">
                    <div class="paid-badge">
                        ✓ Płatność potwierdzona
                    </div>

                    <div class="payment-row">
                        <span>Liczba biletów</span>
                        <strong>{{ $tickets->count() }}</strong>
                    </div>

                    <div class="payment-row">
                        <span>Status płatności</span>
                        <strong>paid</strong>
                    </div>

                    <div class="payment-row">
                        <span>Numer transakcji</span>
                        <strong>{{ $paymentReference ?? 'DEMO' }}</strong>
                    </div>

                    <div class="payment-row">
                        <span>Adres e-mail</span>
                        <strong>{{ $firstTicket?->guest_email }}</strong>
                    </div>

                    <div class="payment-total">
                        <span>Razem zapłacono</span>

                        <strong>
                            {{ number_format($totalPrice, 2, ',', ' ') }} zł
                        </strong>
                    </div>

                    <div class="success-actions">
                        <button
                            type="button"
                            class="success-button primary"
                            onclick="window.print()"
                        >
                            Drukuj potwierdzenie
                        </button>

                        <a
                            href="{{ route('connections') }}"
                            class="success-button secondary"
                        >
                            Kup kolejny bilet
                        </a>

                        <a
                            href="{{ route('home') }}"
                            class="success-button secondary"
                        >
                            Wróć na stronę główną
                        </a>
                    </div>

                    <div class="demo-info">
                        To potwierdzenie jest częścią wersji demo aplikacji RailTicket.
                        Nie jest prawdziwym biletem kolejowym.
                    </div>
                </aside>

            </div>
        </div>
        <div class="print-ticket">
            <div class="print-ticket-header">
                <div>
                    <span>RailTicket Demo</span>
                    <h1>Bilet kolejowy</h1>
                </div>

                <div class="print-ticket-status">
                    ✓ Opłacony
                </div>
            </div>

            <div class="print-ticket-route">
                <div>
                    <small>Skąd</small>
                    <strong>{{ $trip->origin_station }}</strong>
                </div>

                <div class="print-ticket-arrow">→</div>

                <div>
                    <small>Dokąd</small>
                    <strong>{{ $trip->destination_station }}</strong>
                </div>
            </div>

            <div class="print-ticket-details">
                <div>
                    <small>Data</small>
                    <strong>{{ $trip->departure_at->format('d.m.Y') }}</strong>
                </div>

                <div>
                    <small>Odjazd</small>
                    <strong>{{ $trip->departure_at->format('H:i') }}</strong>
                </div>

                <div>
                    <small>Przyjazd</small>
                    <strong>{{ $trip->arrival_at->format('H:i') }}</strong>
                </div>

                <div>
                    <small>Pasażer</small>
                    <strong>{{ $firstTicket?->passengerName() }}</strong>
                </div>
            </div>

            <div class="print-ticket-body">
                <div class="print-ticket-seats">
                    <h2>Miejsca</h2>

                    @foreach ($tickets as $ticket)
                        <div class="print-ticket-seat">
                            <span>
                                Wagon {{ $ticket->seat->wagon->number }},
                                miejsce {{ $ticket->seat->seat_number }}
                            </span>

                            <strong>{{ $ticket->pnr_code }}</strong>
                        </div>
                    @endforeach
                </div>

                <div class="print-ticket-qr">
                    {!! QrCode::size(190)->margin(1)->generate($qrUrl) !!}

                    <p>QR Zeskanuj</p>
                </div>
            </div>

            <div class="print-ticket-footer">
                <div>
                    <small>Transakcja</small>
                    <strong>{{ $paymentReference ?? 'DEMO' }}</strong>
                </div>

                <div>
                    <small>Zapłacono</small>
                    <strong>{{ number_format($totalPrice, 2, ',', ' ') }} zł</strong>
                </div>
            </div>

            <div class="print-ticket-demo-note">
                To jest bilet demonstracyjny. Po zeskanowaniu QR pokaże się wiadomość testowa.
            </div>
        </div>
    </section>
@endsection