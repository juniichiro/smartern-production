@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="min-h-screen w-full flex flex-col lg:flex-row relative overflow-hidden bg-gray-50 dark:bg-gray-950 transition-colors duration-300">
    
    {{-- Back to Landing --}}
    <a href="{{ url('/') }}" class="absolute top-6 left-6 lg:left-24 z-[100] p-3 bg-white/80 dark:bg-gray-900/80 lg:bg-white/10 lg:dark:bg-white/10 backdrop-blur-md border border-gray-200 dark:border-gray-800 lg:border-white/20 lg:dark:border-white/10 rounded-2xl text-gray-700 dark:text-gray-200 lg:text-white/90 lg:dark:text-white/90 hover:bg-white dark:hover:bg-gray-800 hover:lg:bg-white hover:lg:text-[#0B5694] transition-all active:scale-95 shadow-xl flex items-center gap-2 text-[11px] font-bold uppercase tracking-wider">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        <span class="hidden sm:inline">Back</span>
    </a>

    {{-- Dark Mode Toggle --}}
    <div class="absolute top-6 right-6 z-[100]" x-data="darkModeManager">
        <button @click="darkMode = !darkMode" 
                class="p-3 bg-white/90 dark:bg-white/10 backdrop-blur-md border border-gray-200 dark:border-white/10 rounded-2xl text-[#0B5694] dark:text-white hover:bg-white dark:hover:bg-white/20 transition-all active:scale-95 shadow-xl">
            <svg x-show="!darkMode" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
            </svg>
            <svg x-show="darkMode" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
            </svg>
        </button>
    </div>

    {{-- LEFT COLUMN: Image background with brand gradient overlay --}}
    <div class="relative hidden lg:flex lg:w-7/12 flex-col justify-between px-24 py-16 z-10 select-none overflow-hidden">
        {{-- Background image layer with local sdo.png and brand gradient --}}
        <div class="absolute inset-0 z-0">
            <img src="{{ asset('images/sdo.png') }}" alt="SDO Background" class="w-full h-full object-cover brightness-[0.45] dark:brightness-[0.3]">
            <div class="absolute inset-0 bg-gradient-to-b from-[#07365c]/95 via-[#0b5694]/85 to-[#0b5694]/75 dark:from-[#031c30]/95 dark:via-blue-950/90 dark:to-blue-900/80 transition-colors duration-300"></div>
        </div>

        <div></div>

        {{-- Center Content --}}
        <div class="relative z-10 max-w-lg my-auto space-y-6">
            <span class="text-[11px] font-extrabold uppercase tracking-[0.25em] text-blue-200">Welcome To</span>
            <h2 class="text-6xl font-black tracking-tight leading-none text-white font-sans drop-shadow-md">SmarTern</h2>
            <p class="text-sm font-medium leading-relaxed text-white/95 max-w-md">
                A centralized platform for qualification-based placement, real-time progress tracking, secure documentation, and data-driven reporting—built for a smoother internship experience.
            </p>
            <div class="pt-4">
                <a href="{{ route('onboarding.create') }}" class="inline-flex items-center justify-center px-10 py-3 border-2 border-white/60 hover:border-white hover:bg-white/10 text-white font-bold text-xs uppercase tracking-widest rounded-xl transition-all duration-300 active:scale-[0.98] shadow-lg shadow-black/10">
                    Start
                </a>
            </div>
        </div>

        {{-- Footer --}}
        <div class="relative z-10 text-[10px] font-bold uppercase tracking-[0.2em] text-blue-200">
            SmarTern | SDO Quezon City
        </div>
    </div>

    {{-- RIGHT COLUMN: Solid background with Login form --}}
    <div class="relative flex-1 flex items-center justify-center p-6 sm:p-12 z-10 bg-gray-50 dark:bg-gray-950 transition-colors duration-300">
        
        {{-- For mobile views, we can display the background image as overlay so mobile login doesn't feel plain --}}
        <div class="absolute inset-0 z-0 lg:hidden">
            <img src="{{ asset('images/sdo.png') }}" alt="SDO Background" class="w-full h-full object-cover brightness-[0.4] dark:brightness-[0.25]">
            <div class="absolute inset-0 bg-gradient-to-t from-gray-50/95 via-gray-50/75 to-gray-50/95 dark:from-gray-950/95 dark:via-gray-950/75 dark:to-gray-950/95 transition-colors duration-300"></div>
        </div>

        {{-- Login form envelope card --}}
        <div class="relative z-10 w-full max-w-[450px] bg-white/95 dark:bg-gray-900/95 border border-gray-100/50 dark:border-gray-800 rounded-[2.5rem] shadow-2xl p-8 sm:p-12 transition-all duration-300 {{ $errors->any() ? 'animate-shake' : '' }}">
            
            <header class="text-center mb-8">
                <h1 class="text-3xl font-extrabold text-[#0B5694] dark:text-blue-400 tracking-tight leading-none mb-2">Login</h1>
                {{-- Quick mobile onboarding helper --}}
                <p class="lg:hidden text-xs text-gray-500 dark:text-gray-400 mt-2">
                    New intern? <a href="{{ route('onboarding.create') }}" class="text-[#0B5694] dark:text-blue-400 font-bold hover:underline">Start your application here</a>
                </p>
            </header>

            @if ($errors->any())
                <div x-data="{ show: true }" 
                     x-show="show" 
                     x-init="setTimeout(() => show = false, 8000)"
                     class="mb-6 p-4 bg-red-50 dark:bg-red-950/30 rounded-2xl border border-red-100 dark:border-red-900/50 flex items-center gap-3">
                    
                    <div class="flex-shrink-0 w-8 h-8 bg-red-500 rounded-full flex items-center justify-center shadow-lg shadow-red-500/10">
                        <svg xmlns="http://www.w3.org/2000/svg" class="text-white" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </div>
                    <div>
                        @php 
                            $firstError = $errors->first();
                            $isLocked = Str::contains(Str::lower($firstError), ['locked', 'deactivated', 'suspicious', 'try again']); 
                        @endphp
                        <p class="text-[11px] font-bold text-red-600 dark:text-red-400 uppercase tracking-wider leading-none">
                            {{ $isLocked ? 'Account Locked' : 'Access Denied' }}
                        </p>
                        <p class="text-[10px] text-red-400 dark:text-red-500 font-bold uppercase tracking-tight mt-1">
                            {{ $firstError }}
                        </p>
                    </div>
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST" class="space-y-6">
                @csrf

                {{-- Email Input --}}
                <div class="text-left">
                    <label for="email" class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase block mb-1.5 tracking-wider ml-1">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" placeholder="your.email@smartern.com" required 
                        class="w-full bg-gray-50 dark:bg-gray-800 {{ $errors->has('email') ? 'bg-red-50/30 dark:bg-red-900/10 ring-1 ring-red-100 dark:ring-red-900/50 border-red-200' : 'border border-gray-100 dark:border-gray-700' }} rounded-xl px-5 py-3.5 text-sm focus:ring-2 focus:ring-[#0B5694] dark:focus:ring-blue-500 transition-all font-medium dark:text-gray-200">
                </div>

                {{-- Password Input --}}
                <div class="text-left">
                    <label for="password" class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase block mb-1.5 tracking-wider ml-1">Password</label>
                    <div class="relative" x-data="{ show: false }">
                        <input :type="show ? 'text' : 'password'" name="password" id="password" placeholder="Enter your password" required 
                            class="w-full bg-gray-50 dark:bg-gray-800 {{ $errors->has('password') ? 'bg-red-50/30 dark:bg-red-900/10 ring-1 ring-red-100 dark:ring-red-900/50 border-red-200' : 'border border-gray-100 dark:border-gray-700' }} rounded-xl pl-5 pr-12 py-3.5 text-sm focus:ring-2 focus:ring-[#0B5694] dark:focus:ring-blue-500 transition-all font-medium dark:text-gray-200">
                        <button type="button" @click="show = !show" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                            <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg x-show="show" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="display: none;"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.52 13.52 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" y1="2" x2="22" y2="22"/></svg>
                        </button>
                    </div>
                </div>

                {{-- Sign In CTA --}}
                <div class="pt-2 space-y-3">
                    <button type="submit" class="w-full bg-[#0b5694] dark:bg-blue-600 hover:bg-[#084577] dark:hover:bg-blue-700 text-white py-4 rounded-xl font-bold uppercase text-[11px] tracking-wider transition-all duration-300 active:scale-[0.98] shadow-lg shadow-blue-500/20">
                        Sign In
                    </button>
                    
                    <a href="{{ route('templates.index') }}" class="w-full border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 text-gray-600 dark:text-gray-300 py-3.5 rounded-xl font-bold uppercase text-[10px] tracking-wider transition-all duration-300 flex items-center justify-center gap-2 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                        Request Letter Templates
                    </a>
                </div>
 
                <div class="text-center pt-2">
                    <a href="{{ route('password.request') }}" class="text-[10px] font-bold text-gray-400 dark:text-gray-500 hover:text-[#0B5694] dark:hover:text-blue-400 transition-colors uppercase tracking-wider">Forgot password?</a>
                </div>
            </form>
        </div>
    </div>
</div>

@if(request()->has('timeout'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                if (window.showToast) {
                    window.showToast('Your session has timed out. Please sign in again.', 'info');
                }
                const url = new URL(window.location);
                url.searchParams.delete('timeout');
                window.history.replaceState({}, document.title, url.pathname + url.search);
            }, 500);
        });
    </script>
@endif

@if(request()->has('taken_over') || session('session_taken_over'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                if (window.showToast) {
                    window.showToast('Your account was logged in on another device. This session has been terminated.', 'error');
                }
                const url = new URL(window.location);
                url.searchParams.delete('taken_over');
                window.history.replaceState({}, document.title, url.pathname + url.search);
            }, 500);
        });
    </script>
@endif

@endsection