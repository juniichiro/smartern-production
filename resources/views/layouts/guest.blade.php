<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'SmarTern — SDO QC Internship Management System')</title>
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

    {{-- Dark Mode Toggle --}}
    <div class="fixed top-6 right-6 z-[100]" x-data="{ darkMode: document.documentElement.classList.contains('dark') }">
        <button @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode); document.documentElement.classList.toggle('dark', darkMode)"
                class="p-3 bg-white/90 dark:bg-white/10 backdrop-blur-md border border-gray-200 dark:border-white/10 rounded-2xl text-[#0B5694] dark:text-white hover:bg-white dark:hover:bg-white/20 transition-all active:scale-95 shadow-xl">
            <svg x-show="!darkMode" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
            </svg>
            <svg x-show="darkMode" x-cloak xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
            </svg>
        </button>
    </div>

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
