<div 
    x-show="absenceModalOpen" 
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs overflow-y-auto"
    @keydown.escape.window="absenceModalOpen = false"
    x-cloak
    role="dialog"
    aria-modal="true"
    aria-labelledby="absence-modal-title"
>
    <div class="fixed inset-0" @click="absenceModalOpen = false"></div>

    <div 
        class="relative w-full max-w-lg rounded-3xl bg-white dark:bg-gray-900 border border-slate-200/80 dark:border-gray-800 shadow-2xl p-6 sm:p-8 z-10 text-left animate-in zoom-in-95 duration-200"
        @click.stop
    >
        {{-- Header --}}
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-gray-800 mb-5">
            <div>
                <h3 id="absence-modal-title" class="text-lg font-bold text-slate-900 dark:text-white tracking-tight" x-text="absenceModalTitle">
                    Request Absence
                </h3>
                <p class="text-xs text-[#0B5694] dark:text-blue-400 font-semibold mt-0.5" x-text="absenceTargetDateFormatted">
                </p>
            </div>
            <button 
                type="button" 
                @click="absenceModalOpen = false"
                class="p-2 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-gray-800 transition-colors"
                aria-label="Close dialog"
            >
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>

        {{-- 1. Create or Edit Absence Form --}}
        <template x-if="absenceModalMode === 'request' || absenceModalMode === 'edit'">
            <form 
                :action="absenceModalMode === 'edit' ? `/intern/schedule/absence/${absenceActiveItem.id}` : '{{ route('intern.schedule.absence.store') }}'" 
                method="POST" 
                enctype="multipart/form-data" 
                class="space-y-4"
            >
                @csrf
                <template x-if="absenceModalMode === 'edit'">
                    <input type="hidden" name="_method" value="PUT">
                </template>
                <input type="hidden" name="date" :value="absenceTargetDate">

                <div>
                    <label for="absence-reason" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                        Reason for Absence <span class="text-rose-500">*</span>
                    </label>
                    <textarea 
                        id="absence-reason"
                        name="reason" 
                        rows="4" 
                        required
                        x-model="absenceReason"
                        class="w-full text-xs sm:text-sm rounded-2xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 text-slate-800 dark:text-slate-200 p-3.5 focus:ring-2 focus:ring-[#0B5694] dark:focus:ring-blue-400 focus:outline-hidden transition-all placeholder:text-slate-400"
                        placeholder="Please provide a clear and professional reason for your absence or early leave..."
                    ></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                        Supporting Document (Optional, PDF / JPG up to 5MB)
                    </label>
                    <input 
                        type="file" 
                        name="attachment" 
                        id="absence-file-input"
                        accept=".pdf,.jpg,.jpeg,.png"
                        class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-[#0B5694] dark:file:bg-blue-950/40 dark:file:text-blue-300 hover:file:bg-blue-100 dark:hover:file:bg-blue-900/40 cursor-pointer"
                    >
                </div>

                <div class="pt-3 flex items-center justify-end gap-2">
                    <button 
                        type="button" 
                        @click="absenceModalOpen = false"
                        class="py-2.5 px-4 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-gray-800 transition-colors"
                    >
                        Cancel
                    </button>
                    <button 
                        type="submit" 
                        class="py-2.5 px-5 rounded-xl text-xs font-semibold text-white bg-[#0B5694] hover:bg-[#084577] dark:bg-blue-600 dark:hover:bg-blue-700 transition-all shadow-xs"
                    >
                        <span x-text="absenceModalMode === 'edit' ? 'Update Request' : 'Submit Request'"></span>
                    </button>
                </div>
            </form>
        </template>

        {{-- 2. Withdraw Confirmation --}}
        <template x-if="absenceModalMode === 'withdraw'">
            <form :action="`/intern/schedule/absence/${absenceActiveItem ? absenceActiveItem.id : ''}`" method="POST" class="space-y-4">
                @csrf
                @method('DELETE')

                <div class="p-4 rounded-2xl bg-rose-50/70 dark:bg-rose-950/30 border border-rose-200/70 dark:border-rose-900/50 flex items-start gap-3">
                    <div class="w-8 h-8 rounded-xl bg-rose-100 dark:bg-rose-900/40 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-rose-950 dark:text-rose-100">
                            Withdraw Request?
                        </h4>
                        <p class="text-xs text-rose-800/90 dark:text-rose-300 mt-1 leading-relaxed">
                            Are you sure you want to withdraw this absence request? This action cannot be undone.
                        </p>
                    </div>
                </div>

                <div class="pt-3 flex items-center justify-end gap-2">
                    <button 
                        type="button" 
                        @click="absenceModalOpen = false"
                        class="py-2.5 px-4 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-gray-800 transition-colors"
                    >
                        Keep Request
                    </button>
                    <button 
                        type="submit" 
                        class="py-2.5 px-5 rounded-xl text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 transition-all shadow-xs"
                    >
                        Withdraw Permanently
                    </button>
                </div>
            </form>
        </template>
    </div>
</div>
