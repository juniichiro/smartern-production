<nav x-data="{ 
    open: false, 
    darkMode: localStorage.getItem('darkMode') === 'true' || (!('darkMode' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches) 
}" x-init="$watch('darkMode', val => { localStorage.setItem('darkMode', val); if (val) document.documentElement.classList.add('dark'); else document.documentElement.classList.remove('dark'); window.dispatchEvent(new CustomEvent('theme-changed', { detail: val })); })" class="sticky top-0 z-50 bg-white/95 dark:bg-gray-950/95 backdrop-blur-md border-b border-gray-100 dark:border-gray-800 transition-colors duration-300">
    <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-[72px]">
            <a href="{{ url('/') }}" class="flex items-center gap-3 shrink-0">
                <img src="{{ asset('images/blue-smartern.png') }}" alt="SmarTern" class="h-8 w-auto">
                <span class="text-lg font-extrabold text-[#0B5694] dark:text-blue-400 tracking-tight">SmarTern</span>
            </a>

            <div class="hidden sm:flex items-center gap-2">
                <a href="{{ route('login') }}" class="text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider hover:text-[#0B5694] dark:hover:text-blue-400 transition-colors px-1.5">Log In</a>
                <a href="{{ route('onboarding.create') }}" class="inline-flex items-center px-5 py-2.5 bg-[#0B5694] hover:bg-[#084577] dark:bg-blue-600 dark:hover:bg-blue-700 text-white font-bold uppercase text-[10px] tracking-wider rounded-2xl transition-all active:scale-[0.98]">
                    Apply as Intern
                </a>
                <button @click="darkMode = !darkMode" class="p-2.5 rounded-xl text-gray-400 dark:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-[#0B5694] dark:hover:text-blue-400 transition-all">
                    <svg x-show="!darkMode" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                    <svg x-show="darkMode" x-cloak xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                </button>
            </div>

            <button @click="open = !open" class="sm:hidden p-2 rounded-xl text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                <svg x-show="open" x-cloak xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="sm:hidden pb-6 border-t border-gray-100 dark:border-gray-800 pt-4">
            <div class="flex flex-col gap-3">
                <a @click="open = false" href="{{ route('login') }}" class="text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider py-2">Log In</a>
                <a @click="open = false" href="{{ route('onboarding.create') }}" class="inline-flex items-center justify-center px-5 py-3 bg-[#0B5694] hover:bg-[#084577] dark:bg-blue-600 dark:hover:bg-blue-700 text-white font-bold uppercase text-[10px] tracking-wider rounded-2xl transition-all active:scale-[0.98] text-center">Apply as Intern</a>
                <hr class="border-gray-100 dark:border-gray-800 my-1">
                <button @click="darkMode = !darkMode" class="flex items-center gap-2 text-[11px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider py-2">
                    <svg x-show="!darkMode" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                    <svg x-show="darkMode" x-cloak xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                    Toggle Dark Mode
                </button>
            </div>
        </div>
    </div>
</nav>
