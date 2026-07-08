<header class="navbar">
    <div class="container">
        <a href="{{ route('home') }}" class="logo" aria-label="RailTicket — strona startowa">
            <div class="logo-icon" aria-hidden="true">🚄</div>
            <div class="logo-text">Rail<span>Ticket</span></div>
        </a>

        <nav aria-label="Główna nawigacja">
            <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'is-active' : '' }}">Start</a>
            <a href="{{ route('connections') }}" class="{{ request()->routeIs('connections') ? 'is-active' : '' }}">Połączenia</a>
            <a href="{{ route('promotions') }}" class="{{ request()->routeIs('promotions') ? 'is-active' : '' }}">Promocje</a>
            <a href="{{ route('contact') }}" class="{{ request()->routeIs('contact') ? 'is-active' : '' }}">Kontakt</a>
        </nav>

        <div class="navbar-buttons">
            <a
                id="demoAuthButton"
                href="{{ route('auth.index', ['redirect' => request()->fullUrl()]) }}"
                data-login-url="{{ route('auth.index', ['redirect' => request()->fullUrl()]) }}"
                data-logout-url="{{ route('auth.logout') }}"
                class="btn btn-light"
            >
                Zaloguj się
            </a>

            <a href="{{ route('connections') }}" class="btn btn-primary">Kup bilet</a>
        </div>
    </div>
</header>
