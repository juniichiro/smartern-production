<section id="faq" class="py-20 sm:py-24 bg-white dark:bg-gray-900 transition-colors duration-300">
    <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
            <span class="inline-block px-4 py-1.5 mb-5 text-[10px] font-bold text-[#0B5694] dark:text-blue-400 tracking-[0.18em] uppercase bg-[#0B5694]/8 dark:bg-blue-500/10 rounded-full">Common Questions</span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 dark:text-white tracking-tight">Frequently Asked Questions</h2>
        </div>

        <div class="max-w-3xl mx-auto space-y-3" x-data="{ active: null }">
            {{-- FAQ 1 --}}
            <div class="bg-gray-50 dark:bg-gray-800/30 rounded-2xl border border-gray-100 dark:border-gray-800 overflow-hidden">
                <button @click="active = active === 1 ? null : 1" class="w-full flex items-center justify-between px-6 py-5 text-left transition-colors hover:bg-gray-100 dark:hover:bg-gray-800/50">
                    <span class="text-sm font-extrabold text-gray-900 dark:text-white pr-4">What is the internship application process?</span>
                    <svg x-show="active !== 1" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 text-gray-400"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    <svg x-show="active === 1" x-cloak xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 text-[#0B5694]"><line x1="5" y1="12" x2="19" y2="12"/></svg>
                </button>
                <div x-show="active === 1" x-collapse x-cloak>
                    <div class="px-6 pb-5 text-sm text-gray-500 dark:text-gray-400 font-medium leading-relaxed">
                        The process is simple: <strong class="text-gray-700 dark:text-gray-300">Fill out the online application form</strong> with your personal details, education background, and skills. Once submitted, the SDO QC Human Resource team will review your application and match you to an appropriate office. You'll be notified once your placement is confirmed.
                    </div>
                </div>
            </div>

            {{-- FAQ 2 --}}
            <div class="bg-gray-50 dark:bg-gray-800/30 rounded-2xl border border-gray-100 dark:border-gray-800 overflow-hidden">
                <button @click="active = active === 2 ? null : 2" class="w-full flex items-center justify-between px-6 py-5 text-left transition-colors hover:bg-gray-100 dark:hover:bg-gray-800/50">
                    <span class="text-sm font-extrabold text-gray-900 dark:text-white pr-4">Who can apply for an internship?</span>
                    <svg x-show="active !== 2" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 text-gray-400"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    <svg x-show="active === 2" x-cloak xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 text-[#0B5694]"><line x1="5" y1="12" x2="19" y2="12"/></svg>
                </button>
                <div x-show="active === 2" x-collapse x-cloak>
                    <div class="px-6 pb-5 text-sm text-gray-500 dark:text-gray-400 font-medium leading-relaxed">
                        The internship program is open to <strong class="text-gray-700 dark:text-gray-300">college students</strong> who are currently enrolled and require internship hours as part of their curriculum. Applicants must be endorsed by their respective schools and meet the requirements set by SDO Quezon City.
                    </div>
                </div>
            </div>

            {{-- FAQ 3 --}}
            <div class="bg-gray-50 dark:bg-gray-800/30 rounded-2xl border border-gray-100 dark:border-gray-800 overflow-hidden">
                <button @click="active = active === 3 ? null : 3" class="w-full flex items-center justify-between px-6 py-5 text-left transition-colors hover:bg-gray-100 dark:hover:bg-gray-800/50">
                    <span class="text-sm font-extrabold text-gray-900 dark:text-white pr-4">What documents are required?</span>
                    <svg x-show="active !== 3" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 text-gray-400"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    <svg x-show="active === 3" x-cloak xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 text-[#0B5694]"><line x1="5" y1="12" x2="19" y2="12"/></svg>
                </button>
                <div x-show="active === 3" x-collapse x-cloak>
                    <div class="px-6 pb-5 text-sm text-gray-500 dark:text-gray-400 font-medium leading-relaxed">
                        You'll need to upload your <strong class="text-gray-700 dark:text-gray-300">resume (PDF format)</strong> and provide your personal information, educational background, and relevant skills. The onboarding form will guide you through each step. Additional documents may be requested depending on your assigned office.
                    </div>
                </div>
            </div>

            {{-- FAQ 4 --}}
            <div class="bg-gray-50 dark:bg-gray-800/30 rounded-2xl border border-gray-100 dark:border-gray-800 overflow-hidden">
                <button @click="active = active === 4 ? null : 4" class="w-full flex items-center justify-between px-6 py-5 text-left transition-colors hover:bg-gray-100 dark:hover:bg-gray-800/50">
                    <span class="text-sm font-extrabold text-gray-900 dark:text-white pr-4">How will I be assigned to an office?</span>
                    <svg x-show="active !== 4" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 text-gray-400"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    <svg x-show="active === 4" x-cloak xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 text-[#0B5694]"><line x1="5" y1="12" x2="19" y2="12"/></svg>
                </button>
                <div x-show="active === 4" x-collapse x-cloak>
                    <div class="px-6 pb-5 text-sm text-gray-500 dark:text-gray-400 font-medium leading-relaxed">
                        SmarTern uses an <strong class="text-gray-700 dark:text-gray-300">intelligent matching system</strong> that considers your academic background, skills, and preferences alongside the needs of each SDO QC office. This ensures you're placed where you can contribute meaningfully while gaining relevant experience.
                    </div>
                </div>
            </div>

            {{-- FAQ 5 --}}
            <div class="bg-gray-50 dark:bg-gray-800/30 rounded-2xl border border-gray-100 dark:border-gray-800 overflow-hidden">
                <button @click="active = active === 5 ? null : 5" class="w-full flex items-center justify-between px-6 py-5 text-left transition-colors hover:bg-gray-100 dark:hover:bg-gray-800/50">
                    <span class="text-sm font-extrabold text-gray-900 dark:text-white pr-4">How is my internship tracked?</span>
                    <svg x-show="active !== 5" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 text-gray-400"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    <svg x-show="active === 5" x-cloak xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 text-[#0B5694]"><line x1="5" y1="12" x2="19" y2="12"/></svg>
                </button>
                <div x-show="active === 5" x-collapse x-cloak>
                    <div class="px-6 pb-5 text-sm text-gray-500 dark:text-gray-400 font-medium leading-relaxed">
                        Once deployed, you'll use SmarTern to <strong class="text-gray-700 dark:text-gray-300">log daily attendance via QR code, submit tasks</strong> assigned by your supervisor, write progress journals, and track your accumulated hours. At the end of your program, you will receive instructions to pick up your physical certificate of completion.
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
