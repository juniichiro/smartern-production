<nav class="sticky top-0 z-50 bg-white/95 dark:bg-gray-950/95 backdrop-blur-md border-b border-gray-100 dark:border-gray-800 transition-colors duration-300">
    <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-[72px]">
            <a href="{{ url('/') }}" class="flex items-center gap-3">
                <img src="{{ asset('images/blue-smartern.png') }}" alt="SmarTern" class="h-9 w-auto dark:hidden">
                <img src="{{ asset('images/white-smartern.png') }}" alt="SmarTern" class="h-9 w-auto hidden dark:block">
                <span class="text-lg font-extrabold text-[#0B5694] dark:text-blue-400 tracking-tight">SmarTern</span>
            </a>

            {{-- Desktop Nav --}}
            <div class="hidden lg:flex items-center gap-8">
                <a href="#features" class="text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider hover:text-[#0B5694] dark:hover:text-blue-400 transition-colors">Features</a>
                <a href="#security" class="text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider hover:text-[#0B5694] dark:hover:text-blue-400 transition-colors">Security</a>
                <a href="#roles" class="text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider hover:text-[#0B5694] dark:hover:text-blue-400 transition-colors">Roles</a>
            </div>

            <div class="hidden lg:flex items-center gap-4">
                <a href="{{ route('login') }}" class="text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider hover:text-[#0B5694] dark:hover:text-blue-400 transition-colors">Log In</a>
                <a href="{{ route('onboarding.create') }}" class="inline-flex items-center px-6 py-3 bg-[#0B5694] hover:bg-[#084577] dark:bg-blue-600 dark:hover:bg-blue-700 text-white font-bold uppercase text-[10px] tracking-wider rounded-2xl transition-all active:scale-[0.98]">
                    Apply as Intern
                </a>
            </div>

            {{-- Mobile Hamburger --}}
            <button x-data="{ open: false }" @click="open = !open" class="lg:hidden p-2 rounded-xl text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                <svg x-show="open" x-cloak xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        {{-- Mobile Menu --}}
        <div x-data="{ open: false }" x-show="open" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="lg:hidden pb-6 border-t border-gray-100 dark:border-gray-800 pt-4">
            <div class="flex flex-col gap-3">
                <a @click="open = false" href="#features" class="text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider py-2">Features</a>
                <a @click="open = false" href="#security" class="text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider py-2">Security</a>
                <a @click="open = false" href="#roles" class="text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider py-2">Roles</a>
                <hr class="border-gray-100 dark:border-gray-800 my-2">
                <a @click="open = false" href="{{ route('login') }}" class="text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider py-2">Log In</a>
                <a @click="open = false" href="{{ route('onboarding.create') }}" class="inline-flex items-center justify-center px-6 py-3 bg-[#0B5694] hover:bg-[#084577] dark:bg-blue-600 dark:hover:bg-blue-700 text-white font-bold uppercase text-[10px] tracking-wider rounded-2xl transition-all active:scale-[0.98] text-center">Apply as Intern</a>
            </div>
        </div>
    </div>
</nav>
