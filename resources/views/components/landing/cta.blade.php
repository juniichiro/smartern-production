<section class="py-24 bg-[#0B5694] dark:bg-blue-950 transition-colors duration-300">
    <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight mb-4">Ready to Start Your Internship?</h2>
        <p class="text-base text-white/70 max-w-xl mx-auto leading-relaxed mb-10 font-medium">Create your application, get matched to an office, and begin your journey with SDO Quezon City.</p>
        <a href="{{ route('onboarding.create') }}" class="inline-flex items-center px-8 py-4 bg-white text-[#0B5694] dark:bg-white/90 dark:text-blue-900 font-bold uppercase text-[11px] tracking-wider rounded-2xl transition-all hover:bg-white/90 hover:scale-[1.02] active:scale-[0.98] shadow-2xl shadow-black/20">
            Apply Now
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="ml-3"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </a>
        <p class="text-xs text-white/50 font-medium mt-6">Already have an account? <a href="{{ route('login') }}" class="text-white/80 hover:text-white underline underline-offset-2">Log In</a></p>
    </div>
</section>
