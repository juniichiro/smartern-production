@extends('layouts.app')

@section('title', 'Intern Dashboard - SmarTern')

@section('content')
<x-page-layout 
    portalType="Intern" 
    userName="{{ $intern->first_name ?? Auth::user()->email }}" 
    userRole="{{ $intern->office->name ?? 'Intern' }}"
>
    <div 
        x-data="internDashboardManager({{ Auth::id() }})" 
        class="flex flex-col flex-1 min-h-0 w-full"
    >
        {{-- ========================================================================= --}}
        {{-- 1. PAGE HEADER                                                            --}}
        {{-- ========================================================================= --}}
        <header class="mb-6 shrink-0 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-left">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-tight">
                    Welcome back, <span class="text-[#0B5694] dark:text-blue-400">{{ $intern->first_name ?? 'Intern' }}</span>
                </h1>
                <p class="text-xs sm:text-sm font-medium text-slate-500 dark:text-gray-400 mt-0.5">
                    {{ $todayDateFormatted }}
                </p>
            </div>
        </header>

        {{-- ========================================================================= --}}
        {{-- 2. EXTENSION ALERT BANNER (If Approaching End Date or Incomplete)          --}}
        {{-- ========================================================================= --}}
        @php
            $incomplete = ($remaining > 0);
            $endDateObj = $intern && $intern->end_date ? \Carbon\Carbon::parse($intern->end_date)->startOfDay() : null;
            $daysUntilEnd = $endDateObj ? (int) ceil(now()->startOfDay()->diffInDays($endDateObj, false)) : 999;
            $showExtensionNotice = $incomplete && ($daysUntilEnd <= 7);
        @endphp

        @if($showExtensionNotice)
            <div class="mb-6 p-4 sm:p-5 rounded-2xl bg-amber-50/70 dark:bg-amber-950/30 border border-amber-200/70 dark:border-amber-900/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-left">
                <div class="flex items-start gap-3.5">
                    <div class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    </div>
                    <div>
                        <h4 class="text-xs sm:text-sm font-bold text-amber-900 dark:text-amber-200">
                            Internship End Date Approaching
                        </h4>
                        <p class="text-xs font-medium text-amber-800/80 dark:text-amber-300/80 mt-0.5 leading-relaxed">
                            @if($daysUntilEnd < 0)
                                Your expected completion date passed {{ abs($daysUntilEnd) }} days ago and you still have {{ (int) $remaining }} hours remaining.
                            @elseif($daysUntilEnd === 0)
                                Today is your expected completion date, with {{ (int) $remaining }} hours remaining.
                            @else
                                You have {{ $daysUntilEnd }} days remaining until your target end date, with {{ (int) $remaining }} hours left to render.
                            @endif
                        </p>
                    </div>
                </div>

                <div class="shrink-0 self-start sm:self-auto">
                    @if($hasApprovedExtension)
                        <span class="inline-flex items-center px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-amber-100 dark:bg-amber-900/50 text-amber-800 dark:text-amber-200">
                            Extension Approved
                        </span>
                    @elseif($pendingExtension)
                        <span class="inline-flex items-center px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-amber-100 dark:bg-amber-900/50 text-amber-800 dark:text-amber-200">
                            Extension Pending Review
                        </span>
                    @else
                        <button 
                            type="button" 
                            @click="extensionModalOpen = true" 
                            class="px-4 py-2 rounded-xl text-xs font-semibold text-white bg-amber-600 hover:bg-amber-700 transition-colors shadow-xs cursor-pointer min-h-[38px]"
                        >
                            Request Extension
                        </button>
                    @endif
                </div>
            </div>
        @endif

        {{-- ========================================================================= --}}
        {{-- 3. UNIFIED 12-COLUMN DASHBOARD GRID                                       --}}
        {{-- ========================================================================= --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6 items-start w-full">

            {{-- ===================================================================== --}}
            {{-- ROW 1A: ATTENDANCE CARD (4 COLS DESKTOP, FIRST ON MOBILE)            --}}
            {{-- ===================================================================== --}}
            <div 
                class="lg:col-span-4 w-full"
                role="region" 
                aria-label="Today's attendance status"
            >
                <div 
                    x-data="attendanceTimer({
                        state: '{{ $attendanceState['state'] }}',
                        clockInEpochMs: {{ $attendanceState['clock_in_epoch_ms'] }},
                        serverNowEpochMs: {{ $attendanceState['server_now_epoch_ms'] }},
                        initialElapsedFormatted: '{{ $attendanceState['elapsed_formatted'] ?? '0h 00m' }}'
                    })"
                    x-init="init()"
                    class="rounded-3xl border border-slate-200/80 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 sm:p-6 shadow-xs flex flex-col justify-between min-h-[220px] text-left transition-all relative overflow-hidden"
                >
                    {{-- Card Header --}}
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-gray-800/80">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full {{ $attendanceState['state'] === 'timed_in' ? 'bg-[#0B5694] dark:bg-blue-400 animate-pulse' : ($attendanceState['state'] === 'day_complete' ? 'bg-emerald-500' : 'bg-slate-400') }}"></span>
                            <h2 class="text-sm font-bold text-slate-900 dark:text-white tracking-tight">
                                Today
                            </h2>
                        </div>

                        {{-- Status Pill --}}
                        <span 
                            class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold border
                            {{ $attendanceState['status_pill']['color'] === 'emerald' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border-emerald-200/60 dark:border-emerald-800/40' : '' }}
                            {{ $attendanceState['status_pill']['color'] === 'blue' ? 'bg-blue-50 text-[#0B5694] dark:bg-blue-950/40 dark:text-blue-400 border-blue-200/60 dark:border-blue-800/40' : '' }}
                            {{ $attendanceState['status_pill']['color'] === 'slate' ? 'bg-slate-100 text-slate-600 dark:bg-gray-800 dark:text-gray-300 border-slate-200/80 dark:border-gray-700' : '' }}
                            "
                        >
                            {{ $attendanceState['status_pill']['label'] }}
                        </span>
                    </div>

                    {{-- Card Body --}}
                    <div class="my-4 space-y-3">
                        {{-- STATE 1: TIMED IN --}}
                        @if($attendanceState['state'] === 'timed_in')
                            <div>
                                <h3 class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-white leading-tight">
                                    {{ $attendanceState['headline'] }}
                                </h3>
                                <p class="text-xs font-medium text-slate-500 dark:text-gray-400 mt-0.5">
                                    Active shift in progress
                                </p>
                            </div>

                            {{-- Elapsed Timer Box --}}
                            <div class="p-3 rounded-2xl bg-blue-50/60 dark:bg-blue-950/30 border border-blue-200/60 dark:border-blue-900/40 flex items-center justify-between">
                                <span class="text-xs font-semibold text-slate-600 dark:text-gray-300">Elapsed time</span>
                                <span 
                                    class="font-mono text-base font-extrabold text-[#0B5694] dark:text-blue-400 tracking-wider" 
                                    x-text="elapsedFormatted"
                                    aria-label="Elapsed shift time"
                                >
                                    {{ $attendanceState['elapsed_formatted'] ?? '0h 00m' }}
                                </span>
                            </div>

                        {{-- STATE 2: DAY COMPLETE --}}
                        @elseif($attendanceState['state'] === 'day_complete')
                            <div>
                                <h3 class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-white leading-tight">
                                    {{ $attendanceState['headline'] }}
                                </h3>
                                <p class="text-xs font-medium text-slate-500 dark:text-gray-400 mt-0.5">
                                    {{ $attendanceState['subtext'] }}
                                </p>
                            </div>

                            <div class="p-3 rounded-2xl bg-emerald-50/60 dark:bg-emerald-950/30 border border-emerald-200/60 dark:border-emerald-900/40 flex items-center gap-2.5 text-xs font-semibold text-emerald-800 dark:text-emerald-300">
                                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                <span>Shift completed. Great job today!</span>
                            </div>

                        {{-- STATE 3: UNAVAILABLE --}}
                        @elseif($attendanceState['state'] === 'unavailable')
                            <div>
                                <h3 class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-white leading-tight">
                                    {{ $attendanceState['headline'] }}
                                </h3>
                                <p class="text-xs font-medium text-slate-500 dark:text-gray-400 mt-1 leading-relaxed">
                                    {{ $attendanceState['subtext'] }}
                                </p>
                            </div>

                        {{-- STATE 4: NOT TIMED IN --}}
                        @else
                            <div>
                                <h3 class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-white leading-tight">
                                    {{ $attendanceState['headline'] }}
                                </h3>
                                <p class="text-xs font-medium text-slate-500 dark:text-gray-400 mt-0.5">
                                    Ready to record your training session for today.
                                </p>
                            </div>
                        @endif
                    </div>

                    {{-- Card Action Button (Full width on mobile, >= 48px) --}}
                    <div class="pt-2">
                        @if($attendanceState['state'] === 'not_timed_in')
                            <a 
                                href="{{ route('intern.attendance') }}" 
                                class="w-full min-h-[48px] px-4 py-3 rounded-xl bg-[#0B5694] hover:bg-[#084577] dark:bg-blue-600 dark:hover:bg-blue-700 text-white font-semibold text-xs sm:text-sm flex items-center justify-center gap-2.5 transition-all shadow-xs active:scale-98 cursor-pointer text-center"
                            >
                                {{-- Distinct QR Code Icon --}}
                                <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="3" width="7" height="7" rx="1.5"/>
                                    <rect x="14" y="3" width="7" height="7" rx="1.5"/>
                                    <rect x="3" y="14" width="7" height="7" rx="1.5"/>
                                    <rect x="14" y="14" width="3" height="3" rx="0.5"/>
                                    <path d="M21 14v1a2 2 0 0 1-2 2h-1"/>
                                    <path d="M14 21h7"/>
                                </svg>
                                <span>Time in</span>
                            </a>
                        @elseif($attendanceState['state'] === 'timed_in')
                            <a 
                                href="{{ route('intern.attendance') }}" 
                                class="w-full min-h-[48px] px-4 py-3 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-semibold text-xs sm:text-sm flex items-center justify-center gap-2.5 transition-all shadow-xs active:scale-98 cursor-pointer text-center"
                            >
                                <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                                    <polyline points="16 17 21 12 16 7"/>
                                    <line x1="21" y1="12" x2="9" y2="12"/>
                                </svg>
                                <span>Time out</span>
                            </a>
                        @elseif($attendanceState['state'] === 'unavailable')
                            <button 
                                type="button" 
                                disabled 
                                class="w-full min-h-[48px] px-4 py-3 rounded-xl bg-slate-100 dark:bg-gray-800 text-slate-400 dark:text-gray-500 font-semibold text-xs sm:text-sm flex items-center justify-center gap-2 cursor-not-allowed text-center border border-slate-200/60 dark:border-gray-700/60"
                            >
                                <span>Time in unavailable</span>
                            </button>
                        @else
                            {{-- Day Complete: Secondary view details link --}}
                            <a 
                                href="{{ route('intern.attendance') }}" 
                                class="w-full min-h-[44px] px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-gray-800 hover:bg-slate-100 dark:hover:bg-gray-750 text-slate-700 dark:text-gray-200 border border-slate-200/80 dark:border-gray-700 font-semibold text-xs flex items-center justify-center gap-2 transition-all cursor-pointer text-center"
                            >
                                <span>View attendance history</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ===================================================================== --}}
            {{-- ROW 1B: COMPACT INTERNSHIP PROGRESS CARD (8 COLS DESKTOP)            --}}
            {{-- ===================================================================== --}}
            <div class="lg:col-span-8 w-full">
                <div class="rounded-3xl border border-slate-200/80 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 sm:p-6 shadow-xs flex flex-col justify-between min-h-[220px] sm:min-h-[235px] text-left transition-all">
                    {{-- Card Top Header --}}
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-gray-800/80">
                        <div class="flex items-center gap-2">
                            <h2 class="text-sm font-bold text-slate-900 dark:text-white tracking-tight">
                                Internship progress
                            </h2>
                            <span class="text-xs font-medium text-slate-400 dark:text-gray-400">
                                ({{ (int) $totalRequired }} hrs target)
                            </span>
                        </div>

                        {{-- Completion Pill or Certificate Link --}}
                        @if($percentage >= 100)
                            <a 
                                href="{{ route('intern.certificates.index') }}" 
                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800/40 hover:bg-emerald-100 transition-colors"
                            >
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                <span>Requirement complete · View Certificate</span>
                            </a>
                        @else
                            <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                                {{ min(100, round($percentage)) }}% complete
                            </span>
                        @endif
                    </div>

                    {{-- Hero Metrics Row --}}
                    <div class="my-4 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                        <div>
                            <div class="flex items-baseline">
                                <span class="text-4xl sm:text-5xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                                    {{ round($rendered) }}
                                </span>
                                <span class="text-lg sm:text-xl font-bold text-slate-400 dark:text-gray-400 ml-2">
                                    HRS
                                </span>
                            </div>
                            <span class="block text-xs font-medium text-slate-500 dark:text-gray-400 mt-1">
                                Total hours rendered
                            </span>
                        </div>

                        <div class="sm:text-right">
                            <div class="flex items-baseline sm:justify-end">
                                <span class="text-2xl sm:text-3xl font-extrabold text-[#0B5694] dark:text-blue-400 tracking-tight">
                                    {{ (int) $remaining }}
                                </span>
                                <span class="text-xs font-bold text-slate-400 dark:text-gray-400 uppercase ml-2 tracking-wider">
                                    To Go
                                </span>
                            </div>
                            <span class="block text-xs font-medium text-slate-500 dark:text-gray-400 mt-1">
                                Remaining to reach target
                            </span>
                        </div>
                    </div>

                    {{-- Progress Bar with Milestone Ticks --}}
                    <div class="space-y-1.5 pt-1">
                        <div 
                            role="progressbar" 
                            aria-valuenow="{{ min(100, round($percentage)) }}" 
                            aria-valuemin="0" 
                            aria-valuemax="100" 
                            aria-label="Internship progress: {{ min(100, round($percentage)) }}% complete"
                            class="w-full bg-slate-100 dark:bg-gray-800 h-3 rounded-full overflow-hidden relative"
                        >
                            <div 
                                class="h-full rounded-full bg-[#0B5694] dark:bg-blue-500 transition-all duration-500" 
                                style="width: {{ min(100, round($percentage)) }}%;"
                            ></div>
                        </div>

                        {{-- Milestone Labels (25 / 50 / 75 / 100%) --}}
                        <div class="flex justify-between text-[10px] font-semibold text-slate-400 dark:text-gray-400 px-0.5 select-none" aria-hidden="true">
                            <span>0%</span>
                            <span>25%</span>
                            <span>50%</span>
                            <span>75%</span>
                            <span>100%</span>
                        </div>
                    </div>

                    {{-- Secondary Aggregate Line --}}
                    <div class="pt-3 border-t border-slate-100 dark:border-gray-800/80 flex flex-wrap items-center justify-between text-xs text-slate-500 dark:text-gray-400 gap-2">
                        <div class="flex items-center gap-1.5">
                            <span>This week:</span>
                            <span class="font-bold text-slate-700 dark:text-gray-200">{{ $thisWeekHours }} hrs</span>
                            @if($paceEstimate)
                                <span class="text-slate-300 dark:text-gray-600">·</span>
                                <span class="text-slate-500 dark:text-gray-400">{{ $paceEstimate }}</span>
                            @endif
                        </div>

                        @if($rendered == 0)
                            <span class="text-[11px] text-slate-400 dark:text-gray-500">
                                Your hours will update after your first logged shift.
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ===================================================================== --}}
            {{-- ROW 2A: MY TASKS PANEL (6 COLS DESKTOP)                              --}}
            {{-- ===================================================================== --}}
            <div 
                class="lg:col-span-6 w-full"
                x-data="{ activeTab: 'pending' }"
            >
                <div class="rounded-3xl border border-slate-200/80 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 sm:p-6 shadow-xs text-left min-h-[300px] flex flex-col justify-between">
                    {{-- Header with Title & View All --}}
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-gray-800/80 gap-3">
                        <div class="flex items-center gap-2">
                            <h2 class="text-sm font-bold text-slate-900 dark:text-white tracking-tight">
                                My tasks
                            </h2>
                        </div>
                        <a 
                            href="{{ route('intern.progress') }}" 
                            class="text-xs font-semibold text-[#0B5694] dark:text-blue-400 hover:underline cursor-pointer"
                        >
                            View all
                        </a>
                    </div>

                    {{-- Inline Segmented Control Tabs --}}
                    <div 
                        role="tablist" 
                        class="grid grid-cols-3 gap-1 p-1 bg-slate-100 dark:bg-gray-850 rounded-xl my-3"
                    >
                        <button 
                            type="button" 
                            role="tab"
                            :aria-selected="activeTab === 'pending'"
                            @click="activeTab = 'pending'"
                            class="py-1.5 px-2 rounded-lg text-xs font-semibold text-center transition-all cursor-pointer min-h-[36px] flex items-center justify-center gap-1.5"
                            :class="activeTab === 'pending' ? 'bg-white dark:bg-gray-700 text-[#0B5694] dark:text-white shadow-xs' : 'text-slate-600 dark:text-gray-400'"
                        >
                            <span>Pending</span>
                            <span class="px-1.5 py-0.2 rounded-md text-[10px] font-bold bg-slate-200/60 dark:bg-gray-800">
                                {{ $todoTasks->count() }}
                            </span>
                        </button>
                        <button 
                            type="button" 
                            role="tab"
                            :aria-selected="activeTab === 'submitted'"
                            @click="activeTab = 'submitted'"
                            class="py-1.5 px-2 rounded-lg text-xs font-semibold text-center transition-all cursor-pointer min-h-[36px] flex items-center justify-center gap-1.5"
                            :class="activeTab === 'submitted' ? 'bg-white dark:bg-gray-700 text-[#0B5694] dark:text-white shadow-xs' : 'text-slate-600 dark:text-gray-400'"
                        >
                            <span>Submitted</span>
                            <span class="px-1.5 py-0.2 rounded-md text-[10px] font-bold bg-slate-200/60 dark:bg-gray-800">
                                {{ $submittedTasks->count() }}
                            </span>
                        </button>
                        <button 
                            type="button" 
                            role="tab"
                            :aria-selected="activeTab === 'verified'"
                            @click="activeTab = 'verified'"
                            class="py-1.5 px-2 rounded-lg text-xs font-semibold text-center transition-all cursor-pointer min-h-[36px] flex items-center justify-center gap-1.5"
                            :class="activeTab === 'verified' ? 'bg-white dark:bg-gray-700 text-[#0B5694] dark:text-white shadow-xs' : 'text-slate-600 dark:text-gray-400'"
                        >
                            <span>Verified</span>
                            <span class="px-1.5 py-0.2 rounded-md text-[10px] font-bold bg-slate-200/60 dark:bg-gray-800">
                                {{ $verifiedTasks->count() }}
                            </span>
                        </button>
                    </div>

                    {{-- Tasks Tab Panels --}}
                    <div class="space-y-2 flex-1 min-h-[160px]">
                        {{-- 1. PENDING TAB --}}
                        <div x-show="activeTab === 'pending'" class="space-y-2">
                            @forelse($todoTasks->take(5) as $task)
                                @php
                                    $dueDate = $task->due_date ? \Carbon\Carbon::parse($task->due_date)->timezone('Asia/Manila') : null;
                                    $isOverdue = $dueDate && $dueDate->isPast();
                                    $isDueToday = $dueDate && $dueDate->isToday();
                                @endphp
                                <div class="p-3 sm:p-3.5 rounded-2xl border border-slate-200/70 dark:border-gray-800/80 bg-slate-50/50 dark:bg-gray-850/50 flex items-center justify-between gap-3 hover:border-slate-300 dark:hover:border-gray-700 transition-colors">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2">
                                            <h4 class="text-xs sm:text-sm font-bold text-slate-800 dark:text-gray-100 truncate" title="{{ e($task->title) }}">
                                                {{ $task->title }}
                                            </h4>
                                            @if($isOverdue)
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-400 border border-red-200/60 dark:border-red-900/50 shrink-0">
                                                    Overdue
                                                </span>
                                            @elseif($isDueToday)
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-200/60 dark:border-amber-900/50 shrink-0">
                                                    Due today
                                                </span>
                                            @elseif($task->status->value === 'revision')
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-orange-50 text-orange-700 dark:bg-orange-950/40 dark:text-orange-400 border border-orange-200/60 dark:border-orange-900/50 shrink-0">
                                                    Revision
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-[11px] font-medium text-slate-500 dark:text-gray-400 mt-0.5 truncate">
                                            {{ $dueDate ? 'Due ' . $dueDate->format('M j, g:i A') : 'No deadline' }} · Assigned by {{ $task->supervisor->supervisorDetails->first_name ?? 'Supervisor' }}
                                        </p>
                                    </div>

                                    <a 
                                        href="{{ route('intern.tasks.show', $task->route_key ?? $task->id) }}" 
                                        class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-white dark:bg-gray-800 hover:bg-slate-50 dark:hover:bg-gray-700 text-slate-700 dark:text-gray-200 border border-slate-200 dark:border-gray-700 transition-colors shrink-0 cursor-pointer min-h-[34px] flex items-center"
                                    >
                                        View
                                    </a>
                                </div>
                            @empty
                                <div class="py-8 text-center flex flex-col items-center justify-center">
                                    <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-gray-800 text-slate-400 dark:text-gray-500 flex items-center justify-center mb-2">
                                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                    </div>
                                    <p class="text-xs font-semibold text-slate-500 dark:text-gray-400">
                                        No pending tasks. You're all caught up!
                                    </p>
                                </div>
                            @endforelse
                        </div>

                        {{-- 2. SUBMITTED TAB --}}
                        <div x-show="activeTab === 'submitted'" class="space-y-2">
                            @forelse($submittedTasks->take(5) as $task)
                                <div class="p-3 sm:p-3.5 rounded-2xl border border-slate-200/70 dark:border-gray-800/80 bg-slate-50/50 dark:bg-gray-850/50 flex items-center justify-between gap-3 hover:border-slate-300 dark:hover:border-gray-700 transition-colors">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2">
                                            <h4 class="text-xs sm:text-sm font-bold text-slate-800 dark:text-gray-100 truncate" title="{{ e($task->title) }}">
                                                {{ $task->title }}
                                            </h4>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-[#0B5694] dark:bg-blue-950/40 dark:text-blue-400 border border-blue-200/60 dark:border-blue-900/50 shrink-0">
                                                In review
                                            </span>
                                        </div>
                                        <p class="text-[11px] font-medium text-slate-500 dark:text-gray-400 mt-0.5 truncate">
                                            Waiting for supervisor verification
                                        </p>
                                    </div>

                                    <a 
                                        href="{{ route('intern.tasks.show', $task->route_key ?? $task->id) }}" 
                                        class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-white dark:bg-gray-800 hover:bg-slate-50 dark:hover:bg-gray-700 text-slate-700 dark:text-gray-200 border border-slate-200 dark:border-gray-700 transition-colors shrink-0 cursor-pointer min-h-[34px] flex items-center"
                                    >
                                        View
                                    </a>
                                </div>
                            @empty
                                <div class="py-8 text-center flex flex-col items-center justify-center">
                                    <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-gray-800 text-slate-400 dark:text-gray-500 flex items-center justify-center mb-2">
                                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                    </div>
                                    <p class="text-xs font-semibold text-slate-500 dark:text-gray-400">
                                        No tasks waiting for supervisor review.
                                    </p>
                                </div>
                            @endforelse
                        </div>

                        {{-- 3. VERIFIED TAB --}}
                        <div x-show="activeTab === 'verified'" class="space-y-2">
                            @forelse($verifiedTasks->take(5) as $task)
                                <div class="p-3 sm:p-3.5 rounded-2xl border border-slate-200/70 dark:border-gray-800/80 bg-slate-50/50 dark:bg-gray-850/50 flex items-center justify-between gap-3 hover:border-slate-300 dark:hover:border-gray-700 transition-colors">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2">
                                            <h4 class="text-xs sm:text-sm font-bold text-slate-800 dark:text-gray-100 truncate" title="{{ e($task->title) }}">
                                                {{ $task->title }}
                                            </h4>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-900/50 shrink-0">
                                                Verified
                                            </span>
                                        </div>
                                        <p class="text-[11px] font-medium text-slate-500 dark:text-gray-400 mt-0.5 truncate">
                                            Approved by {{ $task->supervisor->supervisorDetails->first_name ?? 'Supervisor' }}
                                        </p>
                                    </div>

                                    <a 
                                        href="{{ route('intern.tasks.show', $task->route_key ?? $task->id) }}" 
                                        class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-white dark:bg-gray-800 hover:bg-slate-50 dark:hover:bg-gray-700 text-slate-700 dark:text-gray-200 border border-slate-200 dark:border-gray-700 transition-colors shrink-0 cursor-pointer min-h-[34px] flex items-center"
                                    >
                                        View
                                    </a>
                                </div>
                            @empty
                                <div class="py-8 text-center flex flex-col items-center justify-center">
                                    <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-gray-800 text-slate-400 dark:text-gray-500 flex items-center justify-center mb-2">
                                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                                    </div>
                                    <p class="text-xs font-semibold text-slate-500 dark:text-gray-400">
                                        No verified tasks yet.
                                    </p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===================================================================== --}}
            {{-- ROW 2B: OFFICE ANNOUNCEMENTS (6 COLS DESKTOP)                         --}}
            {{-- ===================================================================== --}}
            <div class="lg:col-span-6 w-full">
                <div class="rounded-3xl border border-slate-200/80 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 sm:p-6 shadow-xs text-left min-h-[300px] flex flex-col justify-between">
                    {{-- Header with Title & View All --}}
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-gray-800/80 gap-3">
                        <div class="flex items-center gap-2">
                            <h2 class="text-sm font-bold text-slate-900 dark:text-white tracking-tight">
                                Office announcements
                            </h2>
                        </div>
                        <a 
                            href="{{ route('intern.announcements') }}" 
                            class="text-xs font-semibold text-[#0B5694] dark:text-blue-400 hover:underline cursor-pointer"
                        >
                            View all
                        </a>
                    </div>

                    {{-- Announcements List (Latest 3) --}}
                    <div class="space-y-3 my-3 flex-1 min-h-[160px]">
                        @forelse($announcements as $ann)
                            <a 
                                href="{{ route('intern.announcements.show', $ann->route_key ?? $ann->id) }}" 
                                class="p-3 sm:p-3.5 rounded-2xl border border-slate-200/70 dark:border-gray-800/80 bg-slate-50/50 dark:bg-gray-850/50 flex items-start gap-3 hover:border-slate-300 dark:hover:border-gray-700 transition-colors block group cursor-pointer"
                            >
                                <x-avatar :user="$ann->user" size="9" circular="true" textSize="xs" class="shrink-0 mt-0.5" />
                                <div class="min-w-0 flex-1">
                                    <h4 
                                        class="text-xs sm:text-sm font-bold text-slate-800 dark:text-gray-100 group-hover:text-[#0B5694] dark:group-hover:text-blue-400 transition-colors line-clamp-2" 
                                        title="{{ e($ann->title) }}"
                                    >
                                        {{ $ann->title }}
                                    </h4>
                                    <p class="text-[11px] font-medium text-slate-500 dark:text-gray-400 line-clamp-1 mt-0.5">
                                        {{ Str::limit(strip_tags($ann->content), 100) }}
                                    </p>
                                    <p class="text-[10px] font-semibold text-slate-400 dark:text-gray-500 mt-1">
                                        {{ $ann->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </a>
                        @empty
                            <div class="py-10 text-center flex flex-col items-center justify-center">
                                <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-gray-800 text-slate-400 dark:text-gray-500 flex items-center justify-center mb-2">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                                </div>
                                <p class="text-xs font-semibold text-slate-500 dark:text-gray-400">
                                    No announcements yet.
                                </p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- ===================================================================== --}}
            {{-- ROW 3: RECENT ATTENDANCE PANEL (12 COLS DESKTOP, FULL WIDTH)         --}}
            {{-- ===================================================================== --}}
            <div class="lg:col-span-12 w-full">
                <div class="rounded-3xl border border-slate-200/80 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 sm:p-6 shadow-xs text-left">
                    {{-- Header with Title & View All --}}
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-gray-800/80 gap-3">
                        <div class="flex items-center gap-2">
                            <h2 class="text-sm font-bold text-slate-900 dark:text-white tracking-tight">
                                Recent attendance
                            </h2>
                            <span class="text-xs font-medium text-slate-400 dark:text-gray-500">
                                (Last 5 sessions)
                            </span>
                        </div>
                        <a 
                            href="{{ route('intern.attendance') }}" 
                            class="text-xs font-semibold text-[#0B5694] dark:text-blue-400 hover:underline cursor-pointer"
                        >
                            View all
                        </a>
                    </div>

                    {{-- Logs List or Honest Empty State --}}
                    <div class="mt-3">
                        @if($logs->isNotEmpty())
                            {{-- Desktop Table View (>= 640px) --}}
                            <div class="hidden sm:block overflow-x-auto">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="border-b border-slate-100 dark:border-gray-800/80 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-gray-500">
                                            <th class="py-2.5 px-3">Date</th>
                                            <th class="py-2.5 px-3">Time in</th>
                                            <th class="py-2.5 px-3">Time out</th>
                                            <th class="py-2.5 px-3">Shift duration</th>
                                            <th class="py-2.5 px-3 text-right">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100/70 dark:divide-gray-800/60 text-xs">
                                        @foreach($logs as $log)
                                            @php
                                                $in = $log->clock_in ? \Carbon\Carbon::parse($log->clock_in)->timezone('Asia/Manila') : null;
                                                $out = $log->clock_out ? \Carbon\Carbon::parse($log->clock_out)->timezone('Asia/Manila') : null;
                                                
                                                $durationText = '—';
                                                if ($in && $out) {
                                                    $diffMin = $in->diffInMinutes($out);
                                                    if ($diffMin > 300) {
                                                        $diffMin = max(0, $diffMin - 60);
                                                    }
                                                    $h = floor($diffMin / 60);
                                                    $m = $diffMin % 60;
                                                    $durationText = sprintf('%dh %02dm', $h, $m);
                                                } elseif ($in && !$out) {
                                                    $durationText = 'In progress';
                                                }

                                                $status = $log->status ?? 'on_time';
                                            @endphp
                                            <tr class="hover:bg-slate-50/60 dark:hover:bg-gray-850/50 transition-colors">
                                                <td class="py-3 px-3 font-semibold text-slate-800 dark:text-gray-100">
                                                    {{ $in ? $in->format('M j, Y') : '—' }}
                                                </td>
                                                <td class="py-3 px-3 font-medium text-slate-600 dark:text-gray-300">
                                                    {{ $in ? $in->format('g:i A') : '—' }}
                                                </td>
                                                <td class="py-3 px-3 font-medium text-slate-600 dark:text-gray-300">
                                                    {{ $out ? $out->format('g:i A') : ($in ? 'Active' : '—') }}
                                                </td>
                                                <td class="py-3 px-3 font-medium text-slate-600 dark:text-gray-300 font-mono">
                                                    {{ $durationText }}
                                                </td>
                                                <td class="py-3 px-3 text-right">
                                                    <span 
                                                        class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider
                                                        {{ $status === 'on_time' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200/60' : '' }}
                                                        {{ $status === 'late' ? 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-200/60' : '' }}
                                                        {{ $status === 'excused' ? 'bg-blue-50 text-[#0B5694] dark:bg-blue-950/40 dark:text-blue-400 border border-blue-200/60' : '' }}
                                                        {{ $status === 'absent' ? 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400 border border-rose-200/60' : '' }}
                                                        {{ $status === 'incomplete' ? 'bg-slate-100 text-slate-700 dark:bg-gray-800 dark:text-gray-300 border border-slate-200' : '' }}
                                                        "
                                                    >
                                                        {{ str_replace('_', ' ', $status) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{-- Mobile Card View (< 640px) --}}
                            <div class="sm:hidden space-y-2.5">
                                @foreach($logs as $log)
                                    @php
                                        $in = $log->clock_in ? \Carbon\Carbon::parse($log->clock_in)->timezone('Asia/Manila') : null;
                                        $out = $log->clock_out ? \Carbon\Carbon::parse($log->clock_out)->timezone('Asia/Manila') : null;
                                        $status = $log->status ?? 'on_time';
                                    @endphp
                                    <div class="p-3.5 rounded-2xl border border-slate-200/70 dark:border-gray-800/80 bg-slate-50/50 dark:bg-gray-850/50 flex items-center justify-between gap-3">
                                        <div>
                                            <h4 class="text-xs font-bold text-slate-800 dark:text-gray-100">
                                                {{ $in ? $in->format('M j, Y') : '—' }}
                                            </h4>
                                            <p class="text-[11px] font-medium text-slate-500 dark:text-gray-400 mt-0.5">
                                                {{ $in ? $in->format('g:i A') : '—' }} — {{ $out ? $out->format('g:i A') : ($in ? 'Active' : '—') }}
                                            </p>
                                        </div>
                                        <span 
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider
                                            {{ $status === 'on_time' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200/60' : 'bg-slate-100 text-slate-700 dark:bg-gray-800 dark:text-gray-300' }}
                                            "
                                        >
                                            {{ str_replace('_', ' ', $status) }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            {{-- Step 0 Honest Empty State --}}
                            <div class="py-8 sm:py-10 text-center flex flex-col items-center justify-center max-w-md mx-auto">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-gray-800 text-slate-400 dark:text-gray-500 flex items-center justify-center mb-3">
                                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                </div>
                                <h4 class="text-sm font-bold text-slate-800 dark:text-gray-200">
                                    No attendance recorded yet
                                </h4>
                                @if($rendered > 0)
                                    <p class="text-xs font-medium text-slate-500 dark:text-gray-400 mt-1 leading-relaxed">
                                        Your cumulative total of {{ round($rendered) }} hours includes hours credited directly to your profile or completed assignments without individual shift logs.
                                    </p>
                                @else
                                    <p class="text-xs font-medium text-slate-500 dark:text-gray-400 mt-1">
                                        Your daily shift logs will appear here after your first time in.
                                    </p>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>

        {{-- ========================================================================= --}}
        {{-- 4. EXTENSION REQUEST MODAL                                                --}}
        {{-- ========================================================================= --}}
        <div 
            x-show="extensionModalOpen" 
            class="fixed inset-0 z-[150] flex items-center justify-center p-4 bg-slate-900/60 dark:bg-black/75 backdrop-blur-sm" 
            x-cloak
            x-transition:enter="transition ease-out duration-250"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        >
            <div 
                @click.away="extensionModalOpen = false" 
                class="bg-white dark:bg-gray-900 w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden border border-slate-200/80 dark:border-gray-800 p-6 sm:p-8 text-left"
            >
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <span class="text-xs font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider">Internship Schedule</span>
                        <h3 class="text-xl font-extrabold text-slate-900 dark:text-white tracking-tight mt-0.5">Request Date Extension</h3>
                    </div>
                    <button 
                        type="button" 
                        @click="extensionModalOpen = false" 
                        class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-gray-300 rounded-xl transition-colors cursor-pointer"
                        aria-label="Close dialog"
                    >
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>

                <form action="{{ route('intern.schedule.extension.store') }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <div class="space-y-1.5">
                        <label for="new_end_date" class="text-xs font-bold text-slate-700 dark:text-gray-300">Proposed End Date</label>
                        <input 
                            type="date" 
                            id="new_end_date" 
                            name="new_end_date" 
                            required
                            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-gray-800 border border-slate-300 dark:border-gray-700 rounded-xl text-xs sm:text-sm font-semibold text-slate-800 dark:text-gray-100 focus:ring-1 focus:ring-[#0B5694] focus:border-[#0B5694]"
                        >
                    </div>

                    <div class="space-y-1.5">
                        <label for="reason" class="text-xs font-bold text-slate-700 dark:text-gray-300">Reason for Extension</label>
                        <textarea 
                            id="reason" 
                            name="reason" 
                            required 
                            rows="3" 
                            placeholder="Describe why you need an extension (e.g. absences, suspension makeup, schedule adjustments...)"
                            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-gray-800 border border-slate-300 dark:border-gray-700 rounded-xl text-xs sm:text-sm font-medium text-slate-800 dark:text-gray-100 focus:ring-1 focus:ring-[#0B5694] focus:border-[#0B5694] resize-none"
                        ></textarea>
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100 dark:border-gray-800">
                        <button 
                            type="button" 
                            @click="extensionModalOpen = false" 
                            class="px-4 py-2.5 bg-slate-100 dark:bg-gray-800 text-slate-700 dark:text-gray-300 rounded-xl text-xs font-semibold hover:bg-slate-200 transition-colors cursor-pointer min-h-[38px]"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit" 
                            class="px-5 py-2.5 bg-[#0B5694] hover:bg-[#084577] text-white rounded-xl text-xs font-semibold transition-all shadow-xs cursor-pointer min-h-[38px]"
                        >
                            Submit Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-page-layout>

<script>
function attendanceTimer(config) {
    return {
        state: config.state,
        clockInEpochMs: config.clockInEpochMs,
        serverNowEpochMs: config.serverNowEpochMs,
        clientStartPerfMs: performance.now(),
        elapsedFormatted: config.initialElapsedFormatted || '0h 00m',
        timerInterval: null,

        init() {
            if (this.state === 'timed_in' && this.clockInEpochMs > 0) {
                this.updateTick();
                this.timerInterval = setInterval(() => {
                    this.updateTick();
                }, 1000);

                // Pause timer when tab is hidden, resume when visible
                document.addEventListener('visibilitychange', () => {
                    if (document.hidden) {
                        if (this.timerInterval) clearInterval(this.timerInterval);
                    } else {
                        this.updateTick();
                        this.timerInterval = setInterval(() => {
                            this.updateTick();
                        }, 1000);
                    }
                });
            }
        },

        updateTick() {
            // Absolute elapsed time computed from server baseline + monotonic local performance
            const elapsedMs = (this.serverNowEpochMs - this.clockInEpochMs) + (performance.now() - this.clientStartPerfMs);
            const totalSeconds = Math.max(0, Math.floor(elapsedMs / 1000));
            const hours = Math.floor(totalSeconds / 3600);
            const minutes = Math.floor((totalSeconds % 3600) / 60);
            this.elapsedFormatted = `${hours}h ${minutes < 10 ? '0' : ''}${minutes}m`;
        }
    };
}
</script>
@endsection
