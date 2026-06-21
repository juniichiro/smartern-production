@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="min-h-screen w-full flex items-center justify-center relative overflow-hidden bg-gray-50 dark:bg-gray-950 transition-colors duration-300">
    
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

    {{-- Background Design --}}
    <div class="absolute inset-0 z-0">
        <div class="absolute left-0 top-0 w-1/2 h-full bg-[#0B5694] dark:bg-blue-900 opacity-90 dark:opacity-80 skew-x-[-12deg] -translate-x-32 transition-colors duration-300"></div>
        <div class="absolute right-0 top-0 w-full h-full bg-cover bg-center" 
             style="background-image: url('/images/school-bg.jpg'); filter: brightness(0.7);"></div>
    </div>

    @if(request()->has('timeout'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                setTimeout(() => {
                    if (window.showToast) {
                        window.showToast('Your session has timed out. Please sign in again.', 'info');
                    }
                }, 500);
            });
        </script>
    @endif

    {{-- LOGIN CARD --}}
    <div class="relative z-10 w-full max-w-[450px] bg-white dark:bg-gray-900 rounded-[40px] shadow-2xl p-12 mx-4 transition-all duration-300
                {{ $errors->any() ? 'animate-shake' : '' }} border border-transparent dark:border-gray-800">
        
        <header class="text-center mb-10">
            <h1 class="text-4xl font-extrabold text-[#0B5694] dark:text-blue-400 tracking-normal transition-colors duration-300">Login</h1>
        </header>

        {{-- DYNAMIC ERROR FEEDBACK --}}
        @if ($errors->any())
            <div x-data="uiNotification(5000)" 
                 x-show="show" 
                 x-transition:leave="transition ease-in duration-1000"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95"
                 class="mb-8 p-4 bg-red-50 dark:bg-red-950/30 rounded-2xl border border-red-100 dark:border-red-900/50 flex items-center gap-4">
                
                <div class="flex-shrink-0 w-10 h-10 bg-red-500 rounded-full flex items-center justify-center shadow-lg shadow-red-500/20">
                    <svg xmlns="http://www.w3.org/2000/svg" class="text-white" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </div>
                <div>
                    @php $isLocked = Str::contains($errors->first(), 'try again in'); @endphp
                    
                    <p class="text-[12px] font-bold text-red-600 dark:text-red-400 uppercase tracking-wider leading-none mb-1">
                        {{ $isLocked ? 'Account Locked' : 'Access Denied' }}
                    </p>
                    <p class="text-[12px] text-red-400 dark:text-red-500 font-bold uppercase tracking-tight">
                        Please try again later.
                    </p>
                </div>
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST" class="space-y-6">
            @csrf

            {{-- Email --}}
            <div class="text-left">
                <label for="email" class="text-[11px] font-bold text-gray-400 dark:text-gray-500 uppercase block mb-2 tracking-wider ml-1">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required 
                    class="w-full {{ $errors->has('email') ? 'bg-red-50/30 dark:bg-red-900/10 ring-1 ring-red-100 dark:ring-red-900/50' : 'bg-gray-50 dark:bg-gray-800' }} rounded-2xl px-6 py-4 text-sm focus:ring-2 focus:ring-[#0B5694] dark:focus:ring-blue-500 transition-all font-medium dark:text-gray-200">
            </div>

            {{-- Password --}}
            <div class="text-left">
                <label for="password" class="text-[11px] font-bold text-gray-400 dark:text-gray-500 uppercase block mb-2 tracking-wider ml-1">Password</label>
                <input type="password" name="password" id="password" required 
                    class="w-full {{ $errors->has('password') ? 'bg-red-50/30 dark:bg-red-900/10 ring-1 ring-red-100 dark:ring-red-900/50' : 'bg-gray-50 dark:bg-gray-800' }} rounded-2xl px-6 py-4 text-sm focus:ring-2 focus:ring-[#0B5694] dark:focus:ring-blue-500 transition-all font-medium dark:text-gray-200">
            </div>

            {{-- Sign In Button --}}
            <div class="pt-4">
                <button type="submit" class="w-full bg-[#0B5694] dark:bg-blue-600 text-white py-4 rounded-2xl font-bold shadow-lg shadow-blue-500/20 hover:bg-[#084577] dark:hover:bg-blue-700 transition-all active:scale-[0.98]">
                    Sign In
                </button>
            </div>

            <div class="text-center">
                <a href="#" class="text-xs font-bold text-gray-300 dark:text-gray-600 hover:text-[#0B5694] dark:hover:text-blue-400 transition-colors uppercase tracking-wider">Forgot password?</a>
            </div>
        </form>
    </div>
</div>

@endsection