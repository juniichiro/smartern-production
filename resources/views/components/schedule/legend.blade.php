<div 
    class="px-3.5 sm:px-4 py-2 sm:py-2.5 bg-slate-50/70 dark:bg-gray-850/60 border-t border-slate-200/80 dark:border-gray-800 flex flex-wrap items-center justify-between gap-2.5 sm:gap-3 text-[11px] sm:text-xs text-slate-600 dark:text-slate-400 shrink-0"
    role="region"
    aria-label="Calendar legend"
>
    <div class="flex items-center gap-1.5 font-semibold text-slate-800 dark:text-slate-200 text-[11px] sm:text-xs shrink-0">
        <svg class="w-3.5 h-3.5 text-[#0B5694] dark:text-blue-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="16" x2="12" y2="12"/>
            <line x1="12" y1="8" x2="12.01" y2="8"/>
        </svg>
        <span>Legend:</span>
    </div>

    <div class="flex items-center gap-2.5 sm:gap-4 flex-wrap">
        {{-- Holiday --}}
        <div class="flex items-center gap-1.5">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shrink-0"></span>
            <span>Holiday</span>
        </div>

        {{-- Schedule change --}}
        <div class="flex items-center gap-1.5">
            <span class="w-2.5 h-2.5 rounded-full bg-amber-500 shrink-0"></span>
            <span>Schedule change</span>
        </div>

        {{-- Task due --}}
        <div class="flex items-center gap-1.5">
            <span class="w-2.5 h-2.5 rounded-full bg-blue-500 shrink-0"></span>
            <span>Task due</span>
        </div>

        {{-- Attendance --}}
        <div class="flex items-center gap-1.5">
            <span class="w-2.5 h-2.5 rounded-full bg-teal-500 shrink-0"></span>
            <span>Attendance logged</span>
        </div>

        {{-- Today --}}
        <div class="flex items-center gap-1.5">
            <span class="w-3.5 h-3.5 rounded-full bg-[#0B5694] dark:bg-blue-600 text-white text-[9px] font-bold flex items-center justify-center shrink-0">1</span>
            <span>Today</span>
        </div>

        {{-- Selected --}}
        <div class="flex items-center gap-1.5">
            <span class="w-3.5 h-3.5 rounded-sm ring-2 ring-[#0B5694] dark:ring-blue-400 bg-blue-50/50 dark:bg-blue-950/30 shrink-0"></span>
            <span>Selected</span>
        </div>
    </div>
</div>
