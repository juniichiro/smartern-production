@extends('layouts.app')

@section('title', 'Attendance Logs')

@section('content')
<x-page-layout 
    portalType="Supervisor" 
    userName="{{ Auth::user()->supervisorDetails->first_name }}" 
    userRole="{{ Auth::user()->supervisorDetails->office->name }}"
>
    <div class="flex flex-col h-full" x-data="{ ...supervisorAttendanceManager(), activeTab: '{{ request('tab', 'all') }}', searchQuery: '{{ request('search', '') }}' }">
        {{-- Header --}}
        <x-page-header 
            title="Attendance Logs"
            subtitle="Monitor real-time check-ins and internship hours"
        />

        @php
            $activeNowLogs = $allLogs->filter(fn($log) => $log['type'] === 'actual' && empty($log['clock_out']) && $log['clock_in'] && $log['clock_in']->isToday());
            $uniqueInterns = $allLogs->pluck('intern')->unique('user_id');
            $groupedLogs = $allLogs->groupBy(fn($log) => $log['intern']->user_id);
            $startOfWeek = now()->startOfWeek();
            $endOfWeek = now()->endOfWeek();
        @endphp

        {{-- Attendance Summary Section --}}
        <div class="bg-white dark:bg-gray-900 rounded-[20px] md:rounded-[24px] p-3 md:p-4 shadow-sm border border-gray-100 dark:border-gray-800/80 mb-6 relative overflow-hidden">
            <template x-if="loading">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <x-skeleton class="h-24 w-full rounded-2xl" />
                    <x-skeleton class="h-24 w-full rounded-2xl" />
                    <x-skeleton class="h-24 w-full rounded-2xl" />
                </div>
            </template>
            <div x-show="!loading" x-transition.opacity.duration.500ms class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                {{-- Card 1: Today's Presence --}}
                @php
                    $totalExpected = $uniqueInterns->count();
                @endphp
                <div class="flex items-center gap-3 p-3 md:p-4 bg-gray-50/50 dark:bg-gray-800/40 rounded-2xl border border-gray-50/50 dark:border-gray-800/30">
                    <div class="p-2 bg-blue-50 dark:bg-blue-600/20 text-[#0B5694] dark:text-blue-400 rounded-xl inline-flex items-center justify-center shrink-0">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <div class="text-left">
                        <p class="text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest leading-none mb-1">Today's Presence</p>
                        <p class="text-xs sm:text-sm md:text-base font-black text-gray-900 dark:text-gray-100 tracking-tight leading-none">
                            {{ $activeCount }} / {{ $totalExpected }} Present
                        </p>
                    </div>
                </div>

                {{-- Card 2: Weekly Outputs --}}
                <div class="flex items-center gap-3 p-3 md:p-4 bg-gray-50/50 dark:bg-gray-800/40 rounded-2xl border border-gray-50/50 dark:border-gray-800/30">
                    <div class="p-2 bg-emerald-50 dark:bg-emerald-600/20 text-emerald-600 dark:text-emerald-400 rounded-xl inline-flex items-center justify-center shrink-0">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                    <div class="text-left">
                        <p class="text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest leading-none mb-1">Weekly Output</p>
                        <p class="text-xs sm:text-sm md:text-base font-black text-gray-900 dark:text-gray-100 tracking-tight leading-none">
                            {{ round($weeklyHours) }} Hrs
                        </p>
                    </div>
                </div>

                {{-- Card 3: Punctuality Health --}}
                @php
                    $thisWeeksLogs = $allLogs->filter(fn($l) => $l['date']->gte($startOfWeek) && $l['date']->lte($endOfWeek));
                    $weeklyLates = $thisWeeksLogs->filter(fn($l) => $l['type'] === 'actual' && $l['status'] === 'late')->count();
                    $weeklyAbsents = $thisWeeksLogs->filter(fn($l) => $l['type'] === 'workday' && $l['status'] === 'absent')->count();
                @endphp
                <div class="flex items-center gap-3 p-3 md:p-4 bg-gray-50/50 dark:bg-gray-800/40 rounded-2xl border border-gray-50/50 dark:border-gray-800/30">
                    <div class="p-2 bg-orange-50 dark:bg-orange-600/20 text-orange-600 dark:text-orange-400 rounded-xl inline-flex items-center justify-center shrink-0">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    </div>
                    <div class="text-left">
                        <p class="text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest leading-none mb-1">Weekly Alerts</p>
                        <div class="flex items-center gap-2 mt-0.5 leading-none">
                            <span class="text-xs font-black text-orange-500 uppercase tracking-tight">{{ $weeklyLates }} Lates</span>
                            <span class="text-gray-300 dark:text-gray-750 font-bold">•</span>
                            <span class="text-xs font-black text-rose-500 uppercase tracking-tight">{{ $weeklyAbsents }} Absents</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ACTIVE PRESENCE HUB --}}
        @if($activeNowLogs->isNotEmpty())
            <div class="bg-white dark:bg-gray-900 rounded-[20px] md:rounded-[24px] p-3 md:p-4 shadow-sm border border-gray-100 dark:border-gray-800/80 mb-6 relative overflow-hidden">
                <p class="text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-3">Currently Clocked In</p>
                <div class="flex flex-wrap gap-2 md:gap-3">
                    @foreach($activeNowLogs as $activeLog)
                        @php
                            $activeIntern = $activeLog['intern'];
                        @endphp
                        <div class="flex items-center gap-3 bg-emerald-50/40 dark:bg-emerald-950/10 border border-emerald-100/50 dark:border-emerald-900/30 px-3.5 py-2 rounded-xl">
                            <div class="relative">
                                <x-avatar :user="$activeIntern" size="8" border="0" textSize="xs" />
                                <span class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full bg-emerald-500 border-2 border-white dark:border-gray-900 animate-ping"></span>
                                <span class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full bg-emerald-500 border-2 border-white dark:border-gray-900"></span>
                            </div>
                            <div class="text-left">
                                <p class="text-xs font-black text-gray-800 dark:text-gray-200 uppercase tracking-tight">
                                    {{ $activeIntern->first_name }} {{ $activeIntern->last_name }}
                                </p>
                                <p class="text-[9px] text-emerald-600 dark:text-emerald-400 font-bold uppercase tracking-widest mt-0.5">
                                    Since {{ $activeLog['clock_in']->format('h:i A') }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- CONTROLS TOOLBAR --}}
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 md:gap-4 bg-white dark:bg-gray-900 p-3 md:p-4 rounded-2xl md:rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm">
            <!-- Left side: Tabs for filtering -->
            <div x-show="activeView === 'timeline'" x-transition class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-2 md:pb-0">
                <a href="{{ route('supervisor.attendance', ['tab' => 'all', 'search' => request('search')]) }}" 
                        :class="activeTab === 'all' ? 'bg-[#0B5694] dark:bg-blue-600 text-white shadow-md shadow-blue-500/10' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800'" 
                        class="px-3 sm:px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all whitespace-nowrap active:scale-95 flex items-center justify-center">
                    <span class="sm:hidden">All</span><span class="hidden sm:inline">All Logs</span>
                </a>
                <a href="{{ route('supervisor.attendance', ['tab' => 'active', 'search' => request('search')]) }}" 
                        :class="activeTab === 'active' ? 'bg-emerald-500 text-white shadow-md shadow-emerald-500/10' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800'" 
                        class="px-3 sm:px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all whitespace-nowrap active:scale-95 flex items-center justify-center">
                    Active
                </a>
                <a href="{{ route('supervisor.attendance', ['tab' => 'exceptions', 'search' => request('search')]) }}" 
                        :class="activeTab === 'exceptions' ? 'bg-orange-500 text-white shadow-md shadow-orange-500/10' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800'" 
                        class="px-3 sm:px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all whitespace-nowrap active:scale-95 flex items-center justify-center">
                    Exceptions
                </a>
                <a href="{{ route('supervisor.attendance', ['tab' => 'offdays', 'search' => request('search')]) }}" 
                        :class="activeTab === 'offdays' ? 'bg-purple-500 text-white shadow-md shadow-purple-500/10' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800'" 
                        class="px-3 sm:px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all whitespace-nowrap active:scale-95 flex items-center justify-center">
                    <span class="sm:hidden">Off-days</span><span class="hidden sm:inline">Off-days</span>
                </a>
            </div>

            <!-- Right side: Search and View Switcher -->
            <div class="flex items-center gap-3 md:ml-auto">
                <!-- Search bar -->
                <form method="GET" action="{{ route('supervisor.attendance') }}" class="relative flex-1 sm:w-48 md:w-64">
                    <input type="hidden" name="tab" value="{{ request('tab', 'all') }}">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400 dark:text-gray-500">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    </span>
                    <input type="text" 
                           name="search"
                           x-model="searchQuery" 
                           placeholder="Search intern name or ID..." 
                           class="w-full pl-9 pr-4 py-2.5 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-gray-200 text-xs rounded-xl border border-gray-100 dark:border-gray-700/50 focus:outline-none focus:border-[#0B5694] dark:focus:border-blue-500 transition-all font-semibold uppercase tracking-wider">
                </form>

                <!-- View Switcher -->
                <div class="flex items-center bg-gray-100 dark:bg-gray-800 p-1 rounded-xl border border-gray-100 dark:border-gray-700/50 shrink-0">
                    <button @click="activeView = 'timeline'" 
                            :class="activeView === 'timeline' ? 'bg-white dark:bg-gray-700 text-[#0B5694] dark:text-blue-400 shadow-sm' : 'text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300'" 
                            class="p-2 rounded-lg transition-all active:scale-95"
                            title="Timeline Logs View">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                    </button>
                    <button @click="activeView = 'directory'" 
                            :class="activeView === 'directory' ? 'bg-white dark:bg-gray-700 text-[#0B5694] dark:text-blue-400 shadow-sm' : 'text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300'" 
                            class="p-2 rounded-lg transition-all active:scale-95"
                            title="Interns Directory Grid">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- VIEW 1: TIMELINE MASTER LOGS --}}
        <div x-show="activeView === 'timeline'" class="bg-white dark:bg-gray-900 rounded-[25px] md:rounded-[35px] overflow-hidden shadow-sm border border-gray-100 dark:border-gray-800 p-3 sm:p-6 lg:p-10 mb-12" x-cloak>
            <div class="mb-8 flex justify-between items-end">
                <div>
                    <h3 class="text-base md:text-lg font-black text-gray-800 dark:text-gray-100 tracking-tight uppercase">Activity Registry</h3>
                    <p class="text-[10px] md:text-xs text-gray-400 dark:text-gray-500 mt-1 font-bold uppercase tracking-wider">Detailed chronological log stream across all interns</p>
                </div>
            </div>

            <div class="border border-gray-100 dark:border-gray-800 rounded-3xl overflow-hidden shadow-inner">
                <template x-if="loading">
                    <div class="space-y-4 p-6">
                        @for($i = 0; $i < min(count($allLogs) ?: 5, 5); $i++)
                            <x-skeleton class="h-16 w-full rounded-xl" />
                        @endfor
                    </div>
                </template>
                <div x-show="!loading" x-transition.opacity.duration.500ms>
                    {{-- Mobile & Tablet: Card List --}}
                    <div class="lg:hidden divide-y divide-gray-50 dark:divide-gray-800">
                        @forelse($attendances as $log)
                            @php
                                $logIntern = $log['intern'];
                                $isTodayActive = $log['type'] === 'actual' && empty($log['clock_out']) && $log['clock_in'] && $log['clock_in']->isToday();
                                $status = $log['status'];
                                if ($log['type'] === 'actual' && !$log['clock_out']) $status = 'active';
                                $statusConfig = match($status) {
                                    'late' => ['label' => 'Late', 'class' => 'bg-orange-50 dark:bg-orange-950/20 text-orange-500 dark:text-orange-400 border-orange-100/30 dark:border-orange-900/30'],
                                    'absent' => ['label' => 'Absent', 'class' => 'bg-rose-50 dark:bg-rose-950/20 text-rose-600 dark:text-rose-400 border-rose-100/30 dark:border-rose-900/30'],
                                    'excused' => ['label' => 'Excused', 'class' => 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 border-blue-100/30 dark:border-blue-900/30'],
                                    'holiday' => ['label' => 'Holiday', 'class' => 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 border-emerald-100/30 dark:border-emerald-900/30'],
                                    'suspension' => ['label' => 'Suspended', 'class' => 'bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400 border-purple-100/30 dark:border-purple-900/30 animate-pulse'],
                                    'active' => ['label' => 'Active', 'class' => 'bg-blue-50 dark:bg-blue-900/20 text-blue-500 dark:text-blue-400 border-blue-100/30 dark:border-blue-900/30 animate-pulse'],
                                    default => ['label' => 'On-Time', 'class' => 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 border-emerald-100/30 dark:border-emerald-900/30'],
                                };
                            @endphp
                            <div x-show="(activeTab === 'all' || 
                                         (activeTab === 'active' && {{ $isTodayActive ? 'true' : 'false' }}) || 
                                         (activeTab === 'exceptions' && ('{{ $log['status'] }}' === 'late' || '{{ $log['status'] }}' === 'absent')) || 
                                         (activeTab === 'offdays' && ('{{ $log['status'] }}' === 'holiday' || '{{ $log['status'] }}' === 'suspension')))
                                       && (searchQuery === '' || '{{ addslashes(strtolower($logIntern->first_name . ' ' . $logIntern->last_name)) }}'.includes(searchQuery.toLowerCase()) || '{{ addslashes(strtolower($logIntern->student_id)) }}'.includes(searchQuery.toLowerCase()))"
                                 class="p-4 sm:p-5 hover:bg-gray-50/50 dark:hover:bg-gray-800/20 transition-colors">
                                <div class="flex items-start gap-3 sm:gap-4 mb-3">
                                    <x-avatar :user="$logIntern" size="10" border="0" textSize="xs" />
                                    <div class="min-w-0 flex-1">
                                        <p class="font-black text-gray-900 dark:text-gray-100 text-sm uppercase truncate leading-tight">{{ strtolower($logIntern->first_name) }} {{ strtolower($logIntern->last_name) }}</p>
                                        <p class="text-[10px] text-gray-400 dark:text-gray-500 font-bold uppercase tracking-wider">{{ $logIntern->student_id }}</p>
                                    </div>
                                    <div class="shrink-0 flex items-center gap-1.5">
                                        <span class="px-3 py-1.5 {{ $statusConfig['class'] }} text-[10px] font-bold rounded-full border tracking-wider" title="{{ $log['remarks'] }}">
                                            {{ $statusConfig['label'] }}
                                        </span>
                                        @if($log['type'] === 'actual' && !empty($log['record']->latitude) && !empty($log['record']->longitude))
                                            <a href="https://www.google.com/maps/search/?api=1&query={{ $log['record']->latitude }},{{ $log['record']->longitude }}" target="_blank" class="inline-flex items-center justify-center w-7 h-7 bg-blue-50 dark:bg-blue-900/20 hover:bg-[#0B5694] hover:text-white text-[#0B5694] dark:text-blue-400 rounded-lg border border-blue-100 dark:border-blue-900/30 transition active:scale-95 shrink-0" title="Verify Location">
                                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-3 text-xs">
                                    <div>
                                        <span class="text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest block">Date</span>
                                        <span class="font-bold text-gray-900 dark:text-gray-100">{{ $log['date']->format('M d, Y') }}</span>
                                    </div>
                                    <div>
                                        <span class="text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest block">Time In</span>
                                        <span class="font-medium text-gray-700 dark:text-gray-300">{{ $log['clock_in'] ? $log['clock_in']->format('h:i A') : '---' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest block">Time Out</span>
                                        <span class="font-medium text-gray-700 dark:text-gray-300">{{ $log['clock_out'] ? $log['clock_out']->format('h:i A') : '---' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest block">Duration</span>
                                        <span class="font-bold text-gray-900 dark:text-gray-100">
                                            @if($log['type'] === 'actual')
                                                @if($log['clock_out'])
                                                    {{ fmod($log['duration'], 1) == 0 ? (int)$log['duration'] : round($log['duration'], 2) }} HRS
                                                @else
                                                    <span class="text-blue-500 dark:text-blue-400 italic text-[11px] font-semibold animate-pulse">In Progress</span>
                                                @endif
                                            @else
                                                0 HRS
                                            @endif
                                        </span>
                                    </div>
                                </div>
                                @if($log['remarks'])
                                    <p class="mt-2 text-[10px] text-gray-400 dark:text-gray-500 font-bold uppercase tracking-tight italic line-clamp-1">{{ $log['remarks'] }}</p>
                                @endif
                            </div>
                        @empty
                            <div class="py-12 text-center text-gray-400 italic">No logs found.</div>
                        @endforelse
                    </div>

                    {{-- Desktop: Table --}}
                    <div class="hidden lg:block overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left">
                            <thead class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-100 dark:border-gray-700">
                                <tr class="text-gray-400 dark:text-gray-500 uppercase text-[9px] font-black tracking-widest">
                                    <th class="py-3 md:py-5 px-3 md:px-6">Intern</th>
                                    <th class="py-3 md:py-5 px-3 md:px-4 text-center">Date</th>
                                    <th class="py-3 md:py-5 px-3 md:px-4 text-center">Time In</th>
                                    <th class="py-3 md:py-5 px-3 md:px-4 text-center">Time Out</th>
                                    <th class="py-3 md:py-5 px-3 md:px-4 text-center">Duration</th>
                                    <th class="py-3 md:py-5 px-3 md:px-6 text-center">Status</th>
                                    <th class="py-3 md:py-5 px-3 md:px-6 text-left">Remarks</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 dark:divide-gray-800 text-xs font-semibold text-gray-600 dark:text-gray-400">
                                @forelse($attendances as $log)
                                    @php
                                        $logIntern = $log['intern'];
                                        $isTodayActive = $log['type'] === 'actual' && empty($log['clock_out']) && $log['clock_in'] && $log['clock_in']->isToday();
                                        $status = $log['status'];
                                        if ($log['type'] === 'actual' && !$log['clock_out']) $status = 'active';
                                        $statusConfig = match($status) {
                                            'late' => ['label' => 'Late', 'class' => 'bg-orange-50 dark:bg-orange-950/20 text-orange-500 dark:text-orange-400 border-orange-100/30 dark:border-orange-900/30'],
                                            'absent' => ['label' => 'Absent', 'class' => 'bg-rose-50 dark:bg-rose-950/20 text-rose-600 dark:text-rose-400 border-rose-100/30 dark:border-rose-900/30'],
                                            'excused' => ['label' => 'Excused', 'class' => 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 border-blue-100/30 dark:border-blue-900/30'],
                                            'holiday' => ['label' => 'Holiday', 'class' => 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 border-emerald-100/30 dark:border-emerald-900/30'],
                                            'suspension' => ['label' => 'Suspended', 'class' => 'bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400 border-purple-100/30 dark:border-purple-900/30 animate-pulse'],
                                            'active' => ['label' => 'Active', 'class' => 'bg-blue-50 dark:bg-blue-900/20 text-blue-500 dark:text-blue-400 border-blue-100/30 dark:border-blue-900/30 animate-pulse'],
                                            default => ['label' => 'On-Time', 'class' => 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 border-emerald-100/30 dark:border-emerald-900/30'],
                                        };
                                    @endphp
                                    <tr x-show="(activeTab === 'all' || 
                                                 (activeTab === 'active' && {{ $isTodayActive ? 'true' : 'false' }}) || 
                                                 (activeTab === 'exceptions' && ('{{ $log['status'] }}' === 'late' || '{{ $log['status'] }}' === 'absent')) || 
                                                 (activeTab === 'offdays' && ('{{ $log['status'] }}' === 'holiday' || '{{ $log['status'] }}' === 'suspension')))
                                               && (searchQuery === '' || '{{ addslashes(strtolower($logIntern->first_name . ' ' . $logIntern->last_name)) }}'.includes(searchQuery.toLowerCase()) || '{{ addslashes(strtolower($logIntern->student_id)) }}'.includes(searchQuery.toLowerCase()))"
                                        class="hover:bg-gray-50/50 dark:hover:bg-gray-800/20 transition-colors group">

                                        {{-- Intern Profile --}}
                                        <td class="py-3 md:py-5 px-3 md:px-6">
                                            <div class="flex items-center gap-4">
                                                <x-avatar :user="$logIntern" size="12" border="0" textSize="xs" />
                                                <div>
                                                    <p class="font-bold text-gray-900 dark:text-gray-100 text-sm tracking-tight group-hover:text-[#0B5694] dark:group-hover:text-blue-400 transition-colors capitalize">
                                                        {{ strtolower($logIntern->first_name) }} {{ strtolower($logIntern->last_name) }}
                                                    </p>
                                                    <p class="text-xs text-gray-400 dark:text-gray-500 font-bold mt-0.5">
                                                        {{ $logIntern->student_id }}
                                                    </p>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Date --}}
                                        <td class="py-3 md:py-5 px-3 md:px-4 text-center font-bold text-gray-900 dark:text-gray-100 whitespace-nowrap text-sm">
                                            {{ $log['date']->format('M d, Y') }}
                                        </td>

                                        {{-- Time In --}}
                                        <td class="py-3 md:py-5 px-3 md:px-4 text-center text-gray-700 dark:text-gray-300 font-medium whitespace-nowrap text-sm">
                                            {{ $log['clock_in'] ? $log['clock_in']->format('h:i A') : '---' }}
                                        </td>

                                        {{-- Time Out --}}
                                        <td class="py-3 md:py-5 px-3 md:px-4 text-center text-gray-700 dark:text-gray-300 font-medium whitespace-nowrap text-sm">
                                            {{ $log['clock_out'] ? $log['clock_out']->format('h:i A') : '---' }}
                                        </td>

                                        {{-- Duration --}}
                                        <td class="py-3 md:py-5 px-3 md:px-4 text-center font-bold text-gray-900 dark:text-gray-100 whitespace-nowrap text-sm">
                                            @if($log['type'] === 'actual')
                                                @if($log['clock_out'])
                                                    {{ fmod($log['duration'], 1) == 0 ? (int)$log['duration'] : round($log['duration'], 2) }} HRS
                                                @else
                                                    <span class="text-blue-500 dark:text-blue-400 italic text-xs font-semibold animate-pulse">In Progress</span>
                                                @endif
                                            @else
                                                0 HRS
                                            @endif
                                        </td>

                                        {{-- Status Badge --}}
                                        <td class="py-3 md:py-5 px-3 md:px-6 text-center whitespace-nowrap">
                                            <div class="flex items-center justify-center gap-1.5">
                                                <span class="px-3 py-1.5 {{ $statusConfig['class'] }} text-[10px] font-bold rounded-full border tracking-wider" title="{{ $log['remarks'] }}">
                                                    {{ $statusConfig['label'] }}
                                                </span>

                                                @if($log['type'] === 'actual' && !empty($log['record']->latitude) && !empty($log['record']->longitude))
                                                    <a href="https://www.google.com/maps/search/?api=1&query={{ $log['record']->latitude }},{{ $log['record']->longitude }}" 
                                                       target="_blank" 
                                                       class="inline-flex items-center justify-center w-6 h-6 bg-blue-50 dark:bg-blue-900/20 hover:bg-[#0B5694] hover:text-white text-[#0B5694] dark:text-blue-400 rounded-lg border border-blue-100 dark:border-blue-900/30 transition active:scale-95 shrink-0" 
                                                       title="Verify Location ({{ $log['record']->latitude }}, {{ $log['record']->longitude }})">
                                                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>

                                        {{-- Remarks --}}
                                        <td class="py-3 md:py-5 px-3 md:px-6 text-left">
                                            <span class="text-[10px] text-gray-400 dark:text-gray-500 font-bold uppercase tracking-tight italic line-clamp-1 max-w-[150px]" title="{{ $log['remarks'] }}">
                                                {{ $log['remarks'] ?? '---' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-12 text-center text-gray-400 italic">No logs found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                
                {{-- Pagination Links --}}
                @if($attendances->hasPages())
                <div class="flex flex-col sm:flex-row items-center justify-between gap-6 px-6 py-4 bg-gray-50/50 dark:bg-gray-800/20 border-t border-gray-100 dark:border-gray-800">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Showing {{ $attendances->firstItem() }} to {{ $attendances->lastItem() }} of {{ $attendances->total() }} logs</p>
                    {{ $attendances->appends(request()->query())->links('vendor.pagination.tailwind-custom') }}
                </div>
                @endif
            </div>
        </div>

        {{-- VIEW 2: INTERNS DIRECTORY GRID --}}
        <template x-if="loading">
            <div x-show="activeView === 'directory'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-12" x-cloak>
                @for($i = 0; $i < min(count($uniqueInterns) ?: 3, 3); $i++)
                    <div class="bg-white dark:bg-gray-900 rounded-[25px] md:rounded-[30px] border border-gray-100 dark:border-gray-800 p-4 md:p-6 space-y-6">
                        <div class="flex items-start gap-4">
                            <x-skeleton class="w-12 h-12 rounded-full shrink-0" />
                            <div class="flex-1 space-y-2">
                                <x-skeleton class="h-4 w-3/4 rounded-lg" />
                                <x-skeleton class="h-3 w-1/2 rounded-lg" />
                            </div>
                        </div>
                        <div class="flex items-center justify-between p-4 bg-gray-50/50 dark:bg-gray-800/20 rounded-2xl">
                            <div class="space-y-2">
                                <x-skeleton class="h-3 w-16 rounded-lg" />
                                <x-skeleton class="h-4 w-24 rounded-lg" />
                            </div>
                            <x-skeleton class="w-14 h-14 rounded-full shrink-0" />
                        </div>
                        <div class="flex items-center justify-between pt-3 border-t border-gray-50 dark:border-gray-800/60">
                            <x-skeleton class="h-5 w-20 rounded-full" />
                            <x-skeleton class="h-8 w-24 rounded-xl" />
                        </div>
                    </div>
                @endfor
            </div>
        </template>
        <div x-show="activeView === 'directory' && !loading" x-transition.opacity.duration.500ms class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-12" x-cloak>
            @forelse($uniqueInterns as $intern)
                @php
                    $internLogs = $groupedLogs->get($intern->user_id, collect());
                    
                    // Stats
                    $totalHours = $internLogs->filter(fn($l) => $l['type'] === 'actual' && $l['clock_out'])->sum('duration');
                    $lates = $internLogs->filter(fn($l) => $l['type'] === 'actual' && $l['status'] === 'late')->count();
                    $absents = $internLogs->filter(fn($l) => $l['type'] === 'workday' && $l['status'] === 'absent')->count();
                    
                    // Today's Status
                    $todayLog = $internLogs->first(fn($l) => $l['date']->isToday());
                    $todayStatus = 'not_in';
                    $todayDetails = 'Not Clocked In';
                    
                    if ($todayLog) {
                        if ($todayLog['type'] === 'actual') {
                            if (empty($todayLog['clock_out'])) {
                                $todayStatus = 'active';
                                $todayDetails = 'Active Now (In: ' . $todayLog['clock_in']->format('h:i A') . ')';
                            } else {
                                $todayStatus = 'completed';
                                $todayDetails = 'Completed (Shift: ' . round($todayLog['duration']) . ' hrs)';
                            }
                        } else {
                            if ($todayLog['status'] === 'holiday') {
                                $todayStatus = 'holiday';
                                $todayDetails = 'Holiday: ' . $todayLog['remarks'];
                            } elseif ($todayLog['status'] === 'suspension') {
                                $todayStatus = 'suspended';
                                $todayDetails = 'Suspended: ' . $todayLog['remarks'];
                            } elseif ($todayLog['status'] === 'absent') {
                                $todayStatus = 'absent';
                                $todayDetails = 'Absent today';
                            }
                        }
                    }
                    
                    $targetHours = $intern->required_hours ?? 480;
                    $pct = $targetHours > 0 ? min(100, max(0, ($intern->hours_rendered / $targetHours) * 100)) : 0;
                @endphp

                <div x-show="searchQuery === '' || '{{ addslashes(strtolower($intern->first_name . ' ' . $intern->last_name)) }}'.includes(searchQuery.toLowerCase()) || '{{ addslashes(strtolower($intern->student_id)) }}'.includes(searchQuery.toLowerCase())"
                     class="bg-white dark:bg-gray-900 rounded-[25px] md:rounded-[30px] border border-gray-100 dark:border-gray-800 p-4 md:p-6 shadow-sm hover:shadow-md hover:border-[#0B5694]/20 transition-all duration-300 flex flex-col justify-between group">
                    {{-- Header: Avatar & Profile --}}
                    <div class="flex items-start gap-4">
                        <x-avatar :user="$intern" size="12" border="0" textSize="sm" />
                        <div class="min-w-0 flex-1">
                            <p class="font-black text-gray-800 dark:text-gray-100 text-sm uppercase truncate leading-tight mb-0.5">
                                {{ $intern->first_name }} {{ $intern->last_name }}
                            </p>
                            <p class="text-[9px] text-gray-400 dark:text-gray-500 font-bold uppercase tracking-wider">{{ $intern->school }}</p>
                        </div>
                    </div>

                    {{-- Live Status Indicator --}}
                    <div class="my-4 pt-3 border-t border-gray-50 dark:border-gray-800/60">
                        <div class="flex items-center gap-2">
                            @if($todayStatus === 'active')
                                <span class="relative flex h-2.5 w-2.5">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                                </span>
                                <span class="text-[10px] font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-widest">{{ $todayDetails }}</span>
                            @elseif($todayStatus === 'completed')
                                <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                                <span class="text-[10px] font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-widest">{{ $todayDetails }}</span>
                            @elseif($todayStatus === 'absent')
                                <span class="h-2.5 w-2.5 rounded-full bg-rose-500"></span>
                                <span class="text-[10px] font-black text-rose-600 dark:text-rose-400 uppercase tracking-widest">{{ $todayDetails }}</span>
                            @elseif($todayStatus === 'holiday' || $todayStatus === 'suspended')
                                <span class="h-2.5 w-2.5 rounded-full bg-purple-500"></span>
                                <span class="text-[10px] font-black text-purple-600 dark:text-purple-400 uppercase tracking-widest">{{ $todayDetails }}</span>
                            @else
                                <span class="h-2.5 w-2.5 rounded-full bg-gray-300 dark:bg-gray-600"></span>
                                <span class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">{{ $todayDetails }}</span>
                            @endif
                        </div>
                    </div>

                    {{-- Hours progress indicator --}}
                    <div class="bg-gray-50/50 dark:bg-gray-800/20 p-4 rounded-2xl border border-gray-50/50 dark:border-gray-800/30 flex items-center justify-between mb-4">
                        <div class="text-left">
                            <p class="text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Logged Hours</p>
                            <p class="text-sm font-black text-gray-800 dark:text-gray-100 tracking-tight mt-0.5">{{ number_format($intern->hours_rendered) }} / {{ $targetHours }} hrs</p>
                        </div>
                        <div class="relative w-14 h-14 flex items-center justify-center shrink-0">
                            <svg class="w-full h-full transform -rotate-90">
                                <circle cx="28" cy="28" r="22" class="text-gray-200 dark:text-gray-800" stroke-width="4" stroke="currentColor" fill="transparent" />
                                <circle cx="28" cy="28" r="22" class="text-[#0B5694] dark:text-blue-500" stroke-width="4" stroke="currentColor" fill="transparent"
                                        stroke-dasharray="138.23"
                                        stroke-dashoffset="{{ 138.23 - (138.23 * $pct) / 100 }}" />
                            </svg>
                            <span class="absolute text-[10px] font-black text-gray-800 dark:text-gray-100 tracking-tighter">{{ round($pct) }}%</span>
                        </div>
                    </div>

                    {{-- Card Footer --}}
                    <div class="flex items-center justify-between pt-3 border-t border-gray-50 dark:border-gray-800/60">
                        <div class="flex gap-2">
                            <span class="px-2.5 py-1 bg-orange-50 dark:bg-orange-950/20 border border-orange-100/30 dark:border-orange-900/30 text-orange-500 text-[9px] font-black rounded-lg uppercase tracking-widest shrink-0">
                                {{ $lates }} Lates
                            </span>
                            <span class="px-2.5 py-1 bg-rose-50 dark:bg-rose-950/20 border border-rose-100/30 dark:border-rose-900/30 text-rose-500 text-[9px] font-black rounded-lg uppercase tracking-widest shrink-0">
                                {{ $absents }} Absents
                            </span>
                        </div>
                        
                        <button @click="selectedInternId = '{{ $intern->user_id }}'; showLogsDrawer = true; drawerTab = 'all'" class="px-4 py-2 bg-[#0B5694] dark:bg-blue-600 hover:bg-[#084577] dark:hover:bg-blue-700 text-white rounded-xl font-black text-[9px] uppercase tracking-widest shadow-sm transition-all active:scale-95">
                            View Logs
                        </button>
                    </div>

                </div>
            @empty
                <div class="col-span-full py-20 bg-white dark:bg-gray-900 rounded-[35px] border border-gray-100 dark:border-gray-800 text-center">
                    <x-empty-state title="No active interns" compact="true" />
                </div>
            @endforelse
        </div>

        {{-- SLIDE-OVER ATTENDANCE LOGS DRAWER --}}
        <div x-show="showLogsDrawer" 
             class="fixed inset-0 z-[150] overflow-hidden" 
             role="dialog" 
             aria-modal="true" 
             x-cloak>
            
            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity" 
                 x-show="showLogsDrawer"
                 x-transition:enter="ease-in-out duration-500" 
                 x-transition:enter-start="opacity-0" 
                 x-transition:enter-end="opacity-100" 
                 x-transition:leave="ease-in-out duration-500" 
                 x-transition:leave-start="opacity-100" 
                 x-transition:leave-end="opacity-0" 
                 @click="showLogsDrawer = false"></div>

            <div class="absolute inset-y-0 right-0 pl-10 max-w-full flex">
                <div class="w-screen sm:max-w-xl md:max-w-2xl" 
                     x-show="showLogsDrawer"
                     x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700" 
                     x-transition:enter-start="translate-x-full" 
                     x-transition:enter-end="translate-x-0" 
                     x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700" 
                     x-transition:leave-start="translate-x-0" 
                     x-transition:leave-end="translate-x-full">
                    
                    <div class="h-full flex flex-col bg-white dark:bg-gray-900 shadow-2xl border-l border-gray-100 dark:border-gray-800">
                        {{-- Drawer Header --}}
                        <div class="px-4 sm:px-6 py-4 sm:py-6 bg-gray-50 dark:bg-gray-800/40 border-b border-gray-100 dark:border-gray-800/80 flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 flex items-center justify-center bg-blue-50 dark:bg-blue-900/20 text-[#0B5694] dark:text-blue-400 rounded-xl shrink-0">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                </div>
                                <div>
                                    <h2 class="text-base font-black text-gray-800 dark:text-gray-100 uppercase tracking-tight">Time Logs Registry</h2>
                                    <p class="text-[10px] text-gray-400 dark:text-gray-500 font-bold uppercase tracking-widest mt-0.5">Historical and real-time attendance</p>
                                </div>
                            </div>
                            <button @click="showLogsDrawer = false" class="p-2 text-gray-400 hover:text-red-500 dark:hover:text-red-400 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 transition-all active:scale-95 inline-flex items-center justify-center">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            </button>
                        </div>

                        {{-- Drawer Tabs selector --}}
                        <div class="px-4 sm:px-6 py-3 sm:py-4 bg-gray-50/50 dark:bg-gray-800/20 border-b border-gray-100 dark:border-gray-800 flex justify-between items-center gap-4">
                            <span class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Filters</span>
                            
                            <div class="flex items-center bg-gray-100 dark:bg-gray-800 p-0.5 rounded-xl border border-gray-100 dark:border-gray-700/50">
                                <button @click="drawerTab = 'all'" :class="drawerTab === 'all' ? 'bg-white dark:bg-gray-700 text-[#0B5694] dark:text-blue-400 shadow-sm' : 'text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300'" class="px-3 py-1.5 rounded-lg text-[9px] font-black uppercase tracking-widest transition-all">All</button>
                                <button @click="drawerTab = 'active'" :class="drawerTab === 'active' ? 'bg-white dark:bg-gray-700 text-[#0B5694] dark:text-blue-400 shadow-sm' : 'text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300'" class="px-3 py-1.5 rounded-lg text-[9px] font-black uppercase tracking-widest transition-all">Active</button>
                                <button @click="drawerTab = 'exceptions'" :class="drawerTab === 'exceptions' ? 'bg-white dark:bg-gray-700 text-[#0B5694] dark:text-blue-400 shadow-sm' : 'text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300'" class="px-3 py-1.5 rounded-lg text-[9px] font-black uppercase tracking-widest transition-all">Exceptions</button>
                                <button @click="drawerTab = 'overrides'" :class="drawerTab === 'overrides' ? 'bg-white dark:bg-gray-700 text-[#0B5694] dark:text-blue-400 shadow-sm' : 'text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300'" class="px-3 py-1.5 rounded-lg text-[9px] font-black uppercase tracking-widest transition-all">Off-days</button>
                            </div>
                        </div>

                        {{-- Drawer Body --}}
                        <div class="flex-1 overflow-y-auto custom-scrollbar p-4 sm:p-6">
                            @foreach($groupedLogs as $internId => $internLogs)
                                <div x-show="selectedInternId == '{{ $internId }}'" class="space-y-6 text-left" x-cloak>
                                    
                                    {{-- Intern Mini Profile Card --}}
                                    @php
                                        $firstLog = $internLogs->first();
                                        $selectedIntern = $firstLog ? $firstLog['intern'] : null;
                                    @endphp
                                    @if($selectedIntern)
                                        <div class="bg-gray-50/50 dark:bg-gray-800/40 p-4 rounded-2xl border border-gray-50/50 dark:border-gray-800 flex items-center gap-4 mb-6">
                                            <x-avatar :user="$selectedIntern" size="10" border="0" textSize="xs" />
                                            <div>
                                                <h4 class="text-sm font-black text-gray-800 dark:text-gray-100 uppercase tracking-tight leading-tight">{{ $selectedIntern->first_name }} {{ $selectedIntern->last_name }}</h4>
                                                <p class="text-[9px] text-gray-400 dark:text-gray-500 font-bold uppercase tracking-widest mt-0.5">{{ $selectedIntern->school }} • {{ $selectedIntern->student_id }}</p>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="border border-gray-100 dark:border-gray-800 rounded-2xl overflow-hidden shadow-inner">
                                        {{-- Mobile: Card List --}}
                                        <div class="lg:hidden divide-y divide-gray-50 dark:divide-gray-800">
                                            @forelse($internLogs as $log)
                                                <div x-show="drawerTab === 'all' || 
                                                            (drawerTab === 'active' && {{ $log['type'] === 'actual' && empty($log['clock_out']) && $log['clock_in'] && $log['clock_in']->isToday() ? 'true' : 'false' }}) || 
                                                            (drawerTab === 'exceptions' && ('{{ $log['status'] }}' === 'late' || '{{ $log['status'] }}' === 'absent')) || 
                                                            (drawerTab === 'overrides' && ('{{ $log['status'] }}' === 'holiday' || '{{ $log['status'] }}' === 'suspension'))"
                                                     class="p-4 space-y-2">
                                                    <div class="flex items-center justify-between">
                                                        <span class="font-bold text-gray-900 dark:text-gray-100 text-sm">{{ $log['date']->format('M d, Y') }}</span>
                                                        <span>
                                                            @if($log['type'] === 'actual')
                                                                @if(!$log['clock_out'])
                                                                    <span class="px-2.5 py-1 bg-yellow-50 dark:bg-yellow-900/20 text-yellow-600 dark:text-yellow-400 text-[10px] font-bold rounded-full border border-yellow-100/30 dark:border-yellow-900/30 animate-pulse">Active</span>
                                                                @elseif($log['status'] === 'late')
                                                                    <span class="px-2.5 py-1 bg-orange-50 dark:bg-orange-950/20 text-orange-500 dark:text-orange-400 text-[10px] font-bold rounded-full border border-orange-100/30 dark:border-orange-900/30">Late</span>
                                                                @else
                                                                    <span class="px-2.5 py-1 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 text-[10px] font-bold rounded-full border border-emerald-100/30 dark:border-emerald-900/30">Completed</span>
                                                                @endif
                                                            @else
                                                                @if($log['status'] === 'holiday')
                                                                    <span class="px-2.5 py-1 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 text-[10px] font-bold rounded-full border border-emerald-100/30 dark:border-emerald-900/30">Holiday</span>
                                                                @elseif($log['status'] === 'suspension')
                                                                    <span class="px-2.5 py-1 bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400 text-[10px] font-bold rounded-full border border-purple-100/30 dark:border-purple-900/30 animate-pulse">Suspended</span>
                                                                @elseif($log['status'] === 'absent')
                                                                    <span class="px-2.5 py-1 bg-rose-50 dark:bg-rose-950/20 text-rose-600 dark:text-rose-400 text-[10px] font-bold rounded-full border border-rose-100/30 dark:border-rose-900/30">Absent</span>
                                                                @endif
                                                            @endif
                                                        </span>
                                                    </div>
                                                    <div class="grid grid-cols-3 gap-2 text-xs">
                                                        <div>
                                                            <span class="text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest block">In</span>
                                                            <span class="font-medium text-gray-700 dark:text-gray-300">{{ $log['clock_in'] ? $log['clock_in']->format('h:i A') : '---' }}</span>
                                                        </div>
                                                        <div>
                                                            <span class="text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest block">Out</span>
                                                            <span class="font-medium text-gray-700 dark:text-gray-300">{{ $log['clock_out'] ? $log['clock_out']->format('h:i A') : '---' }}</span>
                                                        </div>
                                                        <div>
                                                            <span class="text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest block">Hrs</span>
                                                            <span class="font-bold text-gray-900 dark:text-gray-100">
                                                                @if($log['type'] === 'actual')
                                                                    @if($log['clock_out'])
                                                                        {{ fmod($log['duration'], 1) == 0 ? (int)$log['duration'] : round($log['duration'], 2) }}
                                                                    @else
                                                                        <span class="text-blue-500 dark:text-blue-400 italic text-[11px] font-semibold animate-pulse">In Progress</span>
                                                                    @endif
                                                                @else
                                                                    0
                                                                @endif
                                                            </span>
                                                        </div>
                                                    </div>
                                                    @if($log['remarks'])
                                                        <p class="text-[10px] text-gray-400 dark:text-gray-500 font-bold uppercase tracking-tight italic">{{ $log['remarks'] }}</p>
                                                    @endif
                                                    @if($log['type'] === 'actual' && !empty($log['record']->latitude) && !empty($log['record']->longitude))
                                                        <a href="https://www.google.com/maps/search/?api=1&query={{ $log['record']->latitude }},{{ $log['record']->longitude }}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-50 dark:bg-blue-900/20 text-[#0B5694] dark:text-blue-400 text-[9px] font-bold rounded-lg border border-blue-100/30 dark:border-blue-900/30 transition">
                                                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                                                            View Location
                                                        </a>
                                                    @endif
                                                </div>
                                            @empty
                                                <div class="py-8 text-center text-gray-400 italic">No logs found.</div>
                                            @endforelse
                                        </div>

                                        {{-- Desktop: Table --}}
                                        <div class="hidden lg:block overflow-x-auto custom-scrollbar">
                                            <table class="w-full text-left text-xs">
                                                <thead class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-100 dark:border-gray-700 text-gray-400 dark:text-gray-500 uppercase text-[9px] font-black tracking-widest">
                                                    <tr>
                                                        <th class="py-3 md:py-4 px-3 md:px-5">Date</th>
                                                        <th class="py-4 px-3 text-center">In</th>
                                                        <th class="py-4 px-3 text-center">Out</th>
                                                        <th class="py-4 px-3 text-center">Hrs</th>
                                                        <th class="py-3 md:py-4 px-3 md:px-5 text-center">Status</th>
                                                        <th class="py-3 md:py-4 px-3 md:px-5 text-left">Remarks</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-50 dark:divide-gray-800 text-gray-700 dark:text-gray-300 font-medium">
                                                    @forelse($internLogs as $log)
                                                        <tr x-show="drawerTab === 'all' || 
                                                                    (drawerTab === 'active' && {{ $log['type'] === 'actual' && empty($log['clock_out']) && $log['clock_in'] && $log['clock_in']->isToday() ? 'true' : 'false' }}) || 
                                                                    (drawerTab === 'exceptions' && ('{{ $log['status'] }}' === 'late' || '{{ $log['status'] }}' === 'absent')) || 
                                                                    (drawerTab === 'overrides' && ('{{ $log['status'] }}' === 'holiday' || '{{ $log['status'] }}' === 'suspension'))">
                                                            <td class="py-3 md:py-4 px-3 md:px-5 font-bold text-gray-900 dark:text-gray-100 whitespace-nowrap">{{ $log['date']->format('M d, Y') }}</td>
                                                            <td class="py-4 px-3 text-center whitespace-nowrap">{{ $log['clock_in'] ? $log['clock_in']->format('h:i A') : '---' }}</td>
                                                            <td class="py-4 px-3 text-center whitespace-nowrap">{{ $log['clock_out'] ? $log['clock_out']->format('h:i A') : '---' }}</td>
                                                            <td class="py-4 px-3 text-center font-bold whitespace-nowrap text-gray-900 dark:text-gray-100">
                                                                @if($log['type'] === 'actual')
                                                                    @if($log['clock_out'])
                                                                        {{ fmod($log['duration'], 1) == 0 ? (int)$log['duration'] : round($log['duration'], 2) }}
                                                                    @else
                                                                        <span class="text-blue-500 dark:text-blue-400 italic text-[11px] font-semibold animate-pulse">In Progress</span>
                                                                    @endif
                                                                @else
                                                                    0
                                                                @endif
                                                            </td>
                                                            <td class="py-3 md:py-4 px-3 md:px-5 text-center whitespace-nowrap flex items-center justify-center gap-1.5">
                                                                @if($log['type'] === 'actual')
                                                                    @if(!$log['clock_out'])
                                                                        <span class="px-3 py-1.5 bg-yellow-50 dark:bg-yellow-900/20 text-yellow-600 dark:text-yellow-400 text-[10px] font-bold rounded-full border border-yellow-100/30 dark:border-yellow-900/30 tracking-wider animate-pulse">Active</span>
                                                                    @elseif($log['status'] === 'late')
                                                                        <span class="px-3 py-1.5 bg-orange-50 dark:bg-orange-950/20 text-orange-500 dark:text-orange-400 text-[10px] font-bold rounded-full border border-orange-100/30 dark:border-orange-900/30 tracking-wider" title="{{ $log['remarks'] }}">Late</span>
                                                                    @else
                                                                        <span class="px-3 py-1.5 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 text-[10px] font-bold rounded-full border border-emerald-100/30 dark:border-emerald-900/30 tracking-wider" title="{{ $log['remarks'] }}">Completed</span>
                                                                    @endif

                                                                    @if(!empty($log['record']->latitude) && !empty($log['record']->longitude))
                                                                        <a href="https://www.google.com/maps/search/?api=1&query={{ $log['record']->latitude }},{{ $log['record']->longitude }}" 
                                                                           target="_blank" 
                                                                           class="inline-flex items-center justify-center w-6 h-6 bg-blue-50 dark:bg-blue-900/20 hover:bg-[#0B5694] hover:text-white text-[#0B5694] dark:text-blue-400 rounded-lg border border-blue-100 dark:border-blue-900/30 transition active:scale-95 shrink-0" 
                                                                           title="Verify Location ({{ $log['record']->latitude }}, {{ $log['record']->longitude }})">
                                                                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                                                                        </a>
                                                                    @endif
                                                                @else
                                                                    @if($log['status'] === 'holiday')
                                                                        <span class="px-3 py-1.5 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 text-[10px] font-bold rounded-full border border-emerald-100/30 dark:border-emerald-900/30 tracking-wider" title="{{ $log['remarks'] }}">Holiday</span>
                                                                    @elseif($log['status'] === 'suspension')
                                                                        <span class="px-3 py-1.5 bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400 text-[10px] font-bold rounded-full border border-purple-100/30 dark:border-purple-900/30 tracking-wider shadow-sm animate-pulse" title="{{ $log['remarks'] }}">Suspended</span>
                                                                    @elseif($log['status'] === 'absent')
                                                                        <span class="px-3 py-1.5 bg-rose-50 dark:bg-rose-950/20 text-rose-600 dark:text-rose-400 text-[10px] font-bold rounded-full border border-rose-100/30 dark:border-rose-900/30 tracking-wider" title="No check-in">Absent</span>
                                                                    @endif
                                                                @endif
                                                            </td>
                                                            <td class="py-3 md:py-4 px-3 md:px-5 text-left">
                                                                <span class="text-[10px] text-gray-400 dark:text-gray-500 font-bold uppercase tracking-tight italic line-clamp-1 max-w-[120px]" title="{{ $log['remarks'] }}">
                                                                    {{ $log['remarks'] ?? '---' }}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr><td colspan="5" class="py-8 text-center text-gray-400 italic">No logs found.</td></tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                </div>
                            @endforeach
                        </div>

                        {{-- Drawer Footer --}}
                        <div class="px-6 py-5 bg-gray-50 dark:bg-gray-800/40 border-t border-gray-100 dark:border-gray-800 text-right">
                            <button @click="showLogsDrawer = false" class="px-6 py-2.5 bg-white dark:bg-gray-800 text-gray-400 dark:text-gray-500 border border-gray-200 dark:border-gray-700 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-700 transition active:scale-95">
                                Close Logs
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-page-layout>
@endsection