@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<x-page-layout 
    portalType="Intern" 
    userName="{{ Auth::user()->internDetails->first_name }}" 
    userRole="{{ Auth::user()->internDetails->office->name }}"
>
    <div x-data="internDashboardManager({{ Auth::id() }})" class="flex flex-col flex-1">
        {{-- HEADER --}}
        <x-page-header
            title="Welcome back,"
            highlight="{{ Auth::user()->internDetails->first_name }}"
            subtitle="Training Metrics & Office Overview"
        />

        {{-- SKELETON LOADING VIEW --}}
        @php
            $historySkeletonCount = $logs->isEmpty() ? 1 : min($logs->count(), 5);
            $taskSkeletonCount = $deadlines->isEmpty() ? 1 : min($deadlines->count(), 3);
        @endphp

        <div x-show="loading" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="flex flex-col flex-1">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-10">
                <x-card-container class="lg:col-span-8 h-[380px]" padding="p-12">
                    <x-skeleton type="text" class="w-1/4 mb-10" />
                    <div class="flex items-end justify-between mb-16">
                        <x-skeleton type="text" class="h-16 w-1/3" />
                        <x-skeleton type="text" class="h-10 w-1/4" />
                    </div>
                    <x-skeleton type="text" class="h-5 w-full" />
                </x-card-container>
                <div class="col-span-1 lg:col-span-4 flex flex-col gap-6">
                    <x-skeleton type="block" class="h-[180px]" />
                    <x-card-container class="h-[246px]" padding="p-0">
                        <x-skeleton type="list" :rows="2" class="p-6" />
                    </x-card-container>
                </div>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:h-[320px] mb-8">
                <div class="col-span-1 lg:col-span-7 h-full">
                    <x-card-container class="h-full" padding="p-8">
                        <x-skeleton type="list" :rows="$taskSkeletonCount" />
                    </x-card-container>
                </div>
                <div class="col-span-1 lg:col-span-5 h-full">
                    <x-card-container class="h-full" padding="p-8">
                        <x-skeleton type="list" rows="3" />
                    </x-card-container>
                </div>
            </div>
        </div>

        {{-- ACTUAL CONTENT VIEW --}}
        <div x-show="!loading" x-cloak 
             x-data="{
                requiredHours: {{ $totalRequired }},
                scheduleDays: @js($intern->schedule_assignment ?? ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']),
                startDate: '{{ ($intern->start_date ?? ($schoolSchedule->start_date ?? null)) ? ($intern->start_date ?? $schoolSchedule->start_date)->format('Y-m-d') : '' }}',
                endDate: '{{ $intern->end_date ? $intern->end_date->format('Y-m-d') : '' }}',
                renderedHours: {{ $rendered }},
                dailyHours: {{ $schoolSchedule ? $schoolSchedule->daily_hours ?? 8 : 8 }},
                holidays: @js($holidaysAndSuspensions ?? []),
                extensionModalOpen: false,
                extensionDate: '',
                extensionReason: '',
                
                get targetEndDate() {
                    if (!this.startDate || !this.requiredHours || this.requiredHours <= 0 || !this.scheduleDays.length) {
                        return 'N/A';
                    }
                    
                    let hoursLeft = this.requiredHours;
                    let currentDate = new Date(this.startDate);
                    
                    let safetyCounter = 0;
                    const maxIterations = 365 * 5; 
                    
                    while (hoursLeft > 0 && safetyCounter < maxIterations) {
                        safetyCounter++;
                        
                        const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                        const currentDayName = dayNames[currentDate.getDay()];
                        
                        const yyyy = currentDate.getFullYear();
                        const mm = String(currentDate.getMonth() + 1).padStart(2, '0');
                        const dd = String(currentDate.getDate()).padStart(2, '0');
                        const dateStr = `${yyyy}-${mm}-${dd}`;
                        
                        const isReportingDay = this.scheduleDays.includes(currentDayName);
                        const isNonWorkingDay = this.holidays.includes(dateStr);
                        
                        if (isReportingDay && !isNonWorkingDay) {
                            hoursLeft -= this.dailyHours;
                        }
                        
                        if (hoursLeft > 0) {
                            currentDate.setDate(currentDate.getDate() + 1);
                        }
                    }
                    
                    const options = { year: 'numeric', month: 'short', day: 'numeric' };
                    return currentDate.toLocaleDateString('en-US', options).toUpperCase();
                },

                get projectedEndDate() {
                    let hoursLeft = Math.max(0, this.requiredHours - this.renderedHours);
                    if (hoursLeft <= 0) {
                        return 'COMPLETED';
                    }
                    let currentDate = new Date();
                    currentDate.setHours(0, 0, 0, 0);
                    
                    let safetyCounter = 0;
                    const maxIterations = 365 * 5;
                    
                    while (hoursLeft > 0 && safetyCounter < maxIterations) {
                        safetyCounter++;
                        
                        const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                        const currentDayName = dayNames[currentDate.getDay()];
                        
                        const yyyy = currentDate.getFullYear();
                        const mm = String(currentDate.getMonth() + 1).padStart(2, '0');
                        const dd = String(currentDate.getDate()).padStart(2, '0');
                        const dateStr = `${yyyy}-${mm}-${dd}`;
                        
                        const isReportingDay = this.scheduleDays.includes(currentDayName);
                        const isNonWorkingDay = this.holidays.includes(dateStr);
                        
                        if (isReportingDay && !isNonWorkingDay) {
                            hoursLeft -= this.dailyHours;
                        }
                        
                        if (hoursLeft > 0) {
                            currentDate.setDate(currentDate.getDate() + 1);
                        }
                    }
                    
                    const options = { year: 'numeric', month: 'short', day: 'numeric' };
                    return currentDate.toLocaleDateString('en-US', options).toUpperCase();
                },

                get projectedEndDateISO() {
                    let hoursLeft = Math.max(0, this.requiredHours - this.renderedHours);
                    if (hoursLeft <= 0) return '';
                    let currentDate = new Date();
                    currentDate.setHours(0, 0, 0, 0);
                    let safetyCounter = 0;
                    const maxIterations = 365 * 5;
                    while (hoursLeft > 0 && safetyCounter < maxIterations) {
                        safetyCounter++;
                        const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                        const currentDayName = dayNames[currentDate.getDay()];
                        const yyyy = currentDate.getFullYear();
                        const mm = String(currentDate.getMonth() + 1).padStart(2, '0');
                        const dd = String(currentDate.getDate()).padStart(2, '0');
                        const dateStr = `${yyyy}-${mm}-${dd}`;
                        const isReportingDay = this.scheduleDays.includes(currentDayName);
                        const isNonWorkingDay = this.holidays.includes(dateStr);
                        if (isReportingDay && !isNonWorkingDay) {
                            hoursLeft -= this.dailyHours;
                        }
                        if (hoursLeft > 0) {
                            currentDate.setDate(currentDate.getDate() + 1);
                        }
                    }
                    const yyyy = currentDate.getFullYear();
                    const mm = String(currentDate.getMonth() + 1).padStart(2, '0');
                    const dd = String(currentDate.getDate()).padStart(2, '0');
                    return `${yyyy}-${mm}-${dd}`;
                },

                get isProjectedOverdue() {
                    let referenceEndDate = this.endDate || (this.targetEndDate !== 'N/A' ? this.targetEndDate : null);
                    if (!referenceEndDate) return false;
                    
                    let projDateStr = this.projectedEndDate;
                    if (projDateStr === 'N/A' || projDateStr === 'COMPLETED') return false;
                    
                    let projDate = new Date(projDateStr);
                    let officialEndDate = new Date(referenceEndDate);
                    
                    return projDate.getTime() > officialEndDate.getTime();
                },

                get daysUntilEndDate() {
                    let referenceEndDate = this.endDate || (this.targetEndDate !== 'N/A' ? this.targetEndDate : null);
                    if (!referenceEndDate) return 999;
                    let diffTime = new Date(referenceEndDate).getTime() - new Date().getTime();
                    return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                },

                get showExtensionBanner() {
                    let incomplete = this.requiredHours - this.renderedHours > 0;
                    return incomplete && (this.isProjectedOverdue || this.daysUntilEndDate <= 7);
                }
             }"
             x-transition:enter="transition ease-out duration-500 delay-200" 
             x-transition:enter-start="opacity-0 translate-y-4" 
             x-transition:enter-end="opacity-100 translate-y-0" 
             class="flex flex-col flex-1"
        >
            {{-- ALERT BANNER FOR EXTENSIONS --}}
            <template x-if="showExtensionBanner">
                <div class="mb-8 p-6 bg-rose-50 dark:bg-rose-950/20 border border-rose-100 dark:border-rose-900/50 rounded-3xl flex flex-col md:flex-row items-center justify-between gap-4 shadow-sm">
                    <div class="flex items-center gap-4 text-left">
                        <div class="w-12 h-12 bg-rose-100 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 rounded-2xl flex items-center justify-center flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-black text-rose-800 dark:text-rose-400 uppercase tracking-tight">Extension Required</h4>
                            <p class="text-xs text-rose-600 dark:text-rose-300 font-bold mt-1">
                                <span x-show="isProjectedOverdue">You are currently projected to complete on <span class="font-black" x-text="projectedEndDate"></span>, which is past your Expected End Date.</span>
                                <span x-show="!isProjectedOverdue">Your internship expected end date is in <span x-text="daysUntilEndDate"></span> days but you still have incomplete hours.</span>
                            </p>
                        </div>
                    </div>
                    @if($pendingExtension)
                        <span class="px-5 py-3 bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-400 text-xs font-black uppercase tracking-wider rounded-xl border border-amber-200 dark:border-amber-900/50">
                            Extension Pending Approval
                        </span>
                    @else
                        <button @click="extensionModalOpen = true; extensionDate = projectedEndDateISO;" class="px-6 py-3.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-black uppercase tracking-wider rounded-2xl transition active:scale-95 shadow-md shadow-rose-500/10 cursor-pointer">
                            Request Extension
                        </button>
                    @endif
                </div>
            </template>

            {{-- ROW 1: ENLARGED PROGRESS CARD --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-10">
            {{-- LARGE PROGRESS CARD --}}
            <x-card-container class="lg:col-span-8 h-full relative overflow-hidden group hover:shadow-blue-500/5" padding="p-8 md:p-12">
                <div class="absolute top-0 right-0 w-64 h-64 bg-blue-50/30 dark:bg-blue-900/5 rounded-bl-full -z-0 transition-transform group-hover:scale-110"></div>
                
                <div class="relative z-10 h-full flex flex-col justify-center">
                    <div>
                        <p class="text-[11px] font-bold text-[#0B5694] dark:text-blue-400 uppercase tracking-wider mb-14">Internship Progress ({{ $totalRequired }} Hours)</p>
                        
                        <div class="flex flex-col md:flex-row md:items-end justify-between gap-8 mb-16">
                            <div>
                                <h3 class="text-6xl md:text-7xl font-extrabold text-gray-900 dark:text-white tracking-normal leading-none">
                                    {{ round($rendered) }}<span class="text-2xl md:text-3xl text-gray-300 dark:text-gray-700 ml-4 font-bold tracking-wider">HRS</span>
                                </h3>
                                <p class="text-sm font-bold text-gray-400 dark:text-gray-500 mt-2 uppercase tracking-wider">Total Hours Rendered</p>
                            </div>
                            <div class="text-left md:text-right">
                                @php $remaining = max(0, $totalRequired - round($rendered)); @endphp
                                <h4 class="text-3xl font-extrabold text-[#0B5694] dark:text-blue-400 tracking-normal leading-none">
                                    {{ $remaining }}<span class="text-sm text-gray-400 ml-3 uppercase tracking-wider">To Go</span>
                                </h4>
                                <p class="text-[11px] font-bold text-emerald-500 dark:text-emerald-400 mt-2 uppercase tracking-wider">{{ round($percentage) }}% Complete</p>
                            </div>
                        </div>
                    </div>

                    {{-- PROGRESS BAR --}}
                    <div x-ignore class="w-full bg-gray-100 dark:bg-gray-850 h-5 rounded-full overflow-hidden border border-gray-200 dark:border-gray-700/50 shadow-inner">
                        <div class="h-full rounded-full" 
                             style="width: {{ round($percentage) }}% !important; background: linear-gradient(to right, #0B5694, #60a5fa) !important;">
                        </div>
                    </div>
                </div>
            </x-card-container>

            {{-- QUICK ATTENDANCE & HISTORY STACK --}}
            <div class="col-span-1 lg:col-span-4 flex flex-col gap-6">
                <a href="{{ route('intern.attendance') }}" class="flex-1 min-h-[180px] bg-[#0B5694] dark:bg-blue-600 rounded-[35px] p-8 text-white relative overflow-hidden group hover:scale-[1.02] active:scale-[0.98] transition-all shadow-md dark:shadow-none flex flex-col justify-center text-left">
                    <svg class="absolute -bottom-4 -right-4 w-32 h-32 text-white/10 group-hover:rotate-12 transition-transform" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path d="M12 4v1m6 1.4l-.8.7M20 12h-1M18 18.6l-.7-.8M12 20v-1M6.4 18.6l.8-.7M4 12h1M6.4 5.4l-.7.8"/></svg>
                    <p class="text-[11px] font-bold uppercase tracking-wider opacity-60 mb-8">Daily Attendance</p>
                    <h3 class="text-3xl font-extrabold tracking-normal mb-2 leading-none uppercase">{{ $attendanceStatus }}</h3>
                            <p class="text-xs font-bold opacity-80 uppercase tracking-wider">{{ $attendanceSub }}</p>
                </a>

                {{-- Recent Logs --}}
                <x-card-container 
                    class="h-[246px] shrink-0"
                    title="History"
                    padding="p-0"
                >
                    <x-slot name="headerAction">
                        <a href="{{ route('intern.attendance') }}" class="text-[10px] font-bold text-[#0B5694] dark:text-blue-400 uppercase hover:underline tracking-wider">View All</a>
                    </x-slot>
                    <div class="divide-y divide-gray-50 dark:divide-gray-800 overflow-y-auto custom-scrollbar h-[170px]">
                        @forelse($logs as $log)
                        <div class="px-8 py-5 flex justify-between items-center hover:bg-gray-50/30 dark:hover:bg-gray-800/20 transition-colors">
                            <div>
                                <p class="text-[13px] font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider">{{ $log->clock_in->format('M d, Y') }}</p>
                                <p class="text-[11px] text-gray-400 font-bold uppercase tracking-wider mt-0.5 leading-none">
                                    {{ $log->clock_in->format('h:i A') }} — {{ $log->clock_out ? $log->clock_out->format('h:i A') : 'Active' }}
                                </p>
                            </div>

                            @php
                                $status = 'on-time';
                                if ($log->status === 'absent') $status = 'absent';
                                elseif ($log->status === 'late') $status = 'late';
                                elseif ($log->status === 'excused') $status = 'excused';
                                elseif (!$log->clock_out) $status = 'active';

                                $statusConfig = match($status) {
                                    'late' => ['label' => 'Late', 'class' => 'text-orange-500 bg-orange-50 dark:bg-orange-950/30 border-orange-100 dark:border-orange-900/50'],
                                    'absent' => ['label' => 'Absent', 'class' => 'text-rose-500 bg-rose-50 dark:bg-rose-950/30 border-rose-100 dark:border-rose-900/50'],
                                    'excused' => ['label' => 'Excused', 'class' => 'text-blue-500 bg-blue-50 dark:bg-blue-950/30 border-blue-100 dark:border-blue-900/50'],
                                    'active' => ['label' => 'Active', 'class' => 'text-blue-400 bg-gray-50 dark:bg-gray-800 border-gray-100 dark:border-gray-700'],
                                    default => ['label' => 'On-Time', 'class' => 'text-emerald-500 bg-emerald-50 dark:bg-emerald-950/30 border-emerald-100 dark:border-emerald-900/50'],
                                };
                            @endphp

                            <span class="text-[9px] font-bold {{ $statusConfig['class'] }} border px-3 py-1 rounded-full uppercase tracking-wider">
                                {{ $statusConfig['label'] }}
                            </span>
                        </div>
                        @empty
                            <x-empty-state title="No logs recorded" compact="true" />
                        @endforelse
                    </div>
                </x-card-container>
            </div>
        </div>

        {{-- ROW 2: FIXED HEIGHT SCROLLING --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:h-[320px] mb-8 shrink-0">
            @php
                $todoTasks = $deadlines->filter(fn($t) => $t->status->value === 'revision' || ($t->status->value === 'pending' && $t->submissions->count() === 0));
                $submittedTasks = $deadlines->filter(fn($t) => $t->status->value === 'pending' && $t->submissions->count() > 0);
                $verifiedTasks = $deadlines->filter(fn($t) => $t->status->value === 'completed');
            @endphp
            {{-- DEADLINES COLUMN --}}
            <div class="col-span-1 lg:col-span-7 h-full flex flex-col min-h-[320px] lg:min-h-0" x-data="{ activeTab: 'todo' }">
                <x-card-container 
                    title="My Tasks"
                    padding="p-6 lg:p-8"
                    class="h-full"
                >
                    <x-slot name="headerAction">
                        <nav class="flex items-center gap-1 p-1 bg-gray-50/50 dark:bg-gray-800/50 rounded-2xl w-fit border border-gray-100 dark:border-gray-800 shrink-0">
                            <button @click="activeTab = 'todo'" 
                                    :class="activeTab === 'todo' ? 'bg-white dark:bg-gray-700 text-[#0B5694] dark:text-blue-400 shadow-sm border-gray-100 dark:border-gray-600' : 'text-gray-400 dark:text-slate-500 border-transparent hover:text-gray-600 dark:hover:text-gray-300'" 
                                    class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest border transition-all whitespace-nowrap active:scale-95 flex items-center gap-1.5">
                                Pending
                                <span class="px-1.5 py-0.5 rounded-md text-[8px] bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 font-bold">{{ $todoTasks->count() }}</span>
                            </button>
                            <button @click="activeTab = 'submitted'" 
                                    :class="activeTab === 'submitted' ? 'bg-white dark:bg-gray-700 text-[#0B5694] dark:text-blue-400 shadow-sm border-gray-100 dark:border-gray-600' : 'text-gray-400 dark:text-slate-500 border-transparent hover:text-gray-600 dark:hover:text-gray-300'" 
                                    class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest border transition-all whitespace-nowrap active:scale-95 flex items-center gap-1.5">
                                Submitted
                                <span class="px-1.5 py-0.5 rounded-md text-[8px] bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 font-bold">{{ $submittedTasks->count() }}</span>
                            </button>
                            <button @click="activeTab = 'verified'" 
                                    :class="activeTab === 'verified' ? 'bg-white dark:bg-gray-700 text-[#0B5694] dark:text-blue-400 shadow-sm border-gray-100 dark:border-gray-600' : 'text-gray-400 dark:text-slate-500 border-transparent hover:text-gray-600 dark:hover:text-gray-300'" 
                                    class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest border transition-all whitespace-nowrap active:scale-95 flex items-center gap-1.5">
                                Verified
                                <span class="px-1.5 py-0.5 rounded-md text-[8px] bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 font-bold">{{ $verifiedTasks->count() }}</span>
                            </button>
                        </nav>
                    </x-slot>
                    
                    <div class="relative flex-1 min-h-0 h-[210px] overflow-y-auto custom-scrollbar pr-2">
                        <!-- To-Do Tab Content -->
                        <div x-show="activeTab === 'todo'" x-transition class="space-y-4">
                            @forelse($todoTasks as $task)
                                @php
                                    $dueDate = \Carbon\Carbon::parse($task->due_date);
                                    $isDueToday = $dueDate->isToday();
                                    $isOverdue = $dueDate->isPast() && $task->status->value !== 'completed';
                                    $isUrgent = $isDueToday || $isOverdue;
                                @endphp
                                <a href="{{ route('intern.tasks.show', $task) }}" class="flex items-center gap-6 p-5 rounded-[30px] border border-gray-100 dark:border-gray-800 bg-gray-50/30 dark:bg-gray-800/10 hover:border-[#0B5694]/20 hover:bg-white dark:hover:bg-gray-800/30 transition-all group/deadline hover:shadow-lg">
                                    <div class="w-14 h-14 shrink-0 rounded-2xl flex flex-col items-center justify-center {{ $isUrgent ? 'bg-rose-50 dark:bg-rose-900/20 text-rose-500 border-rose-100' : 'bg-blue-50 dark:bg-blue-900/20 text-blue-500 border-blue-100' }} border dark:border-gray-800 shadow-sm transition-transform group-hover/deadline:scale-110">
                                        <span class="text-[10px] font-bold uppercase leading-none mb-0.5 tracking-wider">{{ $dueDate->format('M') }}</span>
                                        <span class="text-lg font-black leading-none tracking-tight">{{ $dueDate->format('d') }}</span>
                                    </div>
                                    <div class="flex-1 min-w-0 text-left">
                                        <div class="flex items-center gap-3">
                                            <h6 class="text-[15px] font-bold text-gray-800 dark:text-gray-100 group-hover/deadline:text-[#0B5694] dark:group-hover/deadline:text-blue-400 transition-colors truncate uppercase tracking-normal leading-tight truncate">{{ $task->title }}</h6>
                                            @if($isOverdue)
                                                <span class="px-2.5 py-0.5 bg-rose-50 dark:bg-rose-950/30 text-rose-500 border border-rose-100 dark:border-rose-900/50 rounded-full text-[9px] font-bold uppercase tracking-wider animate-pulse shrink-0">Overdue</span>
                                            @elseif($isDueToday)
                                                <span class="px-2.5 py-0.5 bg-orange-50 dark:bg-orange-950/30 text-orange-500 border border-orange-100 dark:border-orange-900/50 rounded-full text-[9px] font-bold uppercase tracking-wider animate-pulse shrink-0">Due Today</span>
                                            @elseif($task->status->value === 'revision')
                                                <span class="px-2.5 py-0.5 bg-yellow-50 dark:bg-yellow-950/30 text-yellow-600 border border-yellow-100 dark:border-yellow-900/50 rounded-full text-[9px] font-bold uppercase tracking-wider shrink-0">Revision</span>
                                            @endif
                                        </div>
                                        <p class="text-[12px] text-gray-400 font-bold uppercase tracking-wider mt-1 truncate">
                                            {{ $task->supervisor->supervisorDetails->first_name ?? 'Supervisor' }} • Due at {{ $dueDate->format('h:i A') }}
                                        </p>
                                    </div>
                                </a>
                            @empty
                                <div class="flex flex-col items-center justify-center h-full py-10 text-center">
                                    <div class="w-12 h-12 rounded-2xl bg-orange-50/50 dark:bg-orange-950/10 flex items-center justify-center mb-3 text-orange-400 dark:text-orange-500 animate-pulse">
                                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    <p class="text-xs font-bold uppercase tracking-wider text-gray-300 dark:text-gray-600">No pending tasks</p>
                                </div>
                            @endforelse
                        </div>

                        <!-- Submitted Tab Content -->
                        <div x-show="activeTab === 'submitted'" x-transition class="space-y-4">
                            @forelse($submittedTasks as $task)
                                @php
                                    $dueDate = \Carbon\Carbon::parse($task->due_date);
                                @endphp
                                <a href="{{ route('intern.tasks.show', $task) }}" class="flex items-center gap-6 p-5 rounded-[30px] border border-gray-100 dark:border-gray-800 bg-gray-50/30 dark:bg-gray-800/10 hover:border-[#0B5694]/20 hover:bg-white dark:hover:bg-gray-800/30 transition-all group/deadline hover:shadow-lg">
                                    <div class="w-14 h-14 shrink-0 rounded-2xl flex flex-col items-center justify-center bg-blue-50 dark:bg-blue-900/20 text-blue-500 border border-blue-100 dark:border-gray-800 shadow-sm transition-transform group-hover/deadline:scale-110">
                                        <span class="text-[10px] font-bold uppercase leading-none mb-0.5 tracking-wider">{{ $dueDate->format('M') }}</span>
                                        <span class="text-lg font-black leading-none tracking-tight">{{ $dueDate->format('d') }}</span>
                                    </div>
                                    <div class="flex-1 min-w-0 text-left">
                                        <div class="flex items-center gap-3">
                                            <h6 class="text-[15px] font-bold text-gray-800 dark:text-gray-100 group-hover/deadline:text-[#0B5694] dark:group-hover/deadline:text-blue-400 transition-colors truncate uppercase tracking-normal leading-tight truncate">{{ $task->title }}</h6>
                                            <span class="px-2.5 py-0.5 bg-blue-50 dark:bg-blue-950/30 text-blue-500 border border-blue-100 dark:border-blue-900/50 rounded-full text-[9px] font-bold uppercase tracking-wider shrink-0">Submitted</span>
                                        </div>
                                        <p class="text-[12px] text-gray-400 font-bold uppercase tracking-wider mt-1 truncate">
                                            Waiting for review • Submitted {{ $task->submissions->last() ? $task->submissions->last()->created_at->diffForHumans() : '' }}
                                        </p>
                                    </div>
                                </a>
                            @empty
                                <div class="flex flex-col items-center justify-center h-full py-10 text-center">
                                    <div class="w-12 h-12 rounded-2xl bg-blue-50/50 dark:bg-blue-950/10 flex items-center justify-center mb-3 text-blue-400 dark:text-blue-500">
                                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    <p class="text-xs font-bold uppercase tracking-wider text-gray-300 dark:text-gray-600">No submitted tasks</p>
                                </div>
                            @endforelse
                        </div>

                        <!-- Verified Tab Content -->
                        <div x-show="activeTab === 'verified'" x-transition class="space-y-4">
                            @forelse($verifiedTasks as $task)
                                @php
                                    $dueDate = \Carbon\Carbon::parse($task->due_date);
                                @endphp
                                <a href="{{ route('intern.tasks.show', $task) }}" class="flex items-center gap-6 p-5 rounded-[30px] border border-gray-100 dark:border-gray-800 bg-gray-50/30 dark:bg-gray-800/10 hover:border-[#0B5694]/20 hover:bg-white dark:hover:bg-gray-800/30 transition-all group/deadline hover:shadow-lg">
                                    <div class="w-14 h-14 shrink-0 rounded-2xl flex flex-col items-center justify-center bg-emerald-50 dark:bg-emerald-900/20 text-emerald-500 border border-emerald-100 dark:border-gray-800 shadow-sm transition-transform group-hover/deadline:scale-110">
                                        <span class="text-[10px] font-bold uppercase leading-none mb-0.5 tracking-wider">{{ $dueDate->format('M') }}</span>
                                        <span class="text-lg font-black leading-none tracking-tight">{{ $dueDate->format('d') }}</span>
                                    </div>
                                    <div class="flex-1 min-w-0 text-left">
                                        <div class="flex items-center gap-3">
                                            <h6 class="text-[15px] font-bold text-gray-800 dark:text-gray-100 group-hover/deadline:text-[#0B5694] dark:group-hover/deadline:text-blue-400 transition-colors truncate uppercase tracking-normal leading-tight truncate">{{ $task->title }}</h6>
                                            <span class="px-2.5 py-0.5 bg-emerald-50 dark:bg-emerald-950/30 text-emerald-500 border border-emerald-100 dark:border-emerald-900/50 rounded-full text-[9px] font-bold uppercase tracking-wider shrink-0">Verified</span>
                                        </div>
                                        <p class="text-[12px] text-gray-400 font-bold uppercase tracking-wider mt-1 truncate">
                                            Completed successfully • Approved by {{ $task->supervisor->supervisorDetails->first_name ?? 'Supervisor' }}
                                        </p>
                                    </div>
                                </a>
                            @empty
                                <div class="flex flex-col items-center justify-center h-full py-10 text-center">
                                    <div class="w-12 h-12 rounded-2xl bg-emerald-50/50 dark:bg-emerald-950/10 flex items-center justify-center mb-3 text-emerald-400 dark:text-emerald-500">
                                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                    <p class="text-xs font-bold uppercase tracking-wider text-gray-300 dark:text-gray-600">No verified tasks</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </x-card-container>
            </div>

            {{-- ANNOUNCEMENTS COLUMN --}}
            <div class="col-span-1 lg:col-span-5 h-full flex flex-col min-h-[320px] lg:min-h-0">
                <x-card-container 
                    title="Office Announcements"
                    headerActionLink="{{ route('intern.announcements') }}"
                    headerActionText="Access Feed"
                    padding="p-6 lg:p-8"
                    class="h-full"
                >
                    <div class="space-y-4 overflow-y-auto custom-scrollbar pr-4 flex-1 min-h-0 h-[210px]">
                        @forelse($announcements as $ann)
                        <div class="flex gap-5 group cursor-pointer items-start py-2 border-b border-gray-50 dark:border-gray-800/50 last:border-0" onclick="window.location.href='{{ route('intern.announcements.show', $ann) }}'">
                            <x-avatar :user="$ann->user" size="12" border="1" circular="true" textSize="xs" />
                            <div class="min-w-0">
                                <h6 class="text-[14px] font-bold text-gray-800 dark:text-gray-100 group-hover:text-[#0B5694] transition-colors uppercase leading-tight truncate tracking-normal">{{ $ann->title }}</h6>
                                <p class="text-[12px] text-gray-400 dark:text-gray-500 line-clamp-2 mt-1 leading-relaxed font-medium">
                                    {{ Str::limit(strip_tags($ann->content), 120) }}
                                </p>
                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mt-2">
                                    {{ $ann->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                        @empty
                            <x-empty-state title="No Announcements" compact="true" />
                        @endforelse
                    </div>
                </x-card-container>
            </div>

            {{-- Extension Request Modal --}}
            <div x-show="extensionModalOpen" 
                 class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6 bg-gray-900/60 dark:bg-black/80 backdrop-blur-sm" 
                 x-cloak
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">
                
                <div @click.away="extensionModalOpen = false" 
                     class="bg-white dark:bg-gray-900 w-full max-w-lg rounded-[30px] sm:rounded-[40px] shadow-2xl overflow-hidden border border-gray-100 dark:border-gray-800">
                    
                    <div class="p-6 sm:p-8">
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <p class="text-[10px] font-black text-rose-500 uppercase tracking-[0.2em] mb-1">Request extension</p>
                                <h3 class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white tracking-tight">Extend Internship Period</h3>
                            </div>
                            <button @click="extensionModalOpen = false" class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                            </button>
                        </div>
        
                        <form action="{{ route('intern.schedule.extension.store') }}" method="POST" class="space-y-6">
                            @csrf
                            
                            <div class="space-y-2 text-left">
                                <span class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest block">Proposed End Date</span>
                                <input type="date" name="new_end_date" x-model="extensionDate" required
                                    class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700 rounded-xl text-xs font-bold text-gray-800 dark:text-gray-100 focus:ring-[#0B5694]/20 focus:border-[#0B5694] transition">
                                <span class="text-[10px] text-gray-400">Recommended base date on remaining hours: <span class="font-bold text-gray-700 dark:text-gray-300" x-text="projectedEndDate"></span></span>
                            </div>

                            <div class="space-y-2 text-left">
                                <span class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest block">Reason for Extension</span>
                                <textarea name="reason" x-model="extensionReason" required rows="3" placeholder="Briefly describe the reason for the extension request (e.g. Absences, schedule conflicts...)"
                                    class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700 rounded-xl text-xs font-bold text-gray-800 dark:text-gray-100 focus:ring-[#0B5694]/20 focus:border-[#0B5694] transition resize-none"></textarea>
                            </div>
                            
                            <div class="pt-4 flex flex-col sm:flex-row gap-3">
                                <button @click="extensionModalOpen = false" type="button" class="w-full sm:w-auto px-6 py-3.5 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200 rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-gray-200 dark:hover:bg-gray-750 transition active:scale-95 text-center cursor-pointer">
                                    Cancel
                                </button>
                                <button type="submit" class="flex-1 bg-rose-600 hover:bg-rose-700 text-white py-3.5 rounded-xl text-xs font-bold uppercase tracking-wider transition active:scale-95 shadow-md shadow-rose-500/10 cursor-pointer text-center">
                                    Submit Request
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-page-layout>
@endsection
