<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'SmarTern')</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('images/white-smartern.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script>
        (function() {
            const isDark = localStorage.getItem('darkMode') === 'true' ||
                          (!('darkMode' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.classList.toggle('dark', isDark);

            const fontSize = localStorage.getItem('fontSize') || 'md';
            document.documentElement.classList.add('font-size-' + fontSize);
        })();
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap" rel="stylesheet">

    <style>
        html { scroll-behavior: smooth; }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-950 antialiased text-gray-900 dark:text-gray-100 transition-colors duration-300">

    {{-- Flash Messages --}}
    @if(session('success'))
        <x-toast :message="session('success')" type="success" />
    @endif
    @if(session('error'))
        <x-toast :message="session('error')" type="error" />
    @endif

    <div id="app">
        @yield('content')
    </div>

    @stack('scripts')
</body>
</html>
