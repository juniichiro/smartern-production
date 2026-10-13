<div 
    x-show="bottomSheetOpen" 
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-end justify-center bg-slate-950/60 backdrop-blur-xs sm:hidden"
    @keydown.escape.window="closeBottomSheet()"
    x-cloak
    role="dialog"
    aria-modal="true"
    aria-label="Selected date details sheet"
>
    {{-- Backdrop click --}}
    <div class="fixed inset-0" @click="closeBottomSheet()"></div>

    {{-- Sheet Container --}}
    <div 
        x-show="bottomSheetOpen"
        x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="translate-y-full"
        x-transition:enter-end="translate-y-0"
        x-transition:leave="transition ease-in duration-200 transform"
        x-transition:leave-start="translate-y-0"
        x-transition:leave-end="translate-y-full"
        class="relative w-full max-h-[85dvh] bg-white dark:bg-gray-900 rounded-t-3xl border-t border-slate-200/80 dark:border-gray-800 shadow-2xl flex flex-col z-10 overflow-hidden"
        @click.stop
    >
        {{-- Grabber handle & header --}}
        <div class="p-4 border-b border-slate-100 dark:border-gray-800 flex items-center justify-between shrink-0">
            <div class="w-8"></div>
            {{-- Grabber --}}
            <div class="w-12 h-1.5 rounded-full bg-slate-300 dark:bg-gray-700"></div>
            {{-- Close Button --}}
            <button 
                type="button" 
                @click="closeBottomSheet()"
                class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-slate-500 dark:text-slate-400 flex items-center justify-center transition-colors cursor-pointer"
                aria-label="Close sheet"
            >
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>

        {{-- Scrollable Content Body --}}
        <div class="p-5 overflow-y-auto flex-1 overscroll-contain">
            <x-schedule.day-panel :selectedDay="$selectedDay" />
        </div>
    </div>
</div>
