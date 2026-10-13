@props([
    'upcomingItems' => [],
])

<div 
    class="rounded-3xl border border-slate-200/80 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 sm:p-6 shadow-xs text-left"
    role="region"
    aria-label="Upcoming schedule items"
>
    <div class="flex items-center justify-between gap-3 mb-4">
        <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
            <svg class="w-4 h-4 text-[#0B5694] dark:text-blue-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            <span>Upcoming</span>
        </h3>
        <span class="text-xs text-slate-400 font-medium">Next 5 items</span>
    </div>

    @if(empty($upcomingItems))
        <div class="py-8 px-4 rounded-2xl bg-slate-50/60 dark:bg-gray-850/30 border border-slate-200/50 dark:border-gray-800 text-center">
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">
                Nothing coming up.
            </p>
        </div>
    @else
        <div class="space-y-2.5">
            @foreach($upcomingItems as $item)
                <button 
                    type="button" 
                    @click="jumpToDate('{{ $item['target_month'] }}', '{{ $item['target_date'] }}')"
                    class="w-full p-3 rounded-2xl border border-slate-200/70 dark:border-gray-800/80 bg-slate-50/50 dark:bg-gray-850/50 hover:bg-white dark:hover:bg-gray-800 hover:border-blue-300 dark:hover:border-blue-500/50 hover:shadow-xs transition-all text-left flex items-center justify-between gap-3 cursor-pointer group focus:outline-hidden focus:ring-2 focus:ring-[#0B5694] dark:focus:ring-blue-400"
                    title="{{ $item['date_formatted'] }}: {{ $item['title'] }}"
                >
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-[11px] font-bold text-[#0B5694] dark:text-blue-400" title="{{ $item['date_formatted'] }}">
                                {{ $item['relative_date'] }}
                            </span>
                            <span class="text-[10px] text-slate-400 dark:text-slate-500">
                                &bull; {{ $item['date_formatted'] }}
                            </span>
                        </div>
                        <h4 class="text-xs sm:text-sm font-semibold text-slate-800 dark:text-slate-200 truncate group-hover:text-[#0B5694] dark:group-hover:text-blue-400 transition-colors">
                            {{ $item['title'] }}
                        </h4>
                    </div>

                    {{-- Type Pill with Icon + Text --}}
                    <span 
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider shrink-0 border {{ $item['type'] === 'holiday' ? 'bg-emerald-50 text-emerald-800 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800' : ($item['type'] === 'task' ? 'bg-blue-50 text-blue-800 border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800' : ($item['type'] === 'absence' ? 'bg-orange-50 text-orange-800 border-orange-200 dark:bg-orange-950/40 dark:text-orange-300 dark:border-orange-800' : 'bg-amber-50 text-amber-800 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800')) }}"
                    >
                        @if($item['type'] === 'holiday')
                            <svg class="w-3 h-3 text-emerald-600 dark:text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg>
                        @elseif($item['type'] === 'task')
                            <svg class="w-3 h-3 text-blue-600 dark:text-blue-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="m9 12 2 2 4-4"/></svg>
                        @else
                            <svg class="w-3 h-3 text-amber-600 dark:text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        @endif
                        <span>{{ $item['type_label'] }}</span>
                    </span>
                </button>
            @endforeach
        </div>
    @endif
</div>
