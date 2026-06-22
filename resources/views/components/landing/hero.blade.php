<section class="relative min-h-[100dvh] flex items-center overflow-hidden">
    {{-- Background with gradient overlay --}}
    <div class="absolute inset-0 bg-[#0B5694] dark:bg-blue-950">
        <div class="absolute inset-0 bg-gradient-to-br from-[#0B5694]/90 via-[#0B5694]/70 to-[#0B5694]/50 dark:from-blue-950/95 dark:via-blue-950/80 dark:to-blue-950/60"></div>
    </div>

    <div class="relative z-10 w-full max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="max-w-3xl">
            <span class="inline-block px-4 py-2 mb-6 text-[10px] font-bold text-white/80 tracking-[0.2em] uppercase border border-white/20 rounded-full">Schools Division Office — Quezon City</span>

            <h1 class="text-4xl sm:text-5xl lg:text-6xl xl:text-7xl font-extrabold text-white tracking-tight leading-none mb-6">
                Internship Management,<br>
                <span class="text-white/80">Reimagined.</span>
            </h1>

            <p class="text-base sm:text-lg text-white/70 max-w-2xl leading-relaxed mb-10 font-medium">
                A secure, data-driven platform for managing student deployments, tracking accomplishments, and automating office placements across SDO Quezon City.
            </p>

            <div class="flex flex-wrap gap-4">
                <a href="{{ route('onboarding.create') }}" class="inline-flex items-center px-8 py-4 bg-white text-[#0B5694] dark:bg-white/90 dark:text-blue-900 font-bold uppercase text-[11px] tracking-wider rounded-2xl transition-all hover:bg-white/90 hover:scale-[1.02] active:scale-[0.98] shadow-2xl shadow-black/20">
                    Apply as Intern
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="ml-3"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
                <a href="{{ route('login') }}" class="inline-flex items-center px-8 py-4 text-white font-bold uppercase text-[11px] tracking-wider rounded-2xl transition-all border-2 border-white/30 hover:border-white/50 hover:bg-white/10 active:scale-[0.98]">
                    Log In to Portal
                </a>
            </div>
        </div>
    </div>

    {{-- Bottom fade --}}
    <div class="absolute bottom-0 left-0 right-0 h-32 bg-gradient-to-t from-gray-50 dark:from-gray-950 to-transparent"></div>
</section>

{{-- Contact Info Strip --}}
<div class="bg-[#0B5694] dark:bg-blue-950 border-t border-white/10">
    <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-5">
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
