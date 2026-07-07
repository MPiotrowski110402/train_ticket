@extends('layouts.app')

@section('title', 'Kontakt — RailTicket')
@section('meta-description', 'Kontakt do demonstracyjnego systemu RailTicket.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/contact.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('js/contact.js') }}"></script>
@endpush

@section('content')
    <section class="contact-page">
        <div class="container">
            <header class="page-intro reveal">
                <span class="page-eyebrow">Centrum pomocy</span>
                <h1>Skontaktuj się z nami.</h1>
                <p>
                    Masz pytanie dotyczące połączenia, rezerwacji albo działania aplikacji?
                    Poniżej znajduje się prezentacyjny kontakt i formularz demo.
                </p>
            </header>

            <div class="contact-grid">
                <aside class="contact-panel reveal">
                    <span class="contact-panel-label">RailTicket Demo</span>
                    <h2>Pomoc dla podróżnych</h2>
                    <p>Przykładowe dane kontaktowe, które możemy później zastąpić właściwą obsługą klienta.</p>

                    <div class="contact-details">
                        <a class="contact-detail" href="mailto:kontakt@railticket.demo">
                            <span class="contact-detail-icon" aria-hidden="true">✉️</span>
                            <span>
                                <small>E-mail</small>
                                <strong>kontakt@railticket.demo</strong>
                            </span>
                        </a>

                        <a class="contact-detail" href="tel:+48123456789">
                            <span class="contact-detail-icon" aria-hidden="true">☎️</span>
                            <span>
                                <small>Infolinia demo</small>
                                <strong>+48 123 456 789</strong>
                            </span>
                        </a>

                        <div class="contact-detail">
                            <span class="contact-detail-icon" aria-hidden="true">🕒</span>
                            <span>
                                <small>Godziny dostępności</small>
                                <strong>Pon.–Pt., 8:00–18:00</strong>
                            </span>
                        </div>
                    </div>

                    <div class="contact-panel-footer">
                        <span aria-hidden="true">🔒</span>
                        Dane na tej stronie są wyłącznie przykładowe.
                    </div>
                </aside>

                <div class="contact-form-card reveal">
                    <div class="form-heading">
                        <span class="page-eyebrow">Formularz demo</span>
                        <h2>Napisz wiadomość</h2>
                        <p>Wysłanie formularza wyświetli lokalne potwierdzenie — bez faktycznej wysyłki wiadomości.</p>
                    </div>

                    <form id="contact-form" class="contact-form" novalidate>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="contact-name">Imię i nazwisko</label>
                                <input id="contact-name" name="name" type="text" placeholder="np. Jan Kowalski" required>
                            </div>

                            <div class="form-group">
                                <label for="contact-email">Adres e-mail</label>
                                <input id="contact-email" name="email" type="email" placeholder="jan@przyklad.pl" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="contact-topic">Temat</label>
                            <select id="contact-topic" name="topic" required>
                                <option value="">Wybierz temat</option>
                                <option value="connection">Pytanie o połączenie</option>
                                <option value="ticket">Pytanie o bilet</option>
                                <option value="technical">Problem techniczny</option>
                                <option value="other">Inna sprawa</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="contact-message">Wiadomość</label>
                            <textarea id="contact-message" name="message" rows="6" placeholder="Opisz krótko swoją sprawę..." required></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary contact-submit">Wyślij wiadomość</button>
                        <p id="contact-form-status" class="form-status" role="status" aria-live="polite"></p>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
