@props([
    'monthTitle',
    'weeks' => [],
    'selectedDateStr',
])

<div 
    class="w-full lg:h-full flex flex-col rounded-3xl border border-slate-200/80 dark:border-gray-800 bg-white dark:bg-gray-900 overflow-hidden shadow-xs"
    role="region"
    aria-label="Month calendar view"
>
    <div 
        role="grid" 
        aria-label="{{ $monthTitle }} Calendar Grid"
        class="w-full flex-1 min-h-0 flex flex-col border-collapse"
        @keydown="handleGridKeydown($event)"
    >
        {{-- Weekday Header Row --}}
        <div role="row" class="grid grid-cols-7 border-b border-slate-200/80 dark:border-gray-800 bg-slate-50/80 dark:bg-gray-850/80 text-center shrink-0">
            @php
                $weekdays = [
                    ['short' => 'Sun', 'full' => 'Sunday'],
                    ['short' => 'Mon', 'full' => 'Monday'],
                    ['short' => 'Tue', 'full' => 'Tuesday'],
                    ['short' => 'Wed', 'full' => 'Wednesday'],
                    ['short' => 'Thu', 'full' => 'Thursday'],
                    ['short' => 'Fri', 'full' => 'Friday'],
                    ['short' => 'Sat', 'full' => 'Saturday'],
                ];
            @endphp
            @foreach($weekdays as $wd)
                <div 
                    role="columnheader" 
                    scope="col"
                    class="py-2 sm:py-2.5 text-[10px] sm:text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider"
                >
                    <abbr title="{{ $wd['full'] }}" class="no-underline">{{ $wd['short'] }}</abbr>
                </div>
            @endforeach
        </div>

        {{-- Week Rows Container (Week rows share remaining height equally) --}}
        <div class="flex-1 min-h-0 flex flex-col">
            @foreach($weeks as $weekIndex => $week)
                <div role="row" class="flex-1 min-h-0 grid grid-cols-7 border-b last:border-b-0 border-slate-200/60 dark:border-gray-800/80">
                    @foreach($week as $dayIndex => $day)
                        <x-schedule.day-cell 
                            :day="$day" 
                            :selectedDateStr="$selectedDateStr" 
                        />
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>

    {{-- Legend Footer Row (Inside calendar panel, separated by subtle divider) --}}
    <x-schedule.legend />
</div>
