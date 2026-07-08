@extends('layouts.app')

@section('title', 'Logowanie demo')

@push('styles')
    <style>
        .auth-page {
            min-height: 100vh;
            padding: 150px 0 100px;
            background:
                radial-gradient(circle at top left, rgba(37, 99, 235, 0.12), transparent 35%),
                linear-gradient(180deg, #f8fafc, #ffffff);
        }

        .auth-container {
            width: min(520px, 92%);
            margin: 0 auto;
        }

        .auth-card {
            padding: 38px;
            border-radius: 32px;
            background: rgba(255, 255, 255, 0.86);
            border: 1px solid rgba(226, 232, 240, 0.9);
            box-shadow: 0 26px 75px rgba(15, 23, 42, 0.11);
            backdrop-filter: blur(24px);
        }

        .auth-badge {
            display: inline-flex;
            padding: 7px 14px;
            border-radius: 999px;
            background: rgba(37, 99, 235, 0.1);
            color: #2563eb;
            font-size: 0.78rem;
            font-weight: 900;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .auth-card h1 {
            margin: 16px 0 8px;
            color: #0f172a;
            font-size: 38px;
            letter-spacing: -1px;
        }

        .auth-card p {
            margin: 0 0 28px;
            color: #64748b;
            line-height: 1.6;
        }

        .auth-field {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 18px;
        }

        .auth-field label {
            color: #64748b;
            font-size: 0.86rem;
            font-weight: 750;
        }

        .auth-field input {
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
                box-shadow 0.22s ease;
        }

        .auth-field input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.11);
        }

        .auth-demo-box {
            margin: 24px 0;
            padding: 16px 18px;
            border-radius: 20px;
            background: rgba(239, 246, 255, 0.95);
            border: 1px solid rgba(191, 219, 254, 0.8);
            color: #1e3a8a;
            font-weight: 700;
            line-height: 1.55;
        }

        .auth-submit {
            width: 100%;
            min-height: 56px;
            border: 0;
            border-radius: 17px;
            background: linear-gradient(135deg, #2563eb, #06b6d4);
            color: #ffffff;
            font-weight: 950;
            cursor: pointer;
            box-shadow: 0 16px 32px rgba(37, 99, 235, 0.25);
            transition:
                transform 0.22s ease,
                box-shadow 0.22s ease,
                opacity 0.22s ease;
        }

        .auth-submit:hover:not(:disabled) {
            transform: translateY(-3px);
            box-shadow: 0 22px 42px rgba(37, 99, 235, 0.34);
        }

        .auth-submit.is-loading {
            cursor: wait;
        }

        .auth-error {
            margin-bottom: 18px;
            padding: 14px 16px;
            border-radius: 16px;
            background: #fef2f2;
            color: #991b1b;
            font-weight: 750;
        }

        .auth-back {
            margin-top: 20px;
            display: inline-flex;
            color: #2563eb;
            font-weight: 800;
        }
        .auth-submit.is-auto-clicking {
            transform: translateY(-3px) scale(0.98);
            box-shadow: 0 22px 42px rgba(37, 99, 235, 0.36);
        }

        .auth-submit.is-loading {
            cursor: wait;
            background: linear-gradient(135deg, #1d4ed8, #0891b2);
        }
    </style>
@endpush

@section('content')
    <section class="auth-page">
        <div class="auth-container">
            <div class="auth-card reveal">
                <span class="auth-badge">Demo login</span>

                <h1>Logowanie</h1>

                <p>
                    To ekran demonstracyjny. Dane zostaną wpisane automatycznie,
                    a system zaloguje pierwszego użytkownika testowego z bazy.
                </p>

                @if (session('error'))
                    <div class="auth-error">
                        {{ session('error') }}
                    </div>
                @endif

                <form id="demoLoginForm" method="POST" action="{{ route('auth.login') }}">
                    @csrf
                    <input type="hidden" name="redirect" value="{{ $redirect ?? route('home', [], false) }}">

                    <div class="auth-field">
                        <label for="email">Adres e-mail</label>

                        <input
                            id="email"
                            type="email"
                            name="email"
                            autocomplete="email"
                            required
                        >
                    </div>

                    <div class="auth-field">
                        <label for="password">Hasło</label>

                        <input
                            id="password"
                            type="password"
                            name="password"
                            autocomplete="current-password"
                            required
                        >
                    </div>

                    <div class="auth-demo-box">
                        Konto demo:
                        <br>
                        Testowy.user@test.pl
                        <br>
                        Hasło: 10 kropek
                    </div>

                    <button id="loginButton" type="submit" class="auth-submit">
                        Zaloguj
                    </button>
                </form>

                <a href="{{ route('home') }}" class="auth-back">
                    ← Wróć na stronę główną
                </a>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('#demoLoginForm');
            const emailInput = document.querySelector('#email');
            const passwordInput = document.querySelector('#password');
            const button = document.querySelector('#loginButton');

            if (!form || !emailInput || !passwordInput || !button) {
                return;
            }

            const demoUser = {
                name: 'Testowy User',
                email: 'testowy.user@test.pl',
                phone: '500 600 700',
            };

            const demoPassword = '..........';

            let hasStarted = false;

            const wait = (ms) => {
                return new Promise((resolve) => window.setTimeout(resolve, ms));
            };

            const clearFields = () => {
                emailInput.value = '';
                passwordInput.value = '';

                emailInput.setAttribute('autocomplete', 'off');
                passwordInput.setAttribute('autocomplete', 'off');
            };

            const typeValue = async (input, value, delay) => {
                input.value = '';

                for (const char of value) {
                    input.value += char;

                    input.dispatchEvent(new Event('input', {
                        bubbles: true,
                    }));

                    await wait(delay);
                }
            };

            const getRedirectUrl = () => {
                const redirectInput = form.querySelector('[name="redirect"]');

                let redirectUrl = redirectInput?.value || '/';

                /*
                 * Jeżeli przypadkiem redirect prowadzi znowu do /auth,
                 * to wracamy na stronę główną.
                 */
                if (redirectUrl.includes('/auth')) {
                    redirectUrl = '/';
                }

                const separator = redirectUrl.includes('?') ? '&' : '?';

                return `${redirectUrl}${separator}demo_logged=1&t=${Date.now()}`;
            };

            const finishLogin = async () => {
                button.disabled = true;
                button.classList.remove('is-auto-clicking');
                button.classList.add('is-loading');
                button.textContent = 'Trwa logowanie...';

                await wait(900);

                localStorage.setItem(
                    'railticket_demo_user',
                    JSON.stringify(demoUser)
                );

                button.textContent = 'Zalogowano ✓';

                await wait(500);

                window.location.replace(getRedirectUrl());
            };

            const runLoginAnimation = async () => {
                if (hasStarted) {
                    return;
                }

                hasStarted = true;

                clearFields();

                button.disabled = true;
                button.textContent = 'Przygotowuję dane...';

                await wait(500);

                await typeValue(emailInput, demoUser.email, 95);

                await wait(350);

                await typeValue(passwordInput, demoPassword, 145);

                await wait(450);

                button.disabled = false;
                button.textContent = 'Zaloguj';
                button.classList.add('is-auto-clicking');

                await wait(600);

                button.disabled = true;
                button.classList.remove('is-auto-clicking');

                await finishLogin();
            };

            form.addEventListener('submit', (event) => {
                event.preventDefault();
            });

            button.addEventListener('click', (event) => {
                event.preventDefault();

                if (!button.classList.contains('is-loading')) {
                    finishLogin();
                }
            });

            runLoginAnimation();
        });
    </script>
@endpush