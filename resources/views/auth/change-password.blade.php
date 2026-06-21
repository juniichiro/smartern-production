@extends('layouts.app')

@section('title', 'Secure Your Account')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-950 flex flex-col justify-center py-12 sm:px-6 lg:px-8 font-sans transition-colors duration-300 relative overflow-hidden" 
     x-data="changePasswordManager">
    
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

    <div class="sm:mx-auto sm:w-full sm:max-w-md relative z-10">
        <div class="flex justify-center mb-6">
            <img class="h-12 w-auto dark:brightness-200" src="{{ asset('images/white-smartern.png') }}" alt="SmarTern Logo" :style="!$store.darkMode ? 'filter: invert(1)' : ''">
        </div>
        
        <h2 class="text-center text-3xl font-extrabold text-gray-800 dark:text-gray-100 tracking-tight">
            Secure Your Account
        </h2>
        <p class="mt-2 text-center text-sm text-gray-500 dark:text-gray-400 font-medium">
            Your security is our priority. Please set a strong password <br> to activate your SmarTern account.
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md relative z-10">
        <div class="bg-white dark:bg-gray-900 py-10 px-6 shadow-2xl border border-gray-100 dark:border-gray-800 rounded-[35px] sm:px-10 transition-colors duration-300">
            
            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 dark:bg-red-950/30 rounded-2xl border border-red-100 dark:border-red-900/50">
                    <ul class="list-disc list-inside text-xs text-red-600 dark:text-red-400 font-bold uppercase tracking-tight">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('profile.password.update') }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- New Password --}}
                <div>
                    <label for="password" class="block text-[11px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-2 ml-1">
                        New Password
                    </label>
                    <input id="password" name="password" type="password" required 
                        x-model="password"
                        @input="validate()"
                        class="appearance-none block w-full px-5 py-4 rounded-2xl bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-[#0B5694] dark:focus:ring-blue-500 transition-all sm:text-sm font-medium border-none">
                    
                    {{-- Strength Checklist --}}
                    <div class="mt-4 grid grid-cols-1 gap-2 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-2xl border border-gray-100 dark:border-gray-800">
                        <template x-for="(met, rule) in rules">
                            <div class="flex items-center gap-3">
                                <div :class="met ? 'bg-green-500 border-green-500' : 'bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700'" 
                                     class="w-4 h-4 rounded-full border flex items-center justify-center transition-colors duration-300">
                                    <svg x-show="met" class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <span :class="met ? 'text-gray-800 dark:text-gray-200' : 'text-gray-400 dark:text-gray-600'" class="text-[10px] font-bold uppercase tracking-widest transition-colors" 
                                      x-text="rule === 'length' ? 'Minimum 12 Characters' : 
                                             rule === 'lower' ? 'Lowercase Letter (a-z)' : 
                                             rule === 'upper' ? 'Uppercase Letter (A-Z)' : 
                                             rule === 'number' ? 'Numerical Digit (0-9)' : 'Special Character (!@#$) '">
                                </span>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Confirm Password --}}
                <div>
                    <label for="password_confirmation" class="block text-[11px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-2 ml-1">
                        Confirm New Password
                    </label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required 
                        x-model="password_confirmation"
                        class="appearance-none block w-full px-5 py-4 rounded-2xl bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-[#0B5694] dark:focus:ring-blue-500 transition-all sm:text-sm font-medium border-none">
                    
                    <div class="mt-2 flex items-center gap-2" x-show="password_confirmation.length > 0">
                        <span :class="password === password_confirmation ? 'text-green-500 dark:text-green-400' : 'text-red-400 dark:text-red-500'" 
                              class="text-[10px] font-bold uppercase tracking-widest"
                              x-text="password === password_confirmation ? 'Passwords Match' : 'Passwords do not match'">
                        </span>
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" 
                        :disabled="!allMet"
                        :class="allMet ? 'bg-[#0B5694] dark:bg-blue-600 hover:bg-[#084577] dark:hover:bg-blue-700 transform hover:scale-[1.02] shadow-lg shadow-blue-500/20' : 'bg-gray-300 dark:bg-gray-800 text-gray-500 dark:text-gray-600 cursor-not-allowed'"
                        class="w-full flex justify-center py-4 px-4 border border-transparent rounded-2xl text-sm font-bold text-white transition-all">
                        Update & Continue to Dashboard
                    </button>
                </div>
            </form>

            <div class="mt-8 pt-6 border-t border-gray-50 dark:border-gray-800 text-center">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-xs font-bold text-gray-400 dark:text-gray-600 hover:text-red-500 transition-colors uppercase tracking-widest">
                        Cancel and Logout
                    </button>
                </form>
            </div>
        </div>
        
        <p class="mt-8 text-center text-[10px] text-gray-400 dark:text-gray-600 font-bold uppercase tracking-widest">
            &copy; 2026 SmarTern Ecosystem. All rights reserved.
        </p>
    </div>
</div>
@endsection