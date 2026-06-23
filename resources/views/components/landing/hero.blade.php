<section class="relative min-h-[92dvh] flex flex-col justify-between bg-gray-50 dark:bg-gray-950 overflow-hidden select-none transition-colors duration-300">
    {{-- Immersive Hero Background visual with brand-aligned gradient overlay and blur filter to mask low resolution --}}
    <div class="absolute inset-0 z-0 overflow-hidden">
        <img src="{{ asset('images/sdoqc.webp') }}" alt="Schools Division Office of Quezon City" class="w-full h-full object-cover blur-[6px] scale-105">
        <div class="absolute inset-0 bg-gradient-to-b from-[#07365c]/95 via-[#0b5694]/65 to-gray-50 dark:from-[#031c30]/95 dark:via-blue-950/80 dark:to-gray-950 transition-colors duration-300"></div>
    </div>

    {{-- Content Container --}}
    <div class="relative z-10 flex-1 flex flex-col justify-center items-center text-center px-4 sm:px-6 lg:px-8 max-w-[1400px] mx-auto w-full pt-20 pb-10">
        
        {{-- Micro-eyebrow Tag --}}
        <span class="inline-flex items-center gap-1.5 px-4.5 py-1.5 mb-6 text-[10px] font-bold text-white/90 tracking-[0.2em] uppercase bg-white/10 backdrop-blur-md border border-white/20 rounded-full animate-fade-in">
            Schools Division Office — Quezon City
        </span>

        {{-- Tightened, high-impact headline --}}
        <h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-extrabold text-white tracking-tight leading-[1.08] max-w-4xl font-sans">
            Your Internship<br class="hidden sm:inline"> Starts Here.
        </h1>

        {{-- Explanatory copy with maximum readability --}}
        <p class="mt-6 text-sm sm:text-base text-white/80 max-w-xl font-medium leading-relaxed">
            SmarTern is the official internship portal of SDO Quezon City. Apply, get matched to a school division office, and track your metrics — all in one streamlined dashboard.
        </p>

        {{-- Interactive CTA pills with nested trailing icon and custom cubic-beziers --}}
        <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4 w-full sm:w-auto">
            <a href="{{ route('onboarding.create') }}" class="group relative inline-flex items-center gap-4 pl-7 pr-2 py-2 bg-[#0B5694] hover:bg-[#084577] dark:bg-blue-600 dark:hover:bg-blue-700 text-white font-bold uppercase text-[10px] tracking-wider rounded-full transition-all duration-500 ease-[cubic-bezier(0.32,0.72,0,1)] active:scale-[0.98] shadow-lg shadow-blue-500/20 w-full sm:w-auto justify-between cursor-pointer">
                <span>Start Your Application</span>
                <span class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center transition-transform duration-300 group-hover:translate-x-1 shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-white"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </span>
            </a>
            
            <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-8 py-3.5 border border-white/30 hover:border-white hover:bg-white/10 text-white font-bold uppercase text-[10px] tracking-wider rounded-full transition-all duration-500 ease-[cubic-bezier(0.32,0.72,0,1)] active:scale-[0.98] w-full sm:w-auto cursor-pointer">
                Log In
            </a>
        </div>

        {{-- Floating Glassmorphic Console with Double-Bezel Architecture (Doppelrand) --}}
        <div class="bg-white/10 dark:bg-black/25 border border-white/15 dark:border-white/5 backdrop-blur-xl p-2 rounded-[2.5rem] max-w-4xl w-full mt-16 shadow-2xl transition-all duration-300">
            <div class="bg-white/95 dark:bg-gray-900/95 border border-gray-100/50 dark:border-gray-800/80 rounded-[calc(2.5rem-0.5rem)] p-6 md:p-8 flex flex-col md:flex-row items-center justify-between gap-6 md:gap-4 transition-colors duration-300">
                
                {{-- Stat Item 1 --}}
                <div class="flex items-center gap-4 text-left w-full md:w-auto px-4">
                    <div class="w-11 h-11 rounded-full bg-blue-50 dark:bg-blue-950/40 flex items-center justify-center text-[#0B5694] dark:text-blue-400 shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    </div>
                    <div>
                        <p class="text-xl font-extrabold text-gray-900 dark:text-white tracking-tight leading-none">S.Y. 25–26</p>
                        <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mt-1.5">Currently Open</p>
                    </div>
                </div>

                <div class="hidden md:block w-px h-10 bg-gray-200/60 dark:bg-gray-800"></div>

                {{-- Stat Item 2 --}}
                <div class="flex items-center gap-4 text-left w-full md:w-auto px-4">
                    <div class="w-11 h-11 rounded-full bg-blue-50 dark:bg-blue-950/40 flex items-center justify-center text-[#0B5694] dark:text-blue-400 shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                    </div>
                    <div>
                        <p class="text-xl font-extrabold text-gray-900 dark:text-white tracking-tight leading-none">Multiple Offices</p>
                        <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mt-1.5">Placement Available</p>
                    </div>
                </div>

                <div class="hidden md:block w-px h-10 bg-gray-200/60 dark:bg-gray-800"></div>

                {{-- Stat Item 3 --}}
                <div class="flex items-center gap-4 text-left w-full md:w-auto px-4">
                    <div class="w-11 h-11 rounded-full bg-blue-50 dark:bg-blue-950/40 flex items-center justify-center text-[#0B5694] dark:text-blue-400 shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                    </div>
                    <div>
                        <p class="text-xl font-extrabold text-gray-900 dark:text-white tracking-tight leading-none">Online</p>
                        <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mt-1.5">Application Mode</p>
                    </div>
                </div>

            </div>
        </div>

    </div>

    {{-- Contact strip nested at the bottom --}}
    <div class="relative z-10 w-full bg-[#0B5694]/85 dark:bg-blue-950/85 backdrop-blur-md border-t border-white/10 py-4 transition-colors duration-300">
        <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row items-center justify-between gap-4 text-white/85 text-xs font-semibold">
                <div class="flex flex-wrap items-center justify-center gap-6 lg:gap-8">
                    <a href="mailto:human.resource@depedqc.ph" class="flex items-center gap-2 hover:text-white transition-colors duration-200">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-80"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        human.resource@depedqc.ph
                    </a>
                    <a href="tel:0285386900" class="flex items-center gap-2 hover:text-white transition-colors duration-200">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-80"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        (02) 8538-6900
                    </a>
                    <span class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-80"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        Nueva Ecija Street, Bago Bantay, Quezon City
                    </span>
                </div>
                <div class="text-[9px] uppercase tracking-[0.15em] text-white/50 font-bold">
                    Schools Division Office QC
                </div>
            </div>
        </div>
    </div>
</section>
