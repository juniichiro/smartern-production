@extends('layouts.app')

@section('title', 'Schedule - SmarTern')

@section('content')
<x-page-layout 
    portalType="Intern" 
    userName="{{ $userName }}" 
    userRole="{{ $userRole }}"
>
    <div 
        x-data="internScheduleController({
            currentMonth: '{{ $currentMonthStr }}',
            currentView: '{{ $currentView }}',
            selectedDate: '{{ $selectedDateStr }}',
            todayDate: '{{ $todayDateStr }}',
            daysByDate: @js($daysByDate),
            urlViewParam: '{{ request()->query('view') ?? '' }}'
        })" 
        class="flex flex-col flex-1 min-h-0 w-full"
    >
        {{-- Screen Reader Live Announcement --}}
        <div aria-live="polite" class="sr-only" x-text="liveAnnouncement"></div>

        {{-- ========================================================================= --}}
        {{-- 1. PAGE HEADER                                                            --}}
        {{-- ========================================================================= --}}
        <header class="mb-5 sm:mb-6 shrink-0 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-left">
            <div>
                {{-- Breadcrumb --}}
                <div class="flex items-center gap-1.5 text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">
                    <svg class="w-3.5 h-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    <span>Schedule</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-tight">
                    Schedule
                </h1>
                <p class="text-xs sm:text-sm font-medium text-slate-500 dark:text-gray-400 mt-0.5">
                    Holidays, schedule changes, tasks, and attendance
                </p>
            </div>
        </header>

        {{-- Session Flash Alerts --}}
        @if(session('success'))
            <div class="mb-5 p-4 rounded-2xl bg-emerald-50 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/50 flex items-center gap-3 text-xs font-semibold">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error') || $errors->any())
            <div class="mb-5 p-4 rounded-2xl bg-rose-50 text-rose-800 dark:bg-rose-950/40 dark:text-rose-300 border border-rose-200 dark:border-rose-800/50 flex items-start gap-3 text-xs font-semibold">
                <svg class="w-4 h-4 text-rose-600 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <div>
                    <div>{{ session('error') ?? 'Please check the following errors:' }}</div>
                    @if($errors->any())
                        <ul class="list-disc list-inside mt-1 font-normal">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        @endif

        {{-- ========================================================================= --}}
        {{-- 2. TOOLBAR                                                                --}}
        {{-- ========================================================================= --}}
        <x-schedule.toolbar 
            :monthTitle="$monthTitle"
            :currentMonthStr="$currentMonthStr"
            :prevMonthStr="$prevMonthStr"
            :nextMonthStr="$nextMonthStr"
            :isTodaySelected="$isTodaySelected"
            :isCurrentMonthSelected="$isCurrentMonthSelected"
            :currentView="$currentView"
            :pickerYears="$pickerYears"
            :pickerMonths="$pickerMonths"
        />

        {{-- ========================================================================= --}}
        {{-- 3. UNIFIED RESPONSIVE GRID (Calendar + Side Column)                       --}}
        {{-- ========================================================================= --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6 items-start w-full">
            
            {{-- CALENDAR COLUMN (8 COLS DESKTOP / 12 COLS TABLET & MOBILE) --}}
            <div class="lg:col-span-7 xl:col-span-8 w-full min-w-0">
                {{-- Month View Grid --}}
                <div x-show="currentView === 'month'">
                    <x-schedule.calendar-grid 
                        :monthTitle="$monthTitle"
                        :weeks="$weeks"
                        :selectedDateStr="$selectedDateStr"
                    />

                    {{-- Compact Legend (under calendar, hidden in Agenda) --}}
                    <x-schedule.legend />
                </div>

                {{-- Agenda View --}}
                <x-schedule.agenda-view 
                    :agendaDays="$agendaDays"
                    :todayDateStr="$todayDateStr"
                />

                {{-- On Mobile/Tablet (<1024px), Upcoming List sits below the Calendar --}}
                <div class="lg:hidden mt-6">
                    <x-schedule.upcoming-list :upcomingItems="$upcomingItems" />
                </div>
            </div>

            {{-- SIDE COLUMN (4 COLS DESKTOP / HIDDEN ON MOBILE <640px AS BOTTOM SHEET) --}}
            <div class="hidden lg:flex lg:col-span-5 xl:col-span-4 flex-col gap-6 w-full min-w-0 sticky top-6">
                {{-- Selected Day Details Panel --}}
                <x-schedule.day-panel :selectedDay="$selectedDay" />

                {{-- Upcoming 5 Items List --}}
                <x-schedule.upcoming-list :upcomingItems="$upcomingItems" />
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- 4. MOBILE BOTTOM SHEET (<1024px on tap)                                   --}}
        {{-- ========================================================================= --}}
        <x-schedule.bottom-sheet :selectedDay="$selectedDay" />

        {{-- ========================================================================= --}}
        {{-- 5. ABSENCE REQUEST & EDIT MODAL                                           --}}
        {{-- ========================================================================= --}}
        <x-schedule.absence-modal />
    </div>

    {{-- Alpine Calendar Controller Script --}}
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('internScheduleController', (config) => ({
                currentMonth: config.currentMonth,
                currentView: config.currentView || 'month',
                selectedDate: config.selectedDate,
                todayDate: config.todayDate,
                daysByDate: config.daysByDate || {},
                currentSelectedDay: config.daysByDate[config.selectedDate] || {},
                liveAnnouncement: '',
                bottomSheetOpen: false,

                // Absence Modal State
                absenceModalOpen: false,
                absenceModalMode: 'request', // 'request', 'edit', 'withdraw'
                absenceModalTitle: 'Request Absence',
                absenceTargetDate: '',
                absenceTargetDateFormatted: '',
                absenceReason: '',
                absenceActiveItem: null,

                init() {
                    // Mobile-first default: on mobile under 640px, default to Agenda view if not explicitly in URL
                    if (window.innerWidth < 640 && !config.urlViewParam) {
                        this.currentView = 'agenda';
                    }

                    // Auto-scroll to today in Agenda view if current month is viewed
                    if (this.currentView === 'agenda' && this.currentMonth === this.todayDate.substring(0, 7)) {
                        this.$nextTick(() => {
                            const todayEl = document.getElementById(`agenda-day-${this.todayDate}`);
                            if (todayEl) {
                                todayEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                            }
                        });
                    }

                    // Listen for browser popstate (back/forward)
                    window.addEventListener('popstate', (e) => {
                        const params = new URLSearchParams(window.location.search);
                        const m = params.get('month');
                        const v = params.get('view');
                        const d = params.get('date');

                        if (m && m !== this.currentMonth) {
                            window.location.reload();
                        } else {
                            if (v && ['month', 'agenda'].includes(v)) {
                                this.currentView = v;
                            }
                            if (d && this.daysByDate[d]) {
                                this.selectDay(d, false);
                            }
                        }
                    });
                },

                selectDay(dateStr, updateHistory = true) {
                    if (!this.daysByDate[dateStr]) return;

                    this.selectedDate = dateStr;
                    this.currentSelectedDay = this.daysByDate[dateStr];

                    // Announce to Screen Readers
                    this.liveAnnouncement = `Selected ${this.currentSelectedDay.formatted_long}. ${this.currentSelectedDay.day_type_label}.`;

                    // Update URL params without reloading
                    if (updateHistory) {
                        const newUrl = `?month=${this.currentMonth}&view=${this.currentView}&date=${dateStr}`;
                        window.history.pushState(null, '', newUrl);
                    }

                    // On screens under 1024px, show bottom sheet
                    if (window.innerWidth < 1024) {
                        this.bottomSheetOpen = true;
                    }
                },

                closeBottomSheet() {
                    this.bottomSheetOpen = false;
                    // Return focus to tapped cell
                    this.$nextTick(() => {
                        const cell = document.getElementById(`cell-${this.selectedDate}`);
                        if (cell) cell.focus();
                    });
                },

                goToMonth(monthStr, targetDate = null) {
                    let url = `?month=${encodeURIComponent(monthStr)}&view=${encodeURIComponent(this.currentView)}`;
                    if (targetDate) {
                        url += `&date=${encodeURIComponent(targetDate)}`;
                    }
                    window.location.href = url;
                },

                goToToday() {
                    const todayMonth = this.todayDate.substring(0, 7);
                    if (this.currentMonth !== todayMonth) {
                        this.goToMonth(todayMonth, this.todayDate);
                    } else {
                        this.selectDay(this.todayDate);
                        const cell = document.getElementById(`cell-${this.todayDate}`);
                        if (cell) cell.focus();
                    }
                },

                jumpToDate(monthStr, dateStr) {
                    if (monthStr !== this.currentMonth) {
                        this.goToMonth(monthStr, dateStr);
                    } else {
                        this.selectDay(dateStr);
                        const cell = document.getElementById(`cell-${dateStr}`);
                        if (cell) cell.focus();
                    }
                },

                jumpToMonth(event) {
                    const form = event.target;
                    const m = form.picker_month.value;
                    const y = form.picker_year.value;
                    this.goToMonth(`${y}-${m}`);
                },

                setView(newView) {
                    this.currentView = newView;
                    const newUrl = `?month=${this.currentMonth}&view=${newView}&date=${this.selectedDate}`;
                    window.history.pushState(null, '', newUrl);
                },

                handleGridKeydown(e) {
                    const keys = ['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End', 'PageUp', 'PageDown', 'Enter', ' '];
                    if (!keys.includes(e.key)) return;

                    const allDates = Object.keys(this.daysByDate);
                    const currentIndex = allDates.indexOf(this.selectedDate);
                    if (currentIndex === -1) return;

                    let newIndex = currentIndex;

                    switch (e.key) {
                        case 'ArrowLeft':
                            newIndex = Math.max(0, currentIndex - 1);
                            break;
                        case 'ArrowRight':
                            newIndex = Math.min(allDates.length - 1, currentIndex + 1);
                            break;
                        case 'ArrowUp':
                            newIndex = Math.max(0, currentIndex - 7);
                            break;
                        case 'ArrowDown':
                            newIndex = Math.min(allDates.length - 1, currentIndex + 7);
                            break;
                        case 'Home':
                            // Start of current week (Sunday)
                            newIndex = currentIndex - (currentIndex % 7);
                            break;
                        case 'End':
                            // End of current week (Saturday)
                            newIndex = currentIndex + (6 - (currentIndex % 7));
                            break;
                        case 'PageUp':
                            e.preventDefault();
                            this.goToMonth('{{ $prevMonthStr }}');
                            return;
                        case 'PageDown':
                            e.preventDefault();
                            this.goToMonth('{{ $nextMonthStr }}');
                            return;
                        case 'Enter':
                        case ' ':
                            e.preventDefault();
                            this.selectDay(this.selectedDate);
                            return;
                    }

                    if (newIndex !== currentIndex && allDates[newIndex]) {
                        e.preventDefault();
                        const newDate = allDates[newIndex];
                        this.selectDay(newDate);
                        this.$nextTick(() => {
                            const newCell = document.getElementById(`cell-${newDate}`);
                            if (newCell) newCell.focus();
                        });
                    }
                },

                openRequestAbsence(dateStr) {
                    this.absenceTargetDate = dateStr;
                    const day = this.daysByDate[dateStr];
                    this.absenceTargetDateFormatted = day ? day.formatted_long : dateStr;
                    this.absenceModalMode = 'request';
                    this.absenceModalTitle = (dateStr === this.todayDate) ? 'Request Early Leave' : 'Request Absence';
                    this.absenceReason = '';
                    this.absenceActiveItem = null;
                    this.absenceModalOpen = true;
                },

                openEditAbsence(item) {
                    this.absenceTargetDate = this.selectedDate;
                    this.absenceTargetDateFormatted = this.currentSelectedDay.formatted_long;
                    this.absenceModalMode = 'edit';
                    this.absenceModalTitle = (item.request_type === 'early_leave') ? 'Edit Early Leave Request' : 'Edit Absence Request';
                    this.absenceReason = item.reason || '';
                    this.absenceActiveItem = item;
                    this.absenceModalOpen = true;
                },

                openWithdrawAbsence(item) {
                    this.absenceTargetDate = this.selectedDate;
                    this.absenceTargetDateFormatted = this.currentSelectedDay.formatted_long;
                    this.absenceModalMode = 'withdraw';
                    this.absenceModalTitle = 'Withdraw Request';
                    this.absenceActiveItem = item;
                    this.absenceModalOpen = true;
                }
            }));
        });
    </script>
</x-page-layout>
@endsection
