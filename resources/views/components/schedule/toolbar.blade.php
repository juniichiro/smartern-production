@props([
    'monthTitle',
    'currentMonthStr',
    'prevMonthStr',
    'nextMonthStr',
    'isTodaySelected' => false,
    'isCurrentMonthSelected' => false,
    'currentView' => 'month',
    'pickerYears' => [],
    'pickerMonths' => [],
])

<div class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6 items-center mb-3.5 sm:mb-4">
    {{-- Left Row (or Row 1 on mobile): Month Title + Today + Picker Toggle (aligns with calendar panel) --}}
    <div class="lg:col-span-7 xl:col-span-8 flex items-center justify-between sm:justify-start gap-2 sm:gap-3 flex-wrap">
        <div class="flex items-center gap-2">
            <h2 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                {{ $monthTitle }}
            </h2>

            {{-- Compact Month/Year Picker Popover --}}
            <div x-data="{ pickerOpen: false }" class="relative inline-block text-left" @click.outside="pickerOpen = false" @keydown.escape.window="pickerOpen = false">
                <button 
                    type="button" 
                    @click="pickerOpen = !pickerOpen"
                    class="p-1.5 rounded-lg text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-gray-800 transition-colors focus:outline-hidden focus:ring-2 focus:ring-[#0B5694] dark:focus:ring-blue-400 min-h-[2.25rem] min-w-[2.25rem] flex items-center justify-center cursor-pointer"
                    aria-label="Choose month and year"
                    :aria-expanded="pickerOpen"
                >
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m6 9 6 6 6-6"/>
                    </svg>
                </button>

                <div 
                    x-show="pickerOpen" 
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="transform opacity-0 scale-95"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="transform opacity-100 scale-100"
                    x-transition:leave-end="transform opacity-0 scale-95"
                    class="absolute left-0 mt-2 w-64 rounded-2xl bg-white dark:bg-gray-900 border border-slate-200/80 dark:border-gray-800 shadow-lg p-3 z-50 text-left"
                    x-cloak
                >
                    <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-2 px-1">
                        Jump to month
                    </div>
                    <form @submit.prevent="jumpToMonth($event)" class="space-y-3">
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label for="picker-month" class="sr-only">Month</label>
                                <select 
                                    id="picker-month" 
                                    name="picker_month"
                                    class="w-full text-xs font-medium rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 text-slate-800 dark:text-slate-200 py-1.5 px-2 focus:ring-2 focus:ring-[#0B5694] dark:focus:ring-blue-400"
                                >
                                    @foreach($pickerMonths as $m)
                                        <option value="{{ $m['num'] }}" {{ substr($currentMonthStr, 5, 2) === $m['num'] ? 'selected' : '' }}>
                                            {{ $m['short'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="picker-year" class="sr-only">Year</label>
                                <select 
                                    id="picker-year" 
                                    name="picker_year"
                                    class="w-full text-xs font-medium rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 text-slate-800 dark:text-slate-200 py-1.5 px-2 focus:ring-2 focus:ring-[#0B5694] dark:focus:ring-blue-400"
                                >
                                    @foreach($pickerYears as $y)
                                        <option value="{{ $y }}" {{ (int)substr($currentMonthStr, 0, 4) === $y ? 'selected' : '' }}>
                                            {{ $y }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <button 
                            type="submit" 
                            class="w-full py-2 px-3 rounded-xl bg-[#0B5694] hover:bg-[#084577] dark:bg-blue-600 dark:hover:bg-blue-700 text-white font-semibold text-xs transition-colors shadow-xs cursor-pointer text-center"
                        >
                            Go to date
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Today Button --}}
        <button 
            type="button" 
            @click="goToToday()"
            class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-all border min-h-[2.25rem] flex items-center justify-center {{ $isTodaySelected ? 'bg-slate-100 dark:bg-gray-800 text-slate-500 dark:text-slate-400 border-slate-200 dark:border-gray-700 cursor-default' : 'bg-white dark:bg-gray-900 text-[#0B5694] dark:text-blue-400 border-slate-200/80 dark:border-gray-800 hover:bg-slate-50 dark:hover:bg-gray-800/80 cursor-pointer shadow-xs' }}"
            {{ $isTodaySelected ? 'disabled' : '' }}
            aria-label="{{ $isTodaySelected ? 'Already viewing today' : 'Go to today' }}"
            title="{{ $isTodaySelected ? 'Already viewing today' : 'Go to today' }}"
        >
            Today
        </button>
    </div>

    {{-- Right Row (or Row 2 on mobile): Arrows + Segmented Control (aligns with side column) --}}
    <div class="lg:col-span-5 xl:col-span-4 flex items-center justify-between sm:justify-end gap-2 sm:gap-3">
        {{-- Previous / Next Month Arrows --}}
        <div class="flex items-center gap-1 bg-white dark:bg-gray-900 border border-slate-200/80 dark:border-gray-800 rounded-xl p-0.5 shadow-xs">
            <button 
                type="button" 
                @click="goToMonth('{{ $prevMonthStr }}')"
                class="p-2 rounded-lg text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-gray-800 transition-colors focus:outline-hidden focus:ring-2 focus:ring-[#0B5694] dark:focus:ring-blue-400 min-h-[2.25rem] min-w-[2.25rem] flex items-center justify-center cursor-pointer"
                aria-label="Previous month"
            >
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m15 18-6-6 6-6"/>
                </svg>
            </button>
            <button 
                type="button" 
                @click="goToMonth('{{ $nextMonthStr }}')"
                class="p-2 rounded-lg text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-gray-800 transition-colors focus:outline-hidden focus:ring-2 focus:ring-[#0B5694] dark:focus:ring-blue-400 min-h-[2.25rem] min-w-[2.25rem] flex items-center justify-center cursor-pointer"
                aria-label="Next month"
            >
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m9 18 6-6-6-6"/>
                </svg>
            </button>
        </div>

        {{-- View Switcher: Segmented Control --}}
        <div class="p-1 bg-slate-100 dark:bg-gray-850 rounded-xl flex items-center gap-1" role="radiogroup" aria-label="Calendar view switcher">
            <button 
                type="button" 
                @click="setView('month')"
                :class="currentView === 'month' ? 'bg-white dark:bg-gray-900 text-[#0B5694] dark:text-blue-400 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                class="py-1.5 px-3 rounded-lg text-xs font-semibold text-center transition-all cursor-pointer min-h-[2rem] flex items-center justify-center gap-1.5"
                role="radio"
                :aria-checked="currentView === 'month'"
            >
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <rect width="18" height="18" x="3" y="3" rx="2"/>
                    <path d="M3 9h18"/>
                    <path d="M9 21V9"/>
                </svg>
                <span>Month</span>
            </button>
            <button 
                type="button" 
                @click="setView('agenda')"
                :class="currentView === 'agenda' ? 'bg-white dark:bg-gray-900 text-[#0B5694] dark:text-blue-400 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                class="py-1.5 px-3 rounded-lg text-xs font-semibold text-center transition-all cursor-pointer min-h-[2rem] flex items-center justify-center gap-1.5"
                role="radio"
                :aria-checked="currentView === 'agenda'"
            >
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M8 6h13"/>
                    <path d="M8 12h13"/>
                    <path d="M8 18h13"/>
                    <path d="M3 6h.01"/>
                    <path d="M3 12h.01"/>
                    <path d="M3 18h.01"/>
                </svg>
                <span>Agenda</span>
            </button>
        </div>
    </div>
</div>
