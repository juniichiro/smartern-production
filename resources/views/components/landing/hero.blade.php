<section class="relative min-h-[95dvh] flex flex-col bg-gray-50 dark:bg-gray-950 transition-colors duration-300">
    {{-- Hero Image Banner --}}
    <div class="relative w-full h-[55dvh] overflow-hidden">
        <img src="{{ asset('images/sdoqc.webp') }}" alt="Schools Division Office of Quezon City" class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-b from-black/40 via-black/30 to-gray-50 dark:via-black/40 dark:to-gray-950"></div>

        {{-- Floating text over image --}}
        <div class="absolute inset-0 flex items-end pb-8">
            <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 w-full">
                <span class="inline-block px-4 py-1.5 mb-4 text-[10px] font-bold text-white/90 tracking-[0.18em] uppercase bg-white/10 backdrop-blur-sm border border-white/20 rounded-full">Schools Division Office — Quezon City</span>
                <h1 class="text-4xl sm:text-5xl lg:text-6xl xl:text-7xl font-extrabold text-white tracking-tight leading-[1.05] max-w-3xl">
                    Your Internship<br>Starts Here.
                </h1>
            </div>
        </div>
    </div>

    {{-- Content Panel --}}
    <div class="flex-1 -mt-6 relative z-10">
        <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-900 rounded-[40px] border border-gray-100 dark:border-gray-800 shadow-2xl p-8 sm:p-10">
                <div class="flex flex-col lg:flex-row lg:items-center gap-8">
                    <div class="flex-1">
                        <p class="text-lg font-extrabold text-[#0B5694] dark:text-blue-400 tracking-tight mb-2">SmarTern — Internship Management System</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium leading-relaxed max-w-lg">
                            The official internship portal of SDO Quezon City. Apply, get matched to an office, and track your progress — all in one place.
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-3 lg:justify-end shrink-0">
                        <a href="{{ route('onboarding.create') }}" class="inline-flex items-center px-7 py-3.5 bg-[#0B5694] hover:bg-[#084577] dark:bg-blue-600 dark:hover:bg-blue-700 text-white font-bold uppercase text-[10px] tracking-wider rounded-2xl transition-all active:scale-[0.98] shadow-lg shadow-blue-500/20">
                            Start Your Application
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="ml-2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                        </a>
                        <a href="{{ route('login') }}" class="inline-flex items-center px-7 py-3.5 border-2 border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 font-bold uppercase text-[10px] tracking-wider rounded-2xl transition-all hover:border-[#0B5694] hover:text-[#0B5694] dark:hover:border-blue-500 dark:hover:text-blue-400 active:scale-[0.98]">
                            Log In
                        </a>
                    </div>
                </div>

                {{-- Stats Row --}}
                <div class="flex flex-wrap gap-8 mt-8 pt-8 border-t border-gray-100 dark:border-gray-800">
                    <div>
                        <p class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">S.Y. 25–26</p>
                        <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mt-1">Currently Open</p>
                    </div>
                    <div>
                        <p class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">Multiple Offices</p>
                        <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mt-1">Placement Available</p>
                    </div>
                    <div>
                        <p class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">Online</p>
                        <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mt-1">Application Mode</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Contact Strip --}}
    <div class="bg-[#0B5694] dark:bg-blue-950 mt-6">
        <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex flex-wrap items-center justify-center gap-6 sm:gap-10 text-white/70 text-sm font-medium">
                <span class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    human.resource@depedqc.ph
                </span>
                <span class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    (02) 8538-6900 to (02) 8538-6919
                </span>
                <span class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    Nueva Ecija Street, Bago Bantay, Quezon City
                </span>
            </div>
        </div>
    </div>
</section>
