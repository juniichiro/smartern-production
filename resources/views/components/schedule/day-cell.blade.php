@props([
    'day',
    'selectedDateStr',
])

@php
    $dateStr = $day['date'];
    $isToday = $day['is_today'];
    $isCurrentMonth = $day['is_current_month'];
    $isPast = $day['is_past'];
    $isWeekend = $day['is_weekend'];
    $markers = $day['markers'] ?? [];
    $markersCount = count($markers);
    $firstTwoMarkers = array_slice($markers, 0, 2);
    $extraMarkersCount = max(0, $markersCount - 2);
@endphp

<div 
    role="gridcell"
    id="cell-{{ $dateStr }}"
    data-date="{{ $dateStr }}"
    :tabindex="selectedDate === '{{ $dateStr }}' ? '0' : '-1'"
    aria-label="{{ $day['accessible_name'] }}"
    @if($isToday) aria-current="date" @endif
    :aria-selected="selectedDate === '{{ $dateStr }}' ? 'true' : 'false'"
    @click="selectDay('{{ $dateStr }}')"
    @keydown.enter.prevent="selectDay('{{ $dateStr }}')"
    @keydown.space.prevent="selectDay('{{ $dateStr }}')"
    :class="{
        'ring-2 ring-[#0B5694] dark:ring-blue-400 ring-inset bg-blue-50/40 dark:bg-blue-950/20 z-10': selectedDate === '{{ $dateStr }}',
        'opacity-40 text-slate-400 dark:text-slate-600 bg-slate-50/30 dark:bg-gray-950/20': !{{ $isCurrentMonth ? 'true' : 'false' }},
        'bg-slate-100/70 dark:bg-gray-800/50': {{ $isCurrentMonth && $isWeekend ? 'true' : 'false' }} && selectedDate !== '{{ $dateStr }}',
        'bg-white dark:bg-gray-900': {{ $isCurrentMonth && !$isWeekend ? 'true' : 'false' }} && selectedDate !== '{{ $dateStr }}',
        'hover:bg-slate-50/80 dark:hover:bg-gray-800/60': {{ $isCurrentMonth ? 'true' : 'false' }} && selectedDate !== '{{ $dateStr }}',
    }"
    class="relative h-full min-h-[4.5rem] sm:min-h-[5rem] lg:min-h-0 p-1 sm:p-1.5 lg:p-2 flex flex-col justify-between transition-all cursor-pointer select-none group border-r last:border-r-0 border-slate-200/60 dark:border-gray-800/80 focus:outline-hidden focus:ring-2 focus:ring-[#0B5694] dark:focus:ring-blue-400 focus:z-20 overflow-hidden"
>
    {{-- Header: Day Number (Top Left convention) --}}
    <div class="flex items-center justify-between shrink-0">
        @if($isToday)
            <span class="w-5.5 h-5.5 sm:w-6 sm:h-6 rounded-full bg-[#0B5694] dark:bg-blue-600 text-white font-bold text-[11px] sm:text-xs flex items-center justify-center shadow-xs">
                {{ $day['day'] }}
            </span>
        @else
            <span class="text-xs sm:text-sm font-semibold {{ $isCurrentMonth ? ($isWeekend ? 'text-slate-600 dark:text-slate-300' : 'text-slate-800 dark:text-slate-200') : 'text-slate-400 dark:text-slate-600' }} group-hover:text-[#0B5694] dark:group-hover:text-blue-400 transition-colors pl-0.5">
                {{ $day['day'] }}
            </span>
        @endif

        {{-- Compact dot indicators on very small screens --}}
        @if($markersCount > 0)
            <div class="flex sm:hidden items-center gap-0.5">
                @foreach(array_slice($markers, 0, 3) as $m)
                    <span class="w-1.5 h-1.5 rounded-full {{ $m['type'] === 'holiday' ? 'bg-emerald-500' : ($m['type'] === 'override' ? 'bg-amber-500' : ($m['type'] === 'task' ? 'bg-blue-500' : ($m['type'] === 'attendance' ? 'bg-teal-500' : 'bg-orange-500'))) }}"></span>
                @endforeach
                @if($markersCount > 3)
                    <span class="text-[9px] font-bold text-slate-400">+</span>
                @endif
            </div>
        @endif
    </div>

    {{-- Marker Chips (Desktop/Tablet: sm+) --}}
    <div class="hidden sm:flex flex-col gap-1 mt-1 flex-1 min-h-0 justify-end overflow-hidden">
        @foreach($firstTwoMarkers as $marker)
            <x-schedule.marker-chip :marker="$marker" />
        @endforeach

        @if($extraMarkersCount > 0)
            <button 
                type="button" 
                @click.stop="selectDay('{{ $dateStr }}')"
                class="w-full text-center py-0.5 px-1 rounded-md text-[10px] font-bold text-slate-500 dark:text-slate-400 bg-slate-100 hover:bg-slate-200 dark:bg-gray-800 dark:hover:bg-gray-750 transition-colors border border-slate-200/50 dark:border-gray-700/50 cursor-pointer truncate shrink-0"
                title="+{{ $extraMarkersCount }} more events"
            >
                +{{ $extraMarkersCount }} more
            </button>
        @endif
    </div>
</div>
