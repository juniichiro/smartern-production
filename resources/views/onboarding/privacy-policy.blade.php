@extends('layouts.app')

@section('title', 'Privacy and Data Protection Policy')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-950 font-sans flex items-center justify-center p-4 md:p-8 transition-colors duration-300 relative">
    
    {{-- Theme Toggle --}}
    <div class="fixed top-6 right-6 z-[100]" x-data="darkModeManager">
        <button @click="darkMode = !darkMode" 
                class="p-3 bg-white/90 dark:bg-white/10 backdrop-blur-md border border-gray-200 dark:border-white/10 rounded-2xl text-[#0B5694] dark:text-white hover:bg-white dark:hover:bg-white/20 transition-all active:scale-95 shadow-xl">
            <svg x-show="!darkMode" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
            </svg>
            <svg x-show="darkMode" x-cloak xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
            </svg>
        </button>
    </div>

    <div class="w-full max-w-6xl bg-white dark:bg-gray-900 rounded-[40px] shadow-xl dark:shadow-none border border-gray-100 dark:border-gray-800 p-6 md:p-12 transition-all duration-300 my-8">
        {{-- Header --}}
        <div class="text-center mb-12">
            <div class="inline-flex items-center justify-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded-[20px] mb-6 text-[#0B5694] dark:text-blue-400">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight uppercase">Privacy & Data Protection Policy</h1>
            <p class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em] mt-2">SmarTern | SDO QC | Operational Deployment Edition</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8 md:gap-12">
            {{-- Navigation Sidebar --}}
            <div class="lg:col-span-1 lg:sticky lg:top-8 self-start space-y-4 max-h-[80vh] overflow-y-auto pr-2 custom-scrollbar">
                <div class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-3">Policy Sections</div>
                <nav class="space-y-1">
                    <a href="#section-1" class="block px-3 py-2 text-xs font-bold text-[#0B5694] dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-950/30 rounded-xl transition-all">1. What is SmarTern?</a>
                    <a href="#section-2" class="block px-3 py-2 text-xs font-bold text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/30 rounded-xl transition-all">2. Who is Responsible?</a>
                    <a href="#section-3" class="block px-3 py-2 text-xs font-bold text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/30 rounded-xl transition-all">3. What We Collect</a>
                    <a href="#section-4" class="block px-3 py-2 text-xs font-bold text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/30 rounded-xl transition-all">4. What We Never Collect</a>
                    <a href="#section-5" class="block px-3 py-2 text-xs font-bold text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/30 rounded-xl transition-all">5. Why We Use Data</a>
                    <a href="#section-6" class="block px-3 py-2 text-xs font-bold text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/30 rounded-xl transition-all">6. Who Can See Data</a>
                    <a href="#section-7" class="block px-3 py-2 text-xs font-bold text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/30 rounded-xl transition-all">7. Sharing With Others</a>
                    <a href="#section-8" class="block px-3 py-2 text-xs font-bold text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/30 rounded-xl transition-all">8. Keeping Data Safe</a>
                    <a href="#section-9" class="block px-3 py-2 text-xs font-bold text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/30 rounded-xl transition-all">9. How Long We Keep Data</a>
                    <a href="#section-10" class="block px-3 py-2 text-xs font-bold text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/30 rounded-xl transition-all">10. Automated Tools</a>
                    <a href="#section-11" class="block px-3 py-2 text-xs font-bold text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/30 rounded-xl transition-all">11. Your Rights</a>
                    <a href="#section-12" class="block px-3 py-2 text-xs font-bold text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/30 rounded-xl transition-all">12. Give/Withdraw Consent</a>
                    <a href="#section-13" class="block px-3 py-2 text-xs font-bold text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/30 rounded-xl transition-all">13. Minors Under 18</a>
                    <a href="#section-14" class="block px-3 py-2 text-xs font-bold text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/30 rounded-xl transition-all">14. Data Breach Policy</a>
                    <a href="#section-15" class="block px-3 py-2 text-xs font-bold text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/30 rounded-xl transition-all">15. Policy Changes</a>
                </nav>
            </div>

            {{-- Policy Body --}}
            <div class="lg:col-span-3">
                @include('components.privacy-policy-content')
            </div>
        </div>


    </div>
</div>
@endsection

