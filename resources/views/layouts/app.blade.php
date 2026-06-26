<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title')</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('images/white-smartern.png') }}">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script>
        /**
         * Flicker Prevention: Apply Dark Mode and Font Size immediately
         * And enforce browser-instance level single session check via localStorage.
         */
        (function() {
            const isDark = localStorage.getItem('darkMode') === 'true' || 
                          (!('darkMode' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.classList.toggle('dark', isDark);

            const fontSize = localStorage.getItem('fontSize') || 'md';
            document.documentElement.classList.add('font-size-' + fontSize);

            // Client-side Unique Device / Browser Profile Binding
            let clientId = localStorage.getItem('smartern_client_id');
            if (!clientId) {
                clientId = 'cli_' + Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
                localStorage.setItem('smartern_client_id', clientId);
            }

            // Parse cookies to verify if smartern_client_id matches our local profile storage
            const cookies = document.cookie.split('; ').reduce((acc, cookie) => {
                const parts = cookie.split('=');
                const name = parts[0] ? parts[0].trim() : '';
                const value = parts[1] ? parts[1].trim() : '';
                if (name) {
                    acc[name] = value;
                }
                return acc;
            }, {});

            const cookieClientId = cookies['smartern_client_id'];
            const hasSession = cookies['smartern-session'];

            // Only trigger cookie mismatch redirect if they possess an active session cookie.
            if (hasSession && cookieClientId && cookieClientId !== clientId) {
                // Clear the session cookies immediately and reload
                document.cookie = "smartern_device_id=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
                document.cookie = "smartern_client_id=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
                document.cookie = "smartern-session=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
                window.location.href = '{{ route('login') }}?taken_over=1';
                return;
            }

            // Write/sync the cookie value to our unique local client ID
            document.cookie = "smartern_client_id=" + clientId + "; path=/; Secure; SameSite=Lax";
        })();
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-50 dark:bg-gray-950 antialiased text-gray-900 dark:text-gray-100 transition-colors duration-300">

    <div id="app">
        {{-- Global Floating Notifications (Flash) --}}
        @if(session('success'))
            <x-toast :message="session('success')" type="success" />
        @endif

        @if(session('error'))
            <x-toast :message="session('error')" type="error" />
        @endif

        @if(session('status'))
            <x-toast :message="session('status')" type="info" />
        @endif

        {{-- Dynamic Toasts for Real-Time & System Events --}}
        <x-toast-display />

        @yield('content')

        {{-- Privacy Policy & Consent Modal --}}
        <x-privacy-modal />

        @auth
            {{-- Real-Time Security Lockout Listener --}}
            <div x-data="{
                init() {
                    if (window.Echo) {
                        window.Echo.private('App.Models.User.{{ Auth::id() }}')
                            .listen('.account.locked', (e) => {
                                window.location.href = '{{ route('login') }}';
                            });
                    }
                }
            }" x-init="init()"></div>

            {{-- Session Timeout Manager --}}
            <div x-data="{ 
                    showModal: false,
                    showTakenOverModal: false,
                    lastActivity: Date.now(),
                    // 30 minutes in milliseconds
                    timeout: {{ config('session.lifetime') }} * 60 * 1000,
                    
                    init() {
                        // Reset activity on init to prevent immediate timeout from guest AFK time
                        this.lastActivity = Date.now();
                        localStorage.setItem('smartern_last_activity', this.lastActivity);

                        // Global activity listeners
                        ['mousedown', 'keydown', 'scroll', 'touchstart'].forEach(event => {
                            window.addEventListener(event, () => {
                                this.lastActivity = Date.now();
                                localStorage.setItem('smartern_last_activity', this.lastActivity);
                            }, { passive: true });
                        });

                        // Heartbeat check every 10 seconds
                        setInterval(() => {
                            if (this.showTakenOverModal) return;

                            // 1. Check if the session is taken over by another device
                            fetch('{{ route('session.ping') }}', {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json'
                                }
                            })
                            .then(response => {
                                if (response.status === 401) {
                                    return response.json().then(data => {
                                        if (data.error === 'session_taken_over') {
                                            this.showTakenOverModal = true;
                                        } else {
                                            window.location.href = '{{ route('login') }}?timeout=1';
                                        }
                                    });
                                }
                            })
                            .catch(err => console.error('Session check failed', err));

                            // 2. Local inactivity check
                            const storedActivity = parseInt(localStorage.getItem('smartern_last_activity') || Date.now());
                            const inactiveDuration = Date.now() - storedActivity;

                            if (inactiveDuration >= this.timeout && !this.showModal && !this.showTakenOverModal) {
                                this.showModal = true;
                                // Auto-redirect after 10 seconds of showing the modal
                                setTimeout(() => {
                                    if (!this.showTakenOverModal) {
                                        this.logoutAndRedirect();
                                    }
                                }, 10000);
                            }
                        }, 10000);
                    },

                    logoutAndRedirect() {
                        const csrfToken = document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content');
                        fetch('{{ route('logout') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            }
                        }).finally(() => {
                            window.location.href = '{{ route('login') }}?timeout=1';
                        });
                    }
                 }"
                 x-init="init()"
                 x-show="showModal"
                 x-cloak
                 class="fixed inset-0 z-[999] flex items-center justify-center p-4 bg-gray-900/60 dark:bg-black/80 backdrop-blur-md"
                 style="display: none;"
                 @keydown.escape.window.prevent
            >
                <div class="w-full max-w-md bg-white dark:bg-gray-900 rounded-[32px] border border-gray-100 dark:border-gray-800 shadow-2xl p-8 text-center"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                >
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-amber-50 dark:bg-amber-900/20 text-amber-500 rounded-[24px] mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                    
                    <h2 class="text-2xl font-extrabold text-gray-900 dark:text-white uppercase tracking-normal mb-2">Session Expired</h2>
                    <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-6">Inactivity Timeout Detected</p>
                    
                    <p class="text-sm text-gray-600 dark:text-gray-300 font-medium leading-relaxed mb-8">
                        Your session has timed out due to inactivity for security reasons. You will be redirected to the login page momentarily.
                    </p>

                    <button @click="logoutAndRedirect()" 
                       class="inline-flex items-center justify-center w-full px-6 py-4 bg-[#0B5694] hover:bg-[#073D6B] dark:bg-blue-600 dark:hover:bg-blue-500 text-white font-bold uppercase text-[10px] tracking-wider rounded-2xl transition-all shadow-md active:scale-95 cursor-pointer">
                        Return to Login
                    </button>
                </div>
            </div>

            {{-- Duplicate Session / Taken Over Warning Modal --}}
            <div x-show="showTakenOverModal"
                 x-cloak
                 class="fixed inset-0 z-[999] flex items-center justify-center p-4 bg-gray-900/60 dark:bg-black/80 backdrop-blur-md"
                 style="display: none;"
                 @keydown.escape.window.prevent
            >
                <div class="w-full max-w-md bg-white dark:bg-gray-900 rounded-[32px] border border-gray-100 dark:border-gray-800 shadow-2xl p-8 text-center"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                >
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-red-50 dark:bg-red-900/20 text-red-500 rounded-[24px] mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                            <line x1="12" y1="9" x2="12" y2="13"/>
                            <line x1="12" y1="17" x2="12.01" y2="17"/>
                        </svg>
                    </div>
                    
                    <h2 class="text-2xl font-extrabold text-gray-900 dark:text-white uppercase tracking-normal mb-2">Logged Out</h2>
                    <p class="text-[10px] font-bold text-red-500 dark:text-red-400 uppercase tracking-wider mb-6">Duplicate Session Detected</p>
                    
                    <p class="text-sm text-gray-600 dark:text-gray-300 font-medium leading-relaxed mb-8">
                        Your account was logged in on another device or browser. This session has been terminated.
                    </p>

                    <a href="{{ route('login') }}?taken_over=1" 
                       class="inline-flex items-center justify-center w-full px-6 py-4 bg-red-600 hover:bg-red-700 text-white font-bold uppercase text-[10px] tracking-wider rounded-2xl transition-all shadow-md active:scale-95">
                        Return to Login
                    </a>
                </div>
            </div>
        @endauth
    </div>

    @stack('scripts')
    
</body>
</html>