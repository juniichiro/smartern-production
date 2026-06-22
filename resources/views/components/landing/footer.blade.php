<footer class="bg-[#0B5694] dark:bg-blue-950 border-t border-white/10">
    <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10">
            {{-- Brand --}}
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <img src="{{ asset('images/white-smartern.png') }}" alt="SmarTern" class="h-8 w-auto">
                    <span class="text-lg font-extrabold text-white tracking-tight">SmarTern</span>
                </div>
                <p class="text-sm text-white/60 font-medium leading-relaxed">Internship Management System by the Schools Division Office of Quezon City.</p>
            </div>

            {{-- Pages --}}
            <div>
                <h4 class="text-[11px] font-bold text-white/80 uppercase tracking-wider mb-5">Pages</h4>
                <ul class="space-y-3">
                    <li><a href="#" class="text-sm text-white/60 hover:text-white transition-colors font-medium">Home</a></li>
                    <li><a href="#features" class="text-sm text-white/60 hover:text-white transition-colors font-medium">Features</a></li>
                    <li><a href="#security" class="text-sm text-white/60 hover:text-white transition-colors font-medium">Security</a></li>
                    <li><a href="#roles" class="text-sm text-white/60 hover:text-white transition-colors font-medium">Roles</a></li>
                    <li><a href="{{ route('privacy-policy') }}" class="text-sm text-white/60 hover:text-white transition-colors font-medium">Privacy Policy</a></li>
                </ul>
            </div>

            {{-- Address --}}
            <div>
                <h4 class="text-[11px] font-bold text-white/80 uppercase tracking-wider mb-5">Address</h4>
                <p class="text-sm text-white/60 font-medium leading-relaxed">
                    Nueva Ecija Street, Bago Bantay,<br>
                    Lungsod Quezon, Kalakhang Maynila
                </p>
            </div>

            {{-- Contact --}}
            <div>
                <h4 class="text-[11px] font-bold text-white/80 uppercase tracking-wider mb-5">Contact Us</h4>
                <ul class="space-y-3">
                    <li class="flex items-center gap-3 text-sm text-white/60 font-medium">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        (02) 8538-6900 to (02) 8538-6919<br>Local 104
                    </li>
                    <li class="flex items-center gap-3 text-sm text-white/60 font-medium">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        human.resource@depedqc.ph
                    </li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Bottom bar --}}
    <div class="border-t border-white/10">
        <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-5 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-xs text-white/50 font-medium">© {{ date('Y') }} Schools Division Office of Quezon City. All rights reserved.</p>
            <div class="flex items-center gap-4">
                <a href="https://www.facebook.com/DepEdTayoSDOQuezonCity" target="_blank" rel="noopener noreferrer" class="text-white/50 hover:text-white transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                </a>
                <a href="https://depedqc.com" target="_blank" rel="noopener noreferrer" class="text-white/50 hover:text-white transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                </a>
            </div>
        </div>
    </div>
</footer>
