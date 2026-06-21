@auth
    @sectionMissing('is_skeleton_loading')
        @if(!auth()->user()->privacy_acknowledged_at && 
        !request()->routeIs('mfa.*') && 
        !request()->routeIs('profile.change-password') && 
        !request()->routeIs('profile.password.update') && 
        !request()->routeIs('attendance.*') && 
        !request()->routeIs('logout'))
        
        {{-- Privacy Policy Modal --}}
        <div x-data="{ 
                showPrivacyModal: true,
                isSubmitting: false,
                errorMsg: '',
                hasScrolledToBottom: false,
                handleScroll() {
                    const el = this.$refs.scrollContainer;
                    if (!el) return;
                    if (el.scrollHeight - el.scrollTop <= el.clientHeight + 10) {
                        this.hasScrolledToBottom = true;
                    }
                },
                checkIfScrollNotNeeded() {
                    this.$nextTick(() => {
                        const el = this.$refs.scrollContainer;
                        if (el && el.scrollHeight <= el.clientHeight) {
                            this.hasScrolledToBottom = true;
                        }
                    });
                },
                acknowledge() {
                    if (!this.hasScrolledToBottom) return;
                    this.isSubmitting = true;
                    this.errorMsg = '';
                    fetch('{{ route('privacy.acknowledge') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Request failed');
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            this.showPrivacyModal = false;
                            document.body.classList.remove('overflow-hidden');
                            window.showToast('Privacy terms acknowledged.', 'success');
                        } else {
                            this.errorMsg = 'Something went wrong. Please try again.';
                        }
                    })
                    .catch(error => {
                        console.error(error);
                        this.errorMsg = 'Failed to connect. Please check your internet connection and try again.';
                    })
                    .finally(() => {
                        this.isSubmitting = false;
                    });
                }
             }"
             x-show="showPrivacyModal"
             x-init="
                if (showPrivacyModal) {
                    document.body.classList.add('overflow-hidden');
                    this.checkIfScrollNotNeeded();
                }
             "
             class="fixed inset-0 z-[300] flex items-center justify-center p-4 bg-gray-900/60 dark:bg-black/80 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             style="display: none;"
             @keydown.escape.window.prevent
        >
            <div class="w-full max-w-2xl bg-white dark:bg-gray-900 rounded-[32px] border border-gray-100 dark:border-gray-800 shadow-2xl overflow-hidden flex flex-col max-h-[85vh] transition-all duration-300"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 @click.away.prevent
            >
                {{-- Modal Header --}}
                <div class="px-8 py-6 border-b border-gray-100 dark:border-gray-800 shrink-0">
                    <div class="flex items-center gap-4">
                        <div class="p-2.5 bg-blue-50 dark:bg-blue-900/20 text-[#0B5694] dark:text-blue-400 rounded-xl">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white uppercase tracking-normal">Privacy Policy & Consent</h2>
                            <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mt-0.5">Data Privacy Act of 2012 (RA 10173) Compliance</p>
                        </div>
                    </div>
                </div>

                {{-- Modal Body (Scrollable) --}}
                <div x-ref="scrollContainer"
                     @scroll="handleScroll"
                     class="px-8 py-6 overflow-y-auto custom-scrollbar flex-1 text-sm text-gray-600 dark:text-gray-300 space-y-6 leading-relaxed">
                    @include('components.privacy-policy-content')
                </div>

                {{-- Modal Footer --}}
                <div class="px-8 py-6 border-t border-gray-100 dark:border-gray-800 shrink-0 bg-gray-50/50 dark:bg-gray-900/50 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <span class="text-xs font-semibold text-center sm:text-left transition-colors duration-250 select-none">
                        <span x-show="!hasScrolledToBottom" class="text-amber-600 dark:text-amber-400 flex items-center gap-2 justify-center sm:justify-start">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="animate-bounce"><line x1="12" y1="5" x2="12" y2="19"/><polyline points="19 12 12 19 5 12"/></svg>
                            Please scroll to the bottom to unlock the agreement.
                        </span>
                        <span x-show="hasScrolledToBottom" class="text-gray-500 dark:text-gray-400" x-cloak>
                            By agreeing, you consent to our terms and policy.
                        </span>
                    </span>
                    <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto justify-end">
                        <template x-if="errorMsg">
                            <span class="text-xs text-rose-500 font-bold" x-text="errorMsg"></span>
                        </template>
                        <button @click="acknowledge()" 
                                :disabled="isSubmitting || !hasScrolledToBottom"
                                class="w-full sm:w-auto px-6 py-3 bg-[#0B5694] hover:bg-[#073D6B] dark:bg-blue-600 dark:hover:bg-blue-500 text-white font-bold uppercase text-[10px] tracking-wider rounded-xl transition-all shadow-md active:scale-95 disabled:opacity-50 disabled:scale-100 flex items-center justify-center gap-2">
                            <template x-if="isSubmitting">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </template>
                            I Acknowledge & Agree
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif
    @endif
@endauth
