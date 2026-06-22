<section id="security" class="py-20 sm:py-28 bg-gray-50 dark:bg-gray-950 transition-colors duration-300">
    <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <span class="inline-block px-4 py-1.5 mb-4 text-[10px] font-bold text-[#0B5694] dark:text-blue-400 tracking-[0.2em] uppercase bg-[#0B5694]/10 dark:bg-blue-500/10 rounded-full">Security & Compliance</span>
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-gray-900 dark:text-white tracking-tight mb-4">Built for Enterprise-Grade Security</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 font-medium max-w-2xl mx-auto">Your data is protected by multiple layers of security, from authentication to physical attendance verification.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            {{-- Pillar 1 --}}
            <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-[40px] p-8 text-center">
                <div class="w-16 h-16 bg-emerald-50 dark:bg-emerald-900/20 rounded-[20px] flex items-center justify-center mx-auto mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                </div>
                <h3 class="text-lg font-extrabold text-gray-900 dark:text-white tracking-tight mb-3">Multi-Factor Authentication</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium leading-relaxed">Mandatory TOTP-based MFA supporting Google and Microsoft Authenticators, with email OTP backup and single-use code security.</p>
            </div>

            {{-- Pillar 2 --}}
            <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-[40px] p-8 text-center">
                <div class="w-16 h-16 bg-emerald-50 dark:bg-emerald-900/20 rounded-[20px] flex items-center justify-center mx-auto mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                </div>
                <h3 class="text-lg font-extrabold text-gray-900 dark:text-white tracking-tight mb-3">Geofenced Attendance</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium leading-relaxed">QR-based attendance with GPS integrity checks ensures interns log time only from approved office locations within designated geofences.</p>
            </div>

            {{-- Pillar 3 --}}
            <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-[40px] p-8 text-center">
                <div class="w-16 h-16 bg-emerald-50 dark:bg-emerald-900/20 rounded-[20px] flex items-center justify-center mx-auto mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
                </div>
                <h3 class="text-lg font-extrabold text-gray-900 dark:text-white tracking-tight mb-3">Privacy & Data Protection</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium leading-relaxed">Fully compliant with RA 10173 (Data Privacy Act). Anomaly detection via Isolation Forest algorithm monitors for suspicious activity in real time.</p>
            </div>
        </div>

        <div class="text-center mt-10">
            <a href="{{ route('privacy-policy') }}" class="inline-flex items-center px-6 py-3 border-2 border-[#0B5694] dark:border-blue-600 text-[#0B5694] dark:text-blue-400 font-bold uppercase text-[10px] tracking-wider rounded-2xl transition-all hover:bg-[#0B5694] hover:text-white dark:hover:bg-blue-600 dark:hover:text-white active:scale-[0.98]">
                Read Our Privacy Policy
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="ml-2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>
        </div>
    </div>
</section>
