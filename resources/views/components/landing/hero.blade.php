<section class="relative overflow-hidden bg-gray-50 dark:bg-gray-950 transition-colors duration-300">
    <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-16 items-center min-h-[90dvh] py-16 lg:py-0">
            {{-- Left: Text --}}
            <div class="order-2 lg:order-1">
                <span class="inline-block px-4 py-1.5 mb-5 text-[10px] font-bold text-[#0B5694] dark:text-blue-400 tracking-[0.18em] uppercase bg-[#0B5694]/8 dark:bg-blue-500/10 rounded-full">Schools Division Office — Quezon City</span>

                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-gray-900 dark:text-white tracking-tight leading-[1.05] mb-5">
                    Your Internship<br>Starts Here.
                </h1>

                <p class="text-lg font-extrabold text-[#0B5694] dark:text-blue-400 tracking-tight mb-3">SmarTern — Internship Management System</p>

                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium leading-relaxed max-w-lg mb-8">
                    The official internship portal of SDO Quezon City. Apply, get matched to an office, and track your progress — all in one place.
                </p>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('onboarding.create') }}" class="inline-flex items-center px-7 py-3.5 bg-[#0B5694] hover:bg-[#084577] dark:bg-blue-600 dark:hover:bg-blue-700 text-white font-bold uppercase text-[10px] tracking-wider rounded-2xl transition-all active:scale-[0.98] shadow-lg shadow-blue-500/20">
                        Start Your Application
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="ml-2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </a>
                    <a href="{{ route('login') }}" class="inline-flex items-center px-7 py-3.5 border-2 border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 font-bold uppercase text-[10px] tracking-wider rounded-2xl transition-all hover:border-[#0B5694] hover:text-[#0B5694] dark:hover:border-blue-500 dark:hover:text-blue-400 active:scale-[0.98]">
                        Log In
                    </a>
                </div>

                {{-- Stats row --}}
                <div class="flex flex-wrap gap-6 mt-10 pt-8 border-t border-gray-100 dark:border-gray-800">
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

            {{-- Right: Institutional Photo --}}
            <div class="order-1 lg:order-2 flex items-center justify-center">
                <div class="relative w-full max-w-md rounded-[40px] border border-gray-200 dark:border-gray-800 overflow-hidden shadow-lg">
                    <img src="{{ asset('images/sdoqc.webp') }}" alt="Schools Division Office of Quezon City" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-[#0B5694]/30 to-transparent pointer-events-none"></div>
                </div>
            </div>
        </div>
    </div>
</section>
