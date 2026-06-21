@extends('layouts.app')

@section('title', 'Authentication Code')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-[#0B5694] dark:bg-gray-950 text-white p-6 relative overflow-hidden transition-colors duration-300">
    
    {{-- Dark Mode Toggle --}}
    <div class="absolute top-6 right-6 z-[100]" x-data="darkModeManager">
        <button @click="darkMode = !darkMode" 
                class="p-3 bg-white/10 dark:bg-white/5 backdrop-blur-md border border-white/20 dark:border-white/10 rounded-2xl text-white hover:bg-white/20 dark:hover:bg-white/10 transition-all active:scale-95 shadow-xl">
            <svg x-show="!darkMode" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
            </svg>
            <svg x-show="darkMode" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
            </svg>
        </button>
    </div>

    <div class="text-center w-full max-w-sm">
        <div class="mb-10">
            <div class="w-20 h-20 bg-white/10 dark:bg-gray-800/50 rounded-[30px] flex items-center justify-center mx-auto mb-6 border-2 border-white/20 dark:border-gray-800 shadow-2xl">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/></svg>
            </div>
            <h1 class="text-3xl font-extrabold uppercase tracking-normal">SmarTern</h1>
            <p class="text-white/60 dark:text-gray-400 text-xs font-bold uppercase tracking-wider mt-2">Login Verification</p>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-[40px] p-10 shadow-2xl border border-white/10 dark:border-gray-800">
            <p class="text-[10px] text-gray-400 dark:text-gray-500 font-bold uppercase tracking-[0.2em] mb-6">Your One-Time Password</p>
            
            <div class="text-5xl font-extrabold text-[#0B5694] dark:text-blue-400 tracking-[0.2em] mb-8 font-mono">
                {{ $otp }}
            </div>

            <div class="space-y-4">
                <p class="text-xs text-gray-500 dark:text-gray-400 italic font-medium leading-relaxed px-4">
                    Enter this code in the login screen to access your account.
                </p>
                <div class="pt-6">
                    <button onclick="location.reload()" class="w-full bg-[#0B5694] dark:bg-blue-600 text-white py-4 rounded-2xl font-bold text-xs uppercase tracking-wider shadow-xl active:scale-95 transition-all">
                        Refresh Code
                    </button>
                </div>
            </div>
        </div>

        <p class="mt-10 text-[10px] text-white/40 uppercase tracking-[0.3em] font-bold italic">Code expires in 30 seconds</p>
    </div>
</div>
@endsection
