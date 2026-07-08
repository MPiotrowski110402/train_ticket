@extends('layouts.app')

@section('title', 'Rezerwacja biletu')

@push('styles')
    <style>
        .checkout-page {
            padding: 140px 0 100px;
            background:
                radial-gradient(circle at top left, rgba(37, 99, 235, 0.11), transparent 34%),
                linear-gradient(180deg, #f8fafc, #ffffff);
            min-height: 100vh;
        }

        .checkout-container {
            max-width: 1180px;
            margin: 0 auto;
            padding: 0 24px;
        }

        .checkout-header {
            margin-bottom: 34px;
        }

        .checkout-header span {
            display: inline-flex;
            padding: 7px 14px;
            border-radius: 999px;
            background: rgba(37, 99, 235, 0.1);
            color: #2563eb;
            font-size: 0.78rem;
            font-weight: 850;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .checkout-header h1 {
            margin: 14px 0 8px;
            color: #0f172a;
            font-size: 42px;
            letter-spacing: -1.2px;
        }

        .checkout-header p {
            margin: 0;
            color: #64748b;
            font-size: 1rem;
        }

        .checkout-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.15fr) 420px;
            gap: 28px;
            align-items: start;
        }

        .checkout-card,
        .summary-card {
            background: rgba(255, 255, 255, 0.86);
            border: 1px solid rgba(226, 232, 240, 0.9);
            border-radius: 30px;
            box-shadow: 0 24px 70px rgba(15, 23, 42, 0.09);
            backdrop-filter: blur(24px);
        }

        .checkout-card {
            padding: 34px;
        }

        .summary-card {
            padding: 28px;
            position: sticky;
            top: 110px;
        }

        .checkout-section-title {
            margin: 0 0 22px;
            color: #0f172a;
            font-size: 1.35rem;
            font-weight: 900;
        }

        .checkout-form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .form-field {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-field.full {
            grid-column: 1 / -1;
        }

        .form-field label {
            color: #64748b;
            font-size: 0.86rem;
            font-weight: 750;
        }

        .form-field input {
            height: 56px;
            padding: 0 17px;
            border: 1px solid #dbe5f2;
            border-radius: 16px;
            background: #ffffff;
            color: #0f172a;
            font: inherit;
            outline: none;
            transition:
                border-color 0.22s ease,
                box-shadow 0.22s ease,
                transform 0.22s ease;
        }

        .form-field input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.11);
        }

        .blik-box {
            margin-top: 26px;
            padding: 24px;
            border-radius: 24px;
            background:
                radial-gradient(circle at top right, rgba(6, 182, 212, 0.12), transparent 35%),
                linear-gradient(135deg, #f8fbff, #eef7ff);
            border: 1px solid rgba(159, 198, 244, 0.45);
        }

        .blik-box h3 {
            margin: 0 0 8px;
            color: #0f172a;
            font-size: 1.14rem;
        }

        .blik-box p {
            margin: 0 0 18px;
            color: #64748b;
            line-height: 1.55;
        }

        .blik-input {
            max-width: 260px;
            letter-spacing: 0.35em;
            text-align: center;
            font-size: 1.15rem !important;
            font-weight: 900;
        }

        .payment-actions {
            margin-top: 28px;
            display: flex;
            align-items: center;
            gap: 18px;
            flex-wrap: wrap;
        }

        .pay-button {
            min-height: 56px;
            padding: 0 28px;
            border: 0;
            border-radius: 17px;
            background: linear-gradient(135deg, #2563eb, #06b6d4);
            color: #ffffff;
            font-weight: 900;
            cursor: pointer;
            box-shadow: 0 16px 32px rgba(37, 99, 235, 0.24);
            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease,
                opacity 0.2s ease;
        }

        .pay-button:hover:not(:disabled) {
            transform: translateY(-3px);
            box-shadow: 0 20px 40px rgba(37, 99, 235, 0.31);
        }

        .pay-button:disabled {
            cursor: wait;
            opacity: 0.82;
        }

        .payment-status {
            display: none;
            align-items: center;
            gap: 14px;
            color: #475569;
            font-weight: 800;
        }

        .payment-status.visible {
            display: flex;
        }

        .payment-circle {
            --progress: 0deg;

            width: 62px;
            height: 62px;
            border-radius: 50%;
            background:
                conic-gradient(#2563eb var(--progress), #e2e8f0 0deg);

            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            box-shadow: 0 10px 26px rgba(37, 99, 235, 0.14);
        }

        .payment-circle::before {
            content: "";
            position: absolute;
            inset: 6px;
            border-radius: 50%;
            background: #ffffff;
        }

        .payment-circle span {
            position: relative;
            z-index: 1;
            color: #0f172a;
            font-size: 1.15rem;
            font-weight: 950;
        }

        .payment-circle.paid {
            background: conic-gradient(#22c55e 360deg, #e2e8f0 0deg);
        }

        .payment-circle.paid span {
            color: #16a34a;
        }

        .summary-card h2 {
            margin: 0 0 22px;
            color: #0f172a;
            font-size: 1.28rem;
            font-weight: 900;
        }

        .summary-route {
            padding: 20px;
            border-radius: 22px;
            background: linear-gradient(135deg, #eff6ff, #f8fafc);
            border: 1px solid rgba(191, 219, 254, 0.7);
        }

        .summary-route strong {
            display: block;
            color: #0f172a;
            font-size: 1.08rem;
            margin-bottom: 10px;
        }

        .summary-route p {
            margin: 7px 0;
            color: #475569;
        }

        .summary-seats {
            margin-top: 22px;
        }

        .summary-seats h3 {
            margin: 0 0 12px;
            color: #0f172a;
            font-size: 1rem;
            font-weight: 850;
        }

        .seat-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 9px;
        }

        .seat-badge {
            padding: 9px 12px;
            border-radius: 999px;
            background: rgba(37, 99, 235, 0.1);
            color: #1d4ed8;
            font-weight: 850;
            font-size: 0.86rem;
        }

        .summary-total {
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
        }

        .summary-total span {
            color: #64748b;
            font-weight: 750;
        }

        .summary-total strong {
            color: #0f172a;
            font-size: 1.55rem;
            font-weight: 950;
        }

        .demo-note {
            margin-top: 18px;
            padding: 14px 16px;
            border-radius: 18px;
            background: rgba(255, 247, 237, 0.9);
            color: #9a3412;
            font-size: 0.88rem;
            line-height: 1.5;
            font-weight: 650;
        }

        .form-errors {
            margin-bottom: 22px;
            padding: 16px 18px;
            border-radius: 18px;
            background: #fef2f2;
            color: #991b1b;
            font-weight: 700;
        }

        .form-errors ul {
            margin: 8px 0 0;
            padding-left: 20px;
        }

        @media (max-width: 980px) {
            .checkout-grid {
                grid-template-columns: 1fr;
            }

            .summary-card {
                position: static;
            }
        }

        @media (max-width: 620px) {
            .checkout-page {
                padding-top: 120px;
            }

            .checkout-header h1 {
                font-size: 32px;
            }

            .checkout-card,
            .summary-card {
                padding: 22px;
                border-radius: 24px;
            }

            .checkout-form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@section('content')
    <section class="checkout-page">
        <div class="checkout-container">

            <div class="checkout-header">
                <span>Rezerwacja demo</span>

                <h1>Dane pasażera i płatność</h1>

                <p>
                    Uzupełnij dane, wpisz 6-cyfrowy kod BLIK i zatwierdź symulowaną płatność.
                </p>
            </div>

            <div class="checkout-grid">

                <div class="checkout-card">
                    <h2 class="checkout-section-title">Dane do biletu</h2>

                    @if ($errors->any())
                        <div class="form-errors">
                            Popraw poniższe pola:
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form
                        id="checkoutPaymentForm"
                        method="POST"
                        action="{{ route('checkout.store', [
                            'trip' => $trip,
                            'selection' => $selectionToken ?? request('selection'),
                        ]) }}"
                    >
                        @csrf
                        action="{{ route('checkout.store', [
                            'trip' => $trip,
                            'selection' => $selectionToken ?? request('selection'),
                        ]) }}"

                        <div class="checkout-form-grid">
                            <div class="form-field full">
                                <label for="guestName">Imię i nazwisko</label>

                                <input
                                    id="guestName"
                                    type="text"
                                    name="guest_name"
                                    value="{{ old('guest_name', auth()->user()?->name) }}"
                                    placeholder="Jan Kowalski"
                                    minlength="3"
                                    maxlength="120"
                                    required
                                >
                            </div>

                            <div class="form-field">
                                <label for="guestEmail">Adres e-mail</label>

                                <input
                                    id="guestEmail"
                                    type="email"
                                    name="guest_email"
                                    value="{{ old('guest_email', auth()->user()?->email) }}"
                                    placeholder="jan@example.com"
                                    maxlength="160"
                                    required
                                >
                            </div>

                            <div class="form-field">
                                <label for="guestPhone">Telefon</label>

                                <input
                                    id="guestPhone"
                                    type="tel"
                                    name="guest_phone"
                                    value="{{ old('guest_phone', auth()->user()?->phone) }}"
                                    placeholder="500 600 700"
                                    pattern="[0-9 +]{7,20}"
                                    required
                                >
                            </div>
                        </div>

                        <div class="blik-box">
                            <h3>Płatność BLIK</h3>

                            <p>
                                Wpisz dowolny 6-cyfrowy kod. W wersji demo płatność zostanie
                                automatycznie zaakceptowana po 5 sekundach.
                            </p>

                            <div class="form-field">
                                <label for="blikCode">Kod BLIK</label>

                                <input
                                    id="blikCode"
                                    class="blik-input"
                                    type="text"
                                    name="blik_code"
                                    inputmode="numeric"
                                    pattern="[0-9]{6}"
                                    maxlength="6"
                                    placeholder="000000"
                                    autocomplete="one-time-code"
                                    required
                                >
                            </div>
                        </div>

                        <div class="payment-actions">
                            <button
                                id="payButton"
                                type="submit"
                                class="pay-button"
                            >
                                Zapłać {{ number_format($totalPrice, 2, ',', ' ') }} zł
                            </button>

                            <div
                                id="paymentStatus"
                                class="payment-status"
                                aria-live="polite"
                            >
                                <div
                                    id="paymentCircle"
                                    class="payment-circle"
                                    style="--progress: 0deg"
                                >
                                    <span id="paymentTimer">5</span>
                                </div>

                                <p id="paymentStatusText">
                                    Oczekujemy na potwierdzenie płatności...
                                </p>
                            </div>
                        </div>
                    </form>
                </div>

                <aside class="summary-card">
                    <h2>Podsumowanie</h2>

                    <div class="summary-route">
                        <strong>
                            {{ $trip->origin_station }}
                            →
                            {{ $trip->destination_station }}
                        </strong>

                        <p>
                            🚄 {{ $trip->train->name ?? 'RailTicket Demo' }}
                            {{ $trip->train->code ? '· ' . $trip->train->code : '' }}
                        </p>

                        <p>
                            {{ $trip->departure_at->format('d.m.Y') }}
                            ·
                            {{ $trip->departure_at->format('H:i') }}
                            →
                            {{ $trip->arrival_at->format('H:i') }}
                        </p>
                    </div>

                    <div class="summary-seats">
                        <h3>Wybrane miejsca</h3>

                        <div class="seat-badges">
                            @foreach ($seats as $seat)
                                <span class="seat-badge">
                                    Wagon {{ $seat->wagon->number }},
                                    {{ $seat->seat_number }}
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <div class="summary-total">
                        <span>Do zapłaty</span>

                        <strong>
                            {{ number_format($totalPrice, 2, ',', ' ') }} zł
                        </strong>
                    </div>

                    <div class="demo-note">
                        To jest symulowana płatność. Kod BLIK nie jest nigdzie realnie
                        weryfikowany ani obciążany.
                    </div>
                </aside>

            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('#checkoutPaymentForm');
            const payButton = document.querySelector('#payButton');
            const paymentStatus = document.querySelector('#paymentStatus');
            const paymentCircle = document.querySelector('#paymentCircle');
            const paymentTimer = document.querySelector('#paymentTimer');
            const paymentStatusText = document.querySelector('#paymentStatusText');
            const blikInput = document.querySelector('#blikCode');

            if (!form || !payButton || !paymentStatus || !paymentCircle || !paymentTimer || !paymentStatusText) {
                return;
            }

            let isProcessing = false;

            blikInput?.addEventListener('input', () => {
                blikInput.value = blikInput.value.replace(/\D/g, '').slice(0, 6);
            });

            form.addEventListener('submit', (event) => {
                if (isProcessing) {
                    return;
                }

                event.preventDefault();

                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }

                isProcessing = true;

                payButton.disabled = true;
                payButton.textContent = 'Oczekujemy na potwierdzenie płatności...';

                paymentStatus.classList.add('visible');

                const totalSeconds = 5;
                const startedAt = Date.now();

                const interval = window.setInterval(() => {
                    const elapsedMs = Date.now() - startedAt;
                    const elapsedSeconds = elapsedMs / 1000;
                    const progress = Math.min(elapsedSeconds / totalSeconds, 1);
                    const remaining = Math.max(Math.ceil(totalSeconds - elapsedSeconds), 0);

                    paymentCircle.style.setProperty('--progress', `${progress * 360}deg`);
                    paymentTimer.textContent = String(remaining);

                    if (progress >= 1) {
                        window.clearInterval(interval);

                        paymentCircle.classList.add('paid');
                        paymentTimer.textContent = '✓';

                        payButton.textContent = 'Zapłacone!';
                        paymentStatusText.textContent = 'Zapłacone! Przekierowujemy do potwierdzenia...';

                        window.setTimeout(() => {
                            HTMLFormElement.prototype.submit.call(form);
                        }, 750);
                    }
                }, 80);
            });
        });
    </script>
@endpush