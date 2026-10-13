@props([
    'agendaDays' => [],
    'todayDateStr',
])

<div 
    x-show="currentView === 'agenda'" 
    class="rounded-3xl border border-slate-200/80 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 sm:p-8 shadow-xs text-left"
    role="region"
    aria-label="Agenda view for the month"
>
    @if(empty($agendaDays))
        <div class="py-16 px-6 text-center">
            <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-gray-800 text-slate-400 dark:text-slate-500 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
            </div>
            <h4 class="text-sm font-bold text-slate-700 dark:text-slate-300">
                No holidays or schedule changes this month.
            </h4>
            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1 max-w-sm mx-auto">
                All dates follow your standard weekly work routine.
            </p>
        </div>
    @else
        <div class="space-y-6 sm:space-y-8">
            @foreach($agendaDays as $day)
                @php
                    $isToday = ($day['date'] === $todayDateStr);
                @endphp
                <div 
                    id="agenda-day-{{ $day['date'] }}"
                    class="relative {{ $isToday ? 'p-4 sm:p-5 rounded-2xl bg-blue-50/40 dark:bg-blue-950/20 border border-blue-200/60 dark:border-blue-900/40' : '' }}"
                >
                    {{-- Sticky Date Heading --}}
                    <div class="sticky top-16 sm:top-20 z-10 py-1.5 px-3 rounded-xl bg-slate-100/90 dark:bg-gray-800/90 backdrop-blur-xs flex items-center justify-between gap-3 mb-3 border border-slate-200/60 dark:border-gray-700/60">
                        <div class="flex items-center gap-2">
                            @if($isToday)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#0B5694] text-white">
                                    Today
                                </span>
                            @endif
                            <h4 class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-200">
                                {{ $day['formatted_long'] }}
                            </h4>
                        </div>
                        <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">
                            {{ $day['day_type_label'] }}
                        </span>
                    </div>

                    {{-- Markers for this day --}}
                    <div class="space-y-2.5 pl-2 sm:pl-3 border-l-2 {{ $isToday ? 'border-[#0B5694] dark:border-blue-400' : 'border-slate-200 dark:border-gray-800' }}">
                        @foreach($day['markers'] as $marker)
                            @if($marker['type'] === 'holiday')
                                <div class="p-3.5 rounded-2xl bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-200/70 dark:border-emerald-900/50 flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 mt-0.5">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2 mb-0.5">
                                            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-800 dark:text-emerald-300">Holiday</span>
                                        </div>
                                        <h5 class="text-sm font-bold text-emerald-950 dark:text-emerald-100 truncate">{{ $marker['title'] }}</h5>
                                        <p class="text-xs text-emerald-700/90 dark:text-emerald-400 mt-0.5">{{ $marker['effect'] }}</p>
                                    </div>
                                </div>
                            @elseif($marker['type'] === 'override')
                                <div class="p-3.5 rounded-2xl bg-amber-50/70 dark:bg-amber-950/30 border border-amber-200/70 dark:border-amber-900/50 flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 mt-0.5">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2 mb-0.5">
                                            <span class="text-[10px] font-bold uppercase tracking-wider text-amber-800 dark:text-amber-300">Schedule Override</span>
                                        </div>
                                        <h5 class="text-sm font-bold text-amber-950 dark:text-amber-100 truncate">{{ $marker['title'] }}</h5>
                                        <p class="text-xs text-amber-700/90 dark:text-amber-400 mt-0.5">{{ $marker['effect'] }}</p>
                                    </div>
                                </div>
                            @elseif($marker['type'] === 'task')
                                <div class="p-3.5 rounded-2xl bg-white dark:bg-gray-850 border border-slate-200/80 dark:border-gray-800 shadow-xs flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-xl {{ $marker['is_overdue'] ? 'bg-rose-100 dark:bg-rose-900/40 text-rose-600 dark:text-rose-400' : 'bg-blue-100 dark:bg-blue-900/40 text-[#0B5694] dark:text-blue-400' }} flex items-center justify-center shrink-0 mt-0.5">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="m9 12 2 2 4-4"/></svg>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center justify-between gap-2 mb-0.5">
                                            <span class="text-[10px] font-bold uppercase tracking-wider text-[#0B5694] dark:text-blue-400">Task Deadline</span>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $marker['is_overdue'] ? 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300' : 'bg-slate-100 text-slate-700 dark:bg-gray-800 dark:text-slate-300' }}">
                                                {{ $marker['is_overdue'] ? 'Overdue' : $marker['status'] }}
                                            </span>
                                        </div>
                                        <h5 class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ $marker['title'] }}</h5>
                                        <div class="mt-2 flex items-center justify-between text-xs">
                                            <span class="text-slate-500">Due at {{ $marker['due_time'] }}</span>
                                            <a href="{{ $marker['route'] }}" class="font-semibold text-[#0B5694] dark:text-blue-400 hover:underline">Open submission &rarr;</a>
                                        </div>
                                    </div>
                                </div>
                            @elseif($marker['type'] === 'attendance')
                                <div class="p-3.5 rounded-2xl bg-teal-50/70 dark:bg-teal-950/30 border border-teal-200/70 dark:border-teal-900/50 flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-teal-100 dark:bg-teal-900/40 text-teal-600 dark:text-teal-400 flex items-center justify-center shrink-0 mt-0.5">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center justify-between gap-2 mb-0.5">
                                            <span class="text-[10px] font-bold uppercase tracking-wider text-teal-800 dark:text-teal-300">Attendance Log</span>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-teal-100 text-teal-800 dark:bg-teal-900 dark:text-teal-200">{{ $marker['status'] }}</span>
                                        </div>
                                        <p class="text-xs text-slate-700 dark:text-slate-300">
                                            Clock in: <span class="font-bold text-slate-900 dark:text-white">{{ $marker['clock_in'] }}</span> &bull; 
                                            Clock out: <span class="font-bold text-slate-900 dark:text-white">{{ $marker['clock_out'] }}</span>
                                        </p>
                                    </div>
                                </div>
                            @elseif($marker['type'] === 'absence_request')
                                <div class="p-3.5 rounded-2xl bg-white dark:bg-gray-850 border border-slate-200/80 dark:border-gray-800 shadow-xs flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 mt-0.5">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center justify-between gap-2 mb-0.5">
                                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400">
                                                {{ $marker['request_type'] === 'early_leave' ? 'Early Leave' : 'Absence' }}
                                            </span>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $marker['status'] === 'approved' ? 'bg-emerald-100 text-emerald-800' : ($marker['status'] === 'rejected' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') }}">
                                                {{ $marker['status'] }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-slate-700 dark:text-slate-300 font-medium">{{ $marker['reason'] }}</p>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
