<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @hasSection('meta_description')
        <meta name="description" content="@yield('meta_description')">
    @endif
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'BATITRAVOO'))</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/vitrine.css') }}">
</head>
<body>
    @include('vitrine.partials.btp_nav')

    <main>
        @yield('content')
    </main>

    @include('vitrine.partials.footer')

    <script src="{{ asset('js/vitrine.js') }}" defer></script>
    @stack('scripts')
</body>
</html>
