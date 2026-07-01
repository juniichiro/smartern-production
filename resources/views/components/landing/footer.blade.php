<footer class="bg-gray-50 dark:bg-gray-950 border-t border-gray-100 dark:border-gray-800">
    <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-14">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-10">
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <img src="{{ asset('images/blue-smartern.png') }}" alt="SmarTern" class="h-7 w-auto dark:hidden">
                    <img src="{{ asset('images/white-smartern.png') }}" alt="SmarTern" class="h-7 w-auto hidden dark:block">
                    <span class="text-base font-extrabold text-[#0B5694] dark:text-blue-400 tracking-tight">SmarTern</span>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium leading-relaxed max-w-xs">The official internship management system of the Schools Division Office of Quezon City.</p>
            </div>

            <div>
                <h4 class="text-[11px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-4">Quick Links</h4>
                <ul class="space-y-2.5">
                    <li><a href="#" class="text-sm text-gray-600 dark:text-gray-400 hover:text-[#0B5694] dark:hover:text-blue-400 transition-colors font-medium">Home</a></li>
                    <li><a href="#how-it-works" class="text-sm text-gray-600 dark:text-gray-400 hover:text-[#0B5694] dark:hover:text-blue-400 transition-colors font-medium">How It Works</a></li>
                    <li><a href="#faq" class="text-sm text-gray-600 dark:text-gray-400 hover:text-[#0B5694] dark:hover:text-blue-400 transition-colors font-medium">FAQs</a></li>
                    <li><a href="{{ route('privacy-policy') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-[#0B5694] dark:hover:text-blue-400 transition-colors font-medium">Privacy Policy</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-[11px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-4">Contact</h4>
                <ul class="space-y-2.5 text-sm text-gray-600 dark:text-gray-400 font-medium">
                    <li>human.resource@depedqc.ph</li>
                    <li>(02) 8538-6900 to (02) 8538-6919</li>
                    <li>Nueva Ecija Street, Bago Bantay,<br>Lungsod Quezon, Kalakhang Maynila</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="border-t border-gray-100 dark:border-gray-800">
        <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-5 flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-[10px] text-gray-400 dark:text-gray-600 font-medium">&copy; {{ date('Y') }} Schools Division Office of Quezon City. All rights reserved.</p>
            <div class="flex items-center gap-4">
                <a href="https://www.facebook.com/depedqc/" target="_blank" rel="noopener noreferrer" class="text-gray-400 dark:text-gray-600 hover:text-[#0B5694] dark:hover:text-blue-400 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                </a>
                <a href="https://depedqc.com" target="_blank" rel="noopener noreferrer" class="text-gray-400 dark:text-gray-600 hover:text-[#0B5694] dark:hover:text-blue-400 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                </a>
            </div>
        </div>
    </div>
</footer>
