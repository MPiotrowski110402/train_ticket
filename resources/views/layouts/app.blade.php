<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'RailTicket')</title>
    <meta name="description" content="@yield('meta-description', 'Demonstracyjny system sprzedaży biletów kolejowych RailTicket')">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    {{-- Oryginalny wspólny styl: tło, loader, nawigacja i animacje strony startowej. --}}
    <link rel="stylesheet" href="{{ asset('css/welcome.css') }}">

    {{-- Style specyficzne dla danej podstrony. --}}
    @stack('styles')
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body>
    @include('partials.loader')

    <div class="scroll-progress" aria-hidden="true"></div>

    <div class="background-wrapper" aria-hidden="true">
        <div class="orb-motion"><div class="gradient-orb orb-one"></div></div>
        <div class="orb-motion"><div class="gradient-orb orb-two"></div></div>
        <div class="orb-motion"><div class="gradient-orb orb-three"></div></div>
        <div class="grid-background"></div>
        <div class="noise"></div>
    </div>

    @include('partials.navbar')

    <main>
        @yield('content')
    </main>

    {{-- Pełny, przywrócony skrypt animacji pierwotnego widoku. --}}
    <script src="{{ asset('js/welcome.js') }}"></script>

    {{-- Skrypty konkretnej podstrony (np. wybór miejsca lub formularz kontaktowy). --}}
    @stack('scripts')
</body>

</html>
