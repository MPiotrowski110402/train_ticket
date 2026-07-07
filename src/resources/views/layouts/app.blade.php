<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'RailTicket')</title>

    <meta name="description"
          content="Nowoczesny system sprzedaży biletów kolejowych">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect"
          href="https://fonts.gstatic.com"
          crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"
          rel="stylesheet">

    <link rel="stylesheet"
          href="{{ asset('css/welcome.css') }}">

    <link rel="stylesheet" href="{{ asset('css/connections.css') }}">
</head>

<body>

    {{-- Loader --}}
    @include('partials.loader')

    {{-- Scroll Progress --}}
    <div class="scroll-progress"></div>

    {{-- Animated Background --}}
    <div class="background-wrapper">

        <div class="gradient-orb orb-one"></div>
        <div class="gradient-orb orb-two"></div>
        <div class="gradient-orb orb-three"></div>

        <div class="grid-background"></div>

        <div class="noise"></div>

    </div>

    @include('partials.navbar')

    <main>

        @yield('content')

    </main>

    <script src="{{ asset('js/welcome.js') }}"></script>
    <script src="{{ asset('js/connections.js') }}"></script>

</body>

</html>