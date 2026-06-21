@extends('layouts.app')

@section('title', 'Attendance')

@section('content')
<x-page-layout 
    portalType="Intern" 
    userName="{{ Auth::user()->internDetails->first_name }}" 
    userRole="{{ Auth::user()->internDetails->office->name }}"
>
    <div x-data="attendanceManager('{{ route('intern.attendance.check-status') }}', {{ $current_record ? 'true' : 'false' }}, {{ $completed_today ? 'true' : 'false' }})" class="flex flex-col flex-1">
        
        {{-- Header --}}
        <x-page-header 
            title="Attendance" 
            subtitle="Log your daily shifts and track your training progress." 
        />

        {{-- SKELETON LOADING VIEW --}}
        <div x-show="loading" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="flex flex-col flex-1">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-10 flex-1">
                <div class="lg:col-span-8 flex flex-col h-full">
                    <x-card-container class="flex-1 flex flex-col items-center justify-center" padding="p-12">
                        <div class="w-64 h-64 bg-gray-100 dark:bg-gray-800 rounded-[70px] animate-shimmer mb-10"></div>
                        <x-skeleton type="text" class="w-1/3" />
                    </x-card-container>
                </div>
                <div class="lg:col-span-4 flex flex-col gap-8 h-full">
                    <x-card-container class="flex-1" padding="p-8">
                        <x-skeleton type="list" rows="5" />
                    </x-card-container>
                    <x-skeleton type="metric" class="h-32" />
                    <x-skeleton type="metric" class="h-32" />
                </div>
            </div>
        </div>

        {{-- ACTUAL CONTENT VIEW --}}
        <div x-show="!loading" x-cloak x-transition:enter="transition ease-out duration-500 delay-200" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="flex flex-col flex-1">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-10 flex-1">
            {{-- PRIMARY ACTION: QR HERO --}}
            <div class="lg:col-span-8 flex flex-col h-full">
                <x-card-container class="flex-1 flex flex-col items-center justify-center text-center relative overflow-hidden" padding="px-8 pb-8 pt-24 lg:px-12 lg:pb-12 lg:pt-32">
                    {{-- Decorative Background --}}
                    <div class="absolute top-0 right-0 w-64 h-64 bg-blue-50/20 dark:bg-blue-900/5 rounded-bl-full -z-0"></div>
                    
                    {{-- Date Anchor Badge --}}
                    <div class="absolute top-6 lg:top-8 left-1/2 -translate-x-1/2 z-10">
                        <span class="px-8 py-3.5 {{ $is_enabled ? 'bg-blue-50 dark:bg-blue-900/20 text-[#0B5694] dark:text-blue-400 border-blue-100 dark:border-blue-800' : ($completed_today ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 border-emerald-100 dark:border-emerald-800' : (($date_status && $date_status['blocked']) ? 'bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 border-amber-100 dark:border-amber-800' : 'bg-orange-50 dark:bg-orange-900/20 text-orange-500 border-orange-100 dark:border-orange-800')) }} rounded-full text-[12px] font-bold uppercase tracking-[0.4em] border transition-all shadow-sm whitespace-nowrap">
                            {{ now()->format('l, F d, Y') }}
                        </span>
                    </div>

                    <div class="relative z-10 flex flex-col items-center justify-center flex-1 w-full">
                        @if($is_enabled)
                            <div class="bg-white p-12 border-8 border-gray-50 dark:border-gray-800/50 rounded-[70px] shadow-2xl dark:shadow-none transition-all hover:scale-[1.02] relative group max-w-[480px] aspect-square flex items-center justify-center ring-1 ring-black/[0.03] dark:ring-white/[0.03]">
                                <div id="qr-code-container" class="w-full h-full flex items-center justify-center">
                                    {!! QrCode::size(380)->generate($qrPayload) !!}
                                </div>
                                <div class="absolute inset-0 border-4 border-[#0B5694] rounded-[70px] opacity-0 group-hover:opacity-10 transition-opacity"></div>
                                
                                {{-- Pulsing indicator --}}
                                <div class="absolute -top-3 -right-3 flex h-8 w-8">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-8 w-8 bg-blue-500 border-4 border-white dark:border-gray-900"></span>
                                </div>
                                </div>
                                <p class="mt-12 text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em]">Ready for Verification</p>
                                @elseif($completed_today)
                                <div class="flex flex-col items-center animate-in fade-in zoom-in duration-500">
                                <div class="w-36 h-36 bg-emerald-50 dark:bg-emerald-900/20 rounded-full flex items-center justify-center text-emerald-500 mb-10 border-4 border-emerald-100 dark:border-emerald-800 shadow-inner">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="72" height="72" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                </div>
                                <h3 class="text-4xl font-extrabold text-gray-800 dark:text-gray-100 uppercase tracking-normal">Shift Complete</h3>
                                <p class="text-[11px] text-gray-400 dark:text-gray-500 max-w-sm leading-relaxed mt-6 font-bold uppercase tracking-[0.2em] italic">Great work! You've finished your shift for today.</p>
                                </div>
                                @elseif($date_status && $date_status['blocked'])
                                <div class="flex flex-col items-center">
                                <div class="w-36 h-36 bg-amber-50 dark:bg-amber-900/20 rounded-full flex items-center justify-center text-amber-500 mb-10 border-4 border-amber-100 dark:border-amber-800 shadow-inner">
                                    @if($date_status['type'] === 'holiday')
                                        <svg xmlns="http://www.w3.org/2000/svg" width="72" height="72" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/><path d="M8 14h.01"/><path d="M12 14h.01"/><path d="M16 14h.01"/><path d="M8 18h.01"/><path d="M12 18h.01"/><path d="M16 18h.01"/></svg>
                                    @elseif($date_status['type'] === 'suspension')
                                        <svg xmlns="http://www.w3.org/2000/svg" width="72" height="72" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" x2="12" y1="9" y2="13"/><line x1="12" x2="12" y1="17" y2="17.01"/></svg>
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" width="72" height="72" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" x2="16" y1="2" x2="16" y2="6"/><line x1="8" x2="8" y1="2" x2="8" y2="6"/><line x1="3" x2="21" y1="10" x2="21" y2="10"/></svg>
                                    @endif
                                </div>
                                <h3 class="text-4xl font-extrabold text-gray-800 dark:text-gray-100 uppercase tracking-normal">
                                    @if($date_status['type'] === 'holiday')
                                        Official Holiday
                                    @elseif($date_status['type'] === 'suspension')
                                        Work Suspended
                                    @else
                                        Non-Working Day
                                    @endif
                                </h3>
                                <p class="text-[11px] text-gray-400 dark:text-gray-500 max-w-sm mt-6 font-bold uppercase tracking-[0.2em] italic leading-relaxed text-center px-6">
                                    {{ $date_status['description'] }}
                                </p>
                                </div>
                                @else
                                <div class="flex flex-col items-center">
                                <div class="w-36 h-36 bg-orange-50 dark:bg-orange-900/20 rounded-full flex items-center justify-center text-orange-500 mb-10 border-4 border-orange-100 dark:border-orange-800 shadow-inner">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="72" height="72" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12" y1="16" y2="16.01"/></svg>
                                </div>
                                <h3 class="text-4xl font-extrabold text-gray-800 dark:text-gray-100 uppercase tracking-normal">Scanner Security</h3>
                                <p class="text-[11px] text-gray-400 dark:text-gray-500 max-w-sm mt-6 font-bold uppercase tracking-[0.2em] italic leading-relaxed px-6">Scanning is temporarily locked for security. You can scan for Time-Out at:</p>
                                <div class="mt-10 px-12 py-5 bg-gray-50 dark:bg-gray-800/50 rounded-[40px] border border-gray-100 dark:border-gray-700 shadow-sm ring-1 ring-black/[0.02]">
                                    <span class="text-[#0B5694] dark:text-blue-400 font-extrabold text-5xl tracking-normal">{{ $next_available_scan ? $next_available_scan->format('h:i A') : '---' }}</span>
                                </div>
                                </div>
                                @endif
                    </div>
                </x-card-container>
            </div>

            {{-- INFORMATION & STATS (SIDEBAR) --}}
            <div class="lg:col-span-4 flex flex-col gap-8 h-full">
                
                {{-- ACTIVITY LOG --}}
                <x-card-container title="Recent Shifts" class="flex-1 flex flex-col min-h-0" padding="p-0">
                    <x-slot name="headerAction">
                        <div class="flex items-center gap-2">
                            <button @click="$dispatch('open-modal', 'all-attendance-logs')" class="text-[9px] font-bold text-[#0B5694] dark:text-blue-400 uppercase tracking-wider bg-blue-50/50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/40 px-3 py-1 rounded-full border border-blue-100/50 dark:border-blue-800/50 transition-all">
                                View All
                            </button>
                            <span class="text-[9px] font-bold text-gray-400 uppercase tracking-wider bg-gray-50 dark:bg-gray-800 px-3 py-1 rounded-full border border-gray-100 dark:border-gray-800">Recent Activity</span>
                        </div>
                    </x-slot>
                    
                    <div class="overflow-y-auto custom-scrollbar p-6 space-y-4 h-[310px]">
                        @forelse($attendanceHistory as $log)
                            <div class="flex items-center justify-between p-5 bg-gray-50/30 dark:bg-gray-800/20 rounded-[30px] border border-gray-50 dark:border-gray-700 transition-all hover:bg-white dark:hover:bg-gray-800/50 hover:shadow-md shrink-0 group">
                                <div class="text-left">
                                    <p class="text-[11px] font-bold text-gray-700 dark:text-gray-200 uppercase tracking-tight">{{ $log['date']->format('M d, Y') }}</p>
                                    <p class="text-[9px] text-gray-400 font-bold uppercase mt-1.5 flex items-center gap-1.5">
                                        @if($log['type'] === 'actual')
                                            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="text-gray-300"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                            {{ $log['clock_in']->format('h:i A') }} - {{ $log['clock_out'] ? $log['clock_out']->format('h:i A') : 'Active' }}
                                        @else
                                            {{ $log['remarks'] }}
                                        @endif
                                    </p>
                                </div>
                                <div class="flex items-center gap-3">
                                    {{-- Status Badge --}}
                                    @php
                                        $statusClass = 'text-gray-400 bg-gray-50 dark:bg-gray-800 border-gray-100 dark:border-gray-700';
                                        $statusLabel = '---';

                                        if ($log['status'] === 'late') {
                                            $statusLabel = 'Late';
                                            $statusClass = 'text-orange-500 bg-orange-50 dark:bg-orange-950/30 border-orange-100 dark:border-orange-900/50';
                                        } elseif ($log['status'] === 'absent') {
                                            $statusLabel = 'Absent';
                                            $statusClass = 'text-rose-500 bg-rose-50 dark:bg-rose-950/30 border-rose-100 dark:border-rose-900/50';
                                        } elseif ($log['status'] === 'excused') {
                                            $statusLabel = 'Excused';
                                            $statusClass = 'text-blue-500 bg-blue-50 dark:bg-blue-950/30 border-blue-100 dark:border-blue-900/50';
                                        } elseif ($log['status'] === 'holiday') {
                                            $statusLabel = 'Holiday';
                                            $statusClass = 'text-emerald-500 bg-emerald-50 dark:bg-emerald-950/30 border-emerald-100 dark:border-emerald-800/50';
                                        } elseif ($log['status'] === 'suspension') {
                                            $statusLabel = 'Suspended';
                                            $statusClass = 'text-purple-500 bg-purple-50 dark:bg-purple-950/30 border-purple-100 dark:border-purple-800/50';
                                        } elseif ($log['type'] === 'actual') {
                                            if (!$log['clock_out']) {
                                                $statusLabel = 'Active';
                                                $statusClass = 'text-blue-500 bg-blue-50 dark:bg-blue-950/30 border-blue-100 dark:border-blue-900/50 animate-pulse';
                                            } else {
                                                $statusLabel = 'On-Time';
                                                $statusClass = 'text-emerald-500 bg-emerald-50 dark:bg-emerald-950/30 border-emerald-100 dark:border-emerald-900/50';
                                            }
                                        }
                                    @endphp

                                    <span class="px-3 py-1 border rounded-full text-[9px] font-bold uppercase tracking-wider {{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="h-full flex flex-col items-center justify-center opacity-50">
                                <x-empty-state title="First session pending" compact="true" />
                            </div>
                        @endforelse
                    </div>
                </x-card-container>

                {{-- QUICK STATS STACK --}}
                <div class="flex flex-col gap-6 shrink-0">
                    @if($schoolSchedule && $schoolSchedule->start_date)
                    <x-card-container padding="p-8" class="rounded-[40px] shadow-sm bg-blue-50/50 dark:bg-blue-900/10 border-blue-100/50 dark:border-blue-800/50">
                        <h4 class="text-[11px] font-black text-[#0B5694] dark:text-blue-400 uppercase tracking-[0.2em] mb-4">Program Timeline</h4>
                        <div class="space-y-4 text-left">
                            <div>
                                <p class="text-[9px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1">Official Start</p>
                                <p class="text-sm font-black text-gray-800 dark:text-gray-100 uppercase">{{ $schoolSchedule->start_date->format('F d, Y') }}</p>
                            </div>
                            @if($schoolSchedule->target_end_date)
                            <div>
                                <p class="text-[9px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1">Target Completion</p>
                                <p class="text-sm font-black text-gray-800 dark:text-gray-100 uppercase">{{ $schoolSchedule->target_end_date->format('F d, Y') }}</p>
                            </div>
                            @endif
                        </div>
                    </x-card-container>
                    @endif

                    <x-stat-card 
                        title="Total Hours Earned" 
                        :value="(int)$rendered" 
                        :subtitle="'/ ' . (int)$totalRequired . ' Target Hours'"
                        padding="p-8"
                        class="rounded-[40px] shadow-sm"
                    />

                    <x-card-container padding="p-8" class="rounded-[40px] shadow-sm">
                        <div class="flex justify-between items-center mb-6">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Overall Completion</p>
                            <span class="text-[12px] font-bold text-[#0B5694] dark:text-blue-400">{{ (int)$percentage }}%</span>
                        </div>
                        <div x-ignore class="w-full bg-gray-50 dark:bg-gray-800 rounded-full h-4 overflow-hidden shadow-inner border border-gray-100 dark:border-gray-700">
                            <div class="h-full rounded-full shadow-lg transition-all duration-1000 ease-out" 
                                 style="width: {{ $percentage }}% !important; background: linear-gradient(to right, #10b981, #34d399) !important;"></div>
                        </div>
                    </x-card-container>
                </div>

            </div>
        </div>
    </div>

        {{-- ALL LOGS MODAL --}}
        <x-modal name="all-attendance-logs" maxWidth="4xl">
            <div class="p-0 flex flex-col h-[80vh] overflow-hidden">
                {{-- Modal Header --}}
                <div class="px-10 py-8 border-b border-gray-50 dark:border-gray-800 flex justify-between items-center shrink-0 bg-gray-50/50 dark:bg-gray-800/20">
                    <div>
                        <h2 class="text-2xl font-extrabold text-gray-800 dark:text-gray-100 uppercase tracking-normal">Shift History</h2>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-[0.2em] mt-1">Full record of your internship attendance</p>
                    </div>
                    <button @click="$dispatch('close-modal', 'all-attendance-logs')" class="p-3 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-all shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>

                {{-- Modal Body (Scrollable Table) --}}
                <div class="flex-1 overflow-y-auto custom-scrollbar p-10">
                    <x-table-container>
                        <table class="w-full text-left">
                            <thead class="bg-gray-50/50 dark:bg-gray-800/30">
                                <tr>
                                    <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100 dark:border-gray-800">Date</th>
                                    <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100 dark:border-gray-800">Schedule</th>
                                    <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100 dark:border-gray-800">Status</th>
                                    <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100 dark:border-gray-800">Hours</th>
                                    <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100 dark:border-gray-800 text-right">Remarks</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 dark:divide-gray-800/50">
                                @foreach($allAttendanceLogs as $log)
                                    <tr class="hover:bg-gray-50/30 dark:hover:bg-gray-800/10 transition-colors">
                                        <td class="px-6 py-5 whitespace-nowrap">
                                            <span class="text-[11px] font-bold text-gray-700 dark:text-gray-200 uppercase tracking-tight">{{ $log['date']->format('M d, Y') }}</span>
                                            <p class="text-[9px] text-gray-400 font-bold uppercase tracking-widest mt-0.5">{{ $log['date']->format('l') }}</p>
                                        </td>
                                        <td class="px-6 py-5 whitespace-nowrap">
                                            @if($log['type'] === 'actual')
                                                <span class="text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-tighter">
                                                    {{ $log['clock_in']->format('h:i A') }} - {{ $log['clock_out'] ? $log['clock_out']->format('h:i A') : 'Active' }}
                                                </span>
                                            @else
                                                <span class="text-[11px] font-bold text-gray-300 dark:text-gray-600 uppercase tracking-tighter italic">No Session</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-5 whitespace-nowrap">
                                            @php
                                                $status = $log['status'];
                                                if ($log['type'] === 'actual' && !$log['clock_out']) $status = 'active';

                                                $statusConfig = match($status) {
                                                    'late' => ['label' => 'Late', 'class' => 'text-orange-500 bg-orange-50 dark:bg-orange-950/30 border-orange-100 dark:border-orange-900/50'],
                                                    'absent' => ['label' => 'Absent', 'class' => 'text-rose-500 bg-rose-50 dark:bg-rose-950/30 border-rose-100 dark:border-rose-900/50'],
                                                    'excused' => ['label' => 'Excused', 'class' => 'text-blue-500 bg-blue-50 dark:bg-blue-950/30 border-blue-100 dark:border-blue-900/50'],
                                                    'holiday' => ['label' => 'Holiday', 'class' => 'text-emerald-500 bg-emerald-50 dark:bg-emerald-950/30 border-emerald-100 dark:border-emerald-800/50'],
                                                    'suspension' => ['label' => 'Suspended', 'class' => 'text-purple-500 bg-purple-50 dark:bg-purple-950/30 border-purple-100 dark:border-purple-800/50'],
                                                    'active' => ['label' => 'Active', 'class' => 'text-blue-500 bg-blue-50 dark:bg-blue-950/30 border-blue-100 dark:border-blue-900/50 animate-pulse'],
                                                    default => ['label' => 'On-Time', 'class' => 'text-emerald-500 bg-emerald-50 dark:bg-emerald-950/30 border-emerald-100 dark:border-emerald-900/50'],
                                                };
                                            @endphp
                                            <span class="px-3 py-1 border rounded-full text-[9px] font-black uppercase tracking-[0.2em] {{ $statusConfig['class'] }}">
                                                {{ $statusConfig['label'] }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-5 whitespace-nowrap">
                                            @php
                                                $isCredited = in_array($log['status'], ['on_time', 'late']) && $log['clock_out'];
                                                $hourClass = $isCredited ? 'text-emerald-500' : ($status === 'active' ? 'text-blue-500 animate-pulse' : 'text-gray-300 dark:text-gray-600');
                                                $hourValue = $log['type'] === 'actual' ? ($log['clock_out'] ? (int)round($log['duration']) . 'H' : 'Active') : '0H';
                                                
                                                // Holidays/Suspensions/Excused are technically 0H but we show them clearly
                                                if (in_array($log['type'], ['holiday', 'suspension', 'excused'])) {
                                                    $hourValue = '0H';
                                                }
                                            @endphp
                                            <span class="text-[13px] font-black uppercase tracking-tighter {{ $hourClass }}">
                                                {{ $hourValue }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-5 text-right">
                                            <span class="text-[10px] text-gray-400 font-bold uppercase tracking-tight leading-relaxed line-clamp-2 max-w-[200px] ml-auto italic">
                                                {{ $log['remarks'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </x-table-container>
                </div>
            </div>
        </x-modal>
    </div>
</x-page-layout>
@endsection
