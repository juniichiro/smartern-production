@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<x-page-layout 
    portalType="Head Supervisor" 
    userName="{{ Auth::user()->supervisorDetails->first_name ?? 'Head' }}" 
    userRole="{{ Auth::user()->supervisorDetails->office->name ?? 'Head Office' }}"
>
    <div x-data="headSupervisorDashboardManager('division')" class="flex flex-col">
        {{-- Header Section --}}
        <x-page-header
            title="Welcome back,"
            highlight="{{ Auth::user()->supervisorDetails->first_name ?? 'Head' }}"
            class="mb-12"
        >
            <x-slot:subtitle>
                <span x-text="activeTab === 'division' ? 'Division Oversight & Administrative Control' : '{{ $deptStats['office_name'] ?? 'Department' }} — Active Cohort Oversight'"></span>
            </x-slot:subtitle>

            <x-slot:actions>
                {{-- TAB NAVIGATION (Only for Dual Roles) --}}
                @if($deptStats)
                <div class="flex p-1.5 bg-gray-100 dark:bg-gray-900 rounded-[22px] border border-gray-200/50 dark:border-gray-800 shrink-0 shadow-inner">
                    <button 
                        @click="setTab('division')"
                        :class="activeTab === 'division' ? 'bg-white dark:bg-gray-800 text-[#0B5694] dark:text-blue-400 shadow-sm' : 'text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300'"
                        class="px-6 py-2.5 rounded-[18px] text-[11px] font-bold uppercase tracking-wider transition-all duration-300"
                    >
                        Division
                    </button>
                    <button 
                        @click="setTab('department')"
                        :class="activeTab === 'department' ? 'bg-white dark:bg-gray-800 text-[#0B5694] dark:text-blue-400 shadow-sm' : 'text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300'"
                        class="px-6 py-2.5 rounded-[18px] text-[11px] font-bold uppercase tracking-wider transition-all duration-300"
                    >
                        Department
                    </button>
                </div>
                @endif
            </x-slot:actions>
        </x-page-header>

        {{-- DEPARTMENT OVERSIGHT PANEL --}}
        @if($deptStats)
        <div x-show="activeTab === 'department'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="mb-12">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 sm:gap-8 mb-10">
                <div class="bg-white dark:bg-gray-900 p-5 sm:p-8 rounded-[25px] sm:rounded-[40px] border border-gray-100 dark:border-gray-800 shadow-sm flex items-center gap-4 sm:gap-6 group hover:border-blue-500/30 transition-all">
                    <div class="w-14 h-14 bg-blue-50 dark:bg-blue-900/20 rounded-2xl flex items-center justify-center text-[#0B5694] dark:text-blue-400 group-hover:scale-110 transition-transform">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <div>
                        <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Active Interns</p>
                        <h4 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-normal">{{ $deptStats['active_interns'] }}</h4>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-900 p-5 sm:p-8 rounded-[25px] sm:rounded-[40px] border border-gray-100 dark:border-gray-800 shadow-sm flex items-center gap-4 sm:gap-6 group hover:border-amber-500/30 transition-all">
                    <div class="w-14 h-14 bg-amber-50 dark:bg-amber-900/20 rounded-2xl flex items-center justify-center text-amber-600 dark:text-amber-400 group-hover:scale-110 transition-transform">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="16" height="16" x="4" y="4" rx="2"/><path d="m9 12 2 2 4-4"/></svg>
                    </div>
                    <div>
                        <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Pending Tasks</p>
                        <h4 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-normal">{{ $deptStats['pending_tasks'] }}</h4>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-900 p-5 sm:p-8 rounded-[25px] sm:rounded-[40px] border border-gray-100 dark:border-gray-800 shadow-sm flex items-center gap-4 sm:gap-6 group hover:border-rose-500/30 transition-all">
                    <div class="w-14 h-14 bg-rose-50 dark:bg-rose-950/30 rounded-2xl flex items-center justify-center text-rose-600 dark:text-rose-400 group-hover:scale-110 transition-transform">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                    <div>
                        <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Attendance Issues</p>
                        <h4 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-normal">{{ $deptStats['attendance_issues'] }}</h4>
                    </div>
                </div>
            </div>

            {{-- DEPT ACTION QUEUES --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-10">
                {{-- UPCOMING DEADLINES --}}
                <x-card-container 
                    class="lg:col-span-7"
                    title="Fulfillment Pipeline"
                    headerActionLink="{{ route('supervisor.tasks') }}"
                    headerActionText="View Pipeline"
                    padding="p-6"
                >
                    <div x-show="loading" class="space-y-6">
                        @for($i = 0; $i < 3; $i++)
                            <div class="flex items-center gap-6 p-5 rounded-[30px] border border-gray-50 dark:border-gray-800 bg-gray-50/30 dark:bg-gray-800/10">
                                <x-skeleton class="w-14 h-14 rounded-2xl shrink-0" />
                                <div class="flex-1 space-y-2">
                                    <x-skeleton class="h-5 w-3/4 rounded-lg" />
                                    <x-skeleton class="h-3 w-1/2 rounded-lg" />
                                </div>
                            </div>
                        @endfor
                    </div>

                    <div x-show="!loading" x-transition.opacity.duration.500ms class="space-y-6 max-h-[400px] overflow-y-auto custom-scrollbar pr-2">
                        @forelse($upcomingDeadlines as $task)
                            @php
                                $dueDate = \Carbon\Carbon::parse($task->due_date);
                                $isUrgent = $dueDate->diffInDays(now()) <= 1;
                                $isOverdue = $dueDate->isPast() && $task->status->value !== 'completed';
                                $isDueToday = $dueDate->isToday() && $task->status->value !== 'completed';
                            @endphp
                            <a href="{{ route('supervisor.tasks.intern', $task->internDetails->user) }}" class="flex items-center gap-6 p-5 rounded-[30px] border border-gray-50 dark:border-gray-800 bg-gray-50/30 dark:bg-gray-800/10 hover:border-[#0B5694]/20 hover:bg-white dark:hover:bg-gray-800/30 transition-all group/deadline hover:shadow-lg shrink-0">
                                <div class="w-14 h-14 shrink-0 rounded-2xl flex flex-col items-center justify-center {{ $isUrgent ? 'bg-rose-50 dark:bg-rose-900/20 text-rose-500 border-rose-100' : 'bg-blue-50 dark:bg-blue-900/20 text-blue-500 border-blue-100' }} border dark:border-gray-800 shadow-sm transition-transform group-hover/deadline:scale-110">
                                    <span class="text-[10px] font-bold uppercase leading-none mb-0.5 tracking-wider">{{ $dueDate->format('M') }}</span>
                                    <span class="text-lg font-extrabold leading-none tracking-normal">{{ $dueDate->format('d') }}</span>
                                </div>
                                <div class="flex-1 min-w-0 text-left">
                                    <div class="flex items-center gap-3">
                                        <h6 class="text-[15px] font-extrabold text-gray-800 dark:text-gray-100 group-hover/deadline:text-[#0B5694] dark:group-hover/deadline:text-blue-400 transition-colors truncate uppercase tracking-normal leading-tight">{{ $task->title }}</h6>
                                        @if($isOverdue)
                                            <span class="px-2.5 py-0.5 bg-rose-50 dark:bg-rose-950/30 text-rose-500 border border-rose-100 dark:border-rose-900/50 rounded-full text-[9px] font-bold uppercase tracking-wider animate-pulse shrink-0">Overdue</span>
                                        @elseif($isDueToday)
                                            <span class="px-2.5 py-0.5 bg-orange-50 dark:bg-orange-950/30 text-orange-500 border border-orange-100 dark:border-orange-900/50 rounded-full text-[9px] font-bold uppercase tracking-wider animate-pulse shrink-0">Due Today</span>
                                        @endif
                                    </div>
                                    <p class="text-[12px] text-gray-400 font-bold uppercase tracking-wider mt-1 truncate">
                                        {{ $task->internDetails->first_name }} {{ $task->internDetails->last_name }} • Due at {{ $dueDate->format('h:i A') }}
                                    </p>
                                </div>
                            </a>
                        @empty
                            <x-empty-state title="No Approaching Deadlines" compact="true" />
                        @endforelse
                    </div>
                </x-card-container>

                {{-- RIGHT COLUMN --}}
                <div class="col-span-1 lg:col-span-5 flex flex-col gap-8 overflow-hidden">
                    {{-- PENDING VERIFICATIONS --}}
                    <x-card-container 
                        title="Pending Verifications"
                        headerActionLink="{{ route('supervisor.tasks') }}"
                        headerActionText="View Queue"
                        padding="p-6"
                        class="min-h-0"
                    >
                        <div x-show="loading" class="space-y-6">
                            @for($i = 0; $i < 2; $i++)
                                <div class="flex items-center gap-5">
                                    <x-skeleton class="w-10 h-10 rounded-2xl shrink-0" />
                                    <div class="flex-1 space-y-2">
                                        <x-skeleton class="h-4 w-2/3 rounded-lg" />
                                        <x-skeleton class="h-3 w-1/2 rounded-lg" />
                                    </div>
                                </div>
                            @endfor
                        </div>

                        <div x-show="!loading" x-transition.opacity.duration.500ms class="space-y-6 max-h-[160px] overflow-y-auto custom-scrollbar pr-2">
                            @forelse($submissionsNeedingReview as $task)
                                <a href="{{ route('supervisor.tasks.show', $task) }}" class="flex items-center gap-5 group/item transition-all hover:translate-x-1 shrink-0">
                                    <div class="w-10 h-10 bg-blue-50 dark:bg-blue-900/20 rounded-2xl flex items-center justify-center shrink-0 border border-blue-100 dark:border-blue-800 transition-transform group-hover/item:scale-110">
                                        <svg class="text-[#0B5694] dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><path d="m9 15 3 3 5-5"/></svg>
                                    </div>
                                    <div class="flex-1 min-w-0 text-left">
                                        <p class="text-[15px] font-bold text-gray-800 dark:text-gray-100 truncate uppercase tracking-wider leading-tight group-hover/item:text-[#0B5694] transition-colors">{{ $task->title }}</p>
                                        <p class="text-[11px] text-gray-400 font-bold uppercase tracking-wider mt-1 truncate">
                                            {{ $task->internDetails->first_name }} • {{ $task->updated_at->diffForHumans() }}
                                        </p>
                                    </div>
                                </a>
                            @empty
                                <x-empty-state title="Zero submissions." compact="true" :showIcon="false" />
                            @endforelse
                        </div>
                    </x-card-container>

                    {{-- ACTIVE ATTENDANCE --}}
                    <x-card-container 
                        title="Active Attendance"
                        padding="p-6"
                        class="min-h-0"
                    >
                        <div x-show="loading" class="space-y-6">
                            @for($i = 0; $i < 2; $i++)
                                <div class="flex items-center gap-5">
                                    <x-skeleton class="w-10 h-10 rounded-full shrink-0" />
                                    <div class="flex-1 space-y-2">
                                        <x-skeleton class="h-4 w-2/3 rounded-lg" />
                                        <x-skeleton class="h-3 w-1/2 rounded-lg" />
                                    </div>
                                </div>
                            @endfor
                        </div>

                        <div x-show="!loading" x-transition.opacity.duration.500ms class="space-y-6 max-h-[160px] overflow-y-auto custom-scrollbar pr-2">
                            @forelse($activeAttendance as $attendance)
                                <div class="flex items-center gap-5 group/att shrink-0">
                                    <div class="relative shrink-0 transition-transform group-hover/att:scale-110">
                                        <x-avatar :user="$attendance->internDetails" size="10" border="1" circular="true" background="bg-emerald-500 dark:bg-emerald-600" textSize="xs" />
                                        <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-white dark:bg-gray-950 rounded-full flex items-center justify-center">
                                            <div class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></div>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0 text-left">
                                        <p class="text-[15px] font-extrabold text-gray-800 dark:text-gray-200 truncate uppercase tracking-normal leading-tight hover:text-[#0B5694] dark:hover:text-blue-400 transition-colors">
                                            <a href="{{ route('supervisor.interns.show', $attendance->internDetails->user) }}">
                                                {{ $attendance->internDetails->first_name }} {{ $attendance->internDetails->last_name }}
                                            </a>
                                        </p>
                                        <p class="text-[12px] text-gray-400 font-bold uppercase mt-1 tracking-wider">Clocked in at {{ $attendance->clock_in->format('h:i A') }}</p>
                                    </div>
                                </div>
                            @empty
                                <x-empty-state title="Currently no active sessions." compact="true" :showIcon="false" />
                            @endforelse
                        </div>
                    </x-card-container>
                </div>
            </div>

            {{-- COHORT PERFORMANCE --}}
            <x-card-container title="Cohort Performance Monitoring" padding="p-8 lg:p-10" class="shrink-0 rounded-[50px]">
                <div x-show="loading" class="flex overflow-x-auto custom-scrollbar pb-4 gap-6 sm:gap-10">
                    @for($i = 0; $i < 3; $i++)
                        <div class="min-w-[260px] sm:min-w-[300px] md:min-w-[340px] lg:min-w-[calc(33.33%-2rem)] bg-gray-50/50 dark:bg-gray-800/30 p-5 sm:p-6 rounded-[25px] sm:rounded-[35px] border border-gray-100 dark:border-gray-700 space-y-4">
                            <div class="flex justify-between items-start">
                                <div class="flex-1 space-y-2">
                                    <x-skeleton class="h-5 w-3/4 rounded-lg" />
                                    <x-skeleton class="h-3 w-1/2 rounded-lg" />
                                </div>
                                <x-skeleton class="h-4 w-12 rounded-lg" />
                            </div>
                            <x-skeleton class="h-2 w-full rounded-full" />
                            <div class="flex justify-between items-center">
                                <x-skeleton class="h-3 w-20 rounded-lg" />
                                <x-skeleton class="h-3 w-16 rounded-lg" />
                            </div>
                            <x-skeleton class="h-2 w-full rounded-full" />
                        </div>
                    @endfor
                </div>

                <div x-show="!loading" x-transition.opacity.duration.500ms class="flex overflow-x-auto custom-scrollbar pb-4 gap-6 sm:gap-10 snap-x snap-mandatory">
                    @forelse($deptInterns as $intern)
                        @php
                            $total = $intern->tasks->count();
                            $done = $intern->tasks->where('status', 'completed')->count();
                            $percentage = $total > 0 ? round(($done / $total) * 100) : 0;
                            $targetHours = $intern->required_hours ?? 480;
                            $hoursPercent = $targetHours > 0 ? round(($intern->hours_rendered / $targetHours) * 100) : 0;
                            $statusColor = 'text-[#0B5694] dark:text-blue-400';
                        @endphp
                        <div class="flex flex-col group min-w-[260px] sm:min-w-[300px] md:min-w-[340px] lg:min-w-[calc(33.33%-2rem)] snap-start bg-gray-50/50 dark:bg-gray-800/30 p-5 sm:p-6 rounded-[25px] sm:rounded-[35px] border border-gray-100 dark:border-gray-700 transition-all hover:border-[#0B5694]/10 hover:shadow-xl hover:bg-white dark:hover:bg-gray-800/50">
                            <div class="flex justify-between items-start mb-6">
                                <div class="min-w-0 flex-1 pr-6">
                                    <p class="text-[15px] font-black text-gray-800 dark:text-gray-100 group-hover:text-[#0B5694] transition-colors uppercase tracking-tight truncate leading-tight">
                                        <a href="{{ route('supervisor.interns.show', $intern->user) }}">
                                            {{ $intern->first_name }} {{ $intern->last_name }}
                                        </a>
                                    </p>
                                    <p class="text-[12px] text-gray-400 font-bold uppercase tracking-wider mt-1 truncate">{{ $intern->school }}</p>
                                </div>
                                <span class="text-[11px] font-bold {{ $statusColor }} tracking-wider whitespace-nowrap uppercase">{{ $percentage }}% Tasks</span>
                            </div>
                            
                            <div class="space-y-4">
                                {{-- Task Progress --}}
                                <div class="w-full bg-gray-100 dark:bg-gray-700 h-2 rounded-full overflow-hidden shadow-inner border border-gray-100 dark:border-gray-600">
                                    <div class="h-full bg-[#0B5694] rounded-full transition-all duration-700" style="width: {{ $percentage }}%"></div>
                                </div>
                                
                                {{-- Hours Progress --}}
                                <div class="flex justify-between items-center text-[12px] font-bold uppercase tracking-wider">
                                    <span class="text-gray-400">Hours Completed</span>
                                    <span class="text-gray-800 dark:text-gray-200">{{ (int)$intern->hours_rendered }} / {{ $targetHours }} HRS</span>
                                </div>
                                <div class="w-full bg-gray-100 dark:bg-gray-700 h-2 rounded-full overflow-hidden shadow-inner border border-gray-100 dark:border-gray-600">
                                    <div class="h-full bg-emerald-500 rounded-full transition-all duration-700" style="width: {{ $hoursPercent }}%"></div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <x-empty-state title="No active interns in cohort." compact="true" />
                    @endforelse
                </div>
            </x-card-container>
        </div>
        @endif

        {{-- DIVISION OVERVIEW PANEL --}}
        <div x-show="activeTab === 'division'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="flex flex-col">
            {{-- ACTION QUEUES --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-10">
                {{-- PENDING PIPELINE --}}
                <x-card-container 
                    class="lg:col-span-7"
                    title="Applicants for Review"
                    headerActionLink="{{ route('head-supervisor.matching') }}"
                    headerActionText="View Pipeline"
                >
                    {{-- Skeletons --}}
                    <div x-show="loading" class="space-y-5">
                        @for($i = 0; $i < 2; $i++)
                            <div class="flex items-center gap-5 pb-5 border-b border-gray-100 dark:border-gray-800/80 last:border-0 last:pb-0">
                                <x-skeleton class="w-12 h-12 rounded-full" />
                                <div class="flex-1 space-y-2">
                                    <x-skeleton class="h-4 w-32 rounded-lg" />
                                    <x-skeleton class="h-3 w-24 rounded-lg" />
                                    <x-skeleton class="h-4 w-40 rounded-lg mt-2" />
                                </div>
                                <x-skeleton class="h-10 w-20 rounded-xl" />
                            </div>
                        @endfor
                    </div>

                    <div x-show="!loading" x-transition.opacity.duration.500ms class="space-y-5 max-h-[220px] overflow-y-auto custom-scrollbar pr-2">
                        @forelse($recentApplications as $intern)
                            @php
                                $score = $intern->match_score;
                                if ($score >= 80) {
                                    $recommendationText = "Highly Recommended";
                                    $badgeClass = "bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/50";
                                } elseif ($score >= 60) {
                                    $recommendationText = "Recommended";
                                    $badgeClass = "bg-blue-50/70 dark:bg-blue-950/30 text-blue-600 dark:text-blue-400 border border-blue-100 dark:border-blue-900/50";
                                } elseif ($score >= 40) {
                                    $recommendationText = "Needs Training";
                                    $badgeClass = "bg-indigo-50 dark:bg-indigo-950/30 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-900/50";
                                } else {
                                    $recommendationText = "Not Recommended";
                                    $badgeClass = "bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-400 border border-amber-100 dark:border-amber-900/50";
                                }
                            @endphp
                            <div class="flex items-center gap-5 pb-5 border-b border-gray-100 dark:border-gray-800/80 last:border-0 last:pb-0 group/item">
                                <x-avatar :user="$intern" size="12" border="1" circular="true" textSize="xs" />
                                <div class="flex-1 min-w-0 text-left">
                                    <h6 class="text-[16px] font-extrabold text-gray-800 dark:text-gray-100 group-hover/item:text-[#0B5694] transition-colors truncate uppercase tracking-normal">{{ $intern->first_name }} {{ $intern->last_name }}</h6>
                                    <p class="text-[12px] text-gray-400 dark:text-gray-500 font-bold uppercase tracking-wider mt-0.5 leading-none">
                                        {{ $intern->school }}
                                    </p>
                                    <div class="flex flex-wrap items-center gap-2 mt-2">
                                        <span class="px-2.5 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider {{ $badgeClass }}">
                                            {{ $recommendationText }}
                                        </span>
                                        <span class="text-[11px] text-gray-400 dark:text-gray-500 font-bold uppercase tracking-wider">
                                            for {{ $intern->matched_office }}
                                        </span>
                                    </div>
                                </div>
                                <a href="{{ route('head-supervisor.matching') }}" class="px-5 py-2.5 bg-[#0B5694] dark:bg-blue-600 text-white rounded-xl text-[10px] font-bold uppercase tracking-wider hover:scale-105 active:scale-95 transition-all shadow-lg shadow-blue-500/20">Review</a>
                            </div>
@empty
                            <x-empty-state title="No new applications to process." compact="true" />
                        @endforelse
                    </div>
                </x-card-container>

                {{-- CERTIFICATE REVIEW --}}
                <x-card-container 
                    class="lg:col-span-5"
                    title="Pending Certificates"
                >
                    <x-slot name="headerAction">
                        <span class="bg-orange-50 dark:bg-orange-900/20 text-orange-500 px-3 py-1 rounded-full text-[10px] font-bold uppercase border border-orange-100 dark:border-orange-800 tracking-wider">{{ $pendingCertificates }} Pending</span>
                    </x-slot>
                    
                    {{-- Skeletons --}}
                    <div x-show="loading" class="space-y-5">
                        @for($i = 0; $i < 2; $i++)
                            <div class="flex items-center gap-4 pb-5 border-b border-gray-100 dark:border-gray-800/80 last:border-0 last:pb-0">
                                <x-skeleton class="w-10 h-10 rounded-xl" />
                                <div class="flex-1 space-y-2">
                                    <x-skeleton class="h-4 w-28 rounded-lg" />
                                    <x-skeleton class="h-3 w-32 rounded-lg" />
                                </div>
                            </div>
                        @endfor
                    </div>

                    <div x-show="!loading" x-transition.opacity.duration.500ms class="space-y-5 max-h-[220px] overflow-y-auto custom-scrollbar pr-2">
                        @forelse($pendingCertificateDetails as $cert)
                            <div class="flex items-center gap-4 pb-5 border-b border-gray-100 dark:border-gray-800/80 last:border-0 last:pb-0">
                                <div class="w-10 h-10 bg-orange-50 dark:bg-orange-900/20 rounded-xl flex items-center justify-center text-orange-500 border border-orange-100 dark:border-orange-800">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/></svg>
                                </div>
                                <div class="flex-1 min-w-0 text-left">
                                    <p class="text-[15px] font-extrabold text-gray-800 dark:text-gray-200 truncate uppercase tracking-normal">{{ $cert->intern->first_name }} {{ $cert->intern->last_name }}</p>
                                    <p class="text-[11px] text-gray-400 font-bold uppercase tracking-wider mt-0.5 italic">Ready for final publication</p>
                                </div>
                            </div>
                        @empty
                            <x-empty-state title="No pending certificates" compact="true" />
                        @endforelse
                    </div>
                </x-card-container>
            </div>

            {{-- ENDORSEMENT PIPELINE --}}
            <div class="mb-10">
                <x-card-container 
                    title="Endorsement Pipeline"
                    headerActionLink="{{ route('head-supervisor.certificates.index') }}"
                    headerActionText="Go to Endorsements"
                >
                    <x-slot name="headerAction">
                        <span class="bg-emerald-50 dark:bg-emerald-900/20 text-emerald-500 px-3 py-1 rounded-full text-[10px] font-bold uppercase border border-emerald-100 dark:border-emerald-800 tracking-wider">{{ $endorsementQueue->count() }} Endorsed</span>
                    </x-slot>

                    <div x-show="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @for($i = 0; $i < 3; $i++)
                            <div class="flex items-center gap-4 p-4 rounded-2xl border border-gray-100 dark:border-gray-800">
                                <x-skeleton class="w-10 h-10 rounded-full" />
                                <div class="flex-1 space-y-2">
                                    <x-skeleton class="h-4 w-24 rounded-lg" />
                                    <x-skeleton class="h-3 w-32 rounded-lg" />
                                </div>
                            </div>
                        @endfor
                    </div>

                    <div x-show="!loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @forelse($endorsementQueue as $intern)
                            <div class="flex items-center gap-4 p-4 rounded-2xl bg-gray-50/50 dark:bg-gray-800/30 border border-gray-100 dark:border-gray-800 group hover:border-emerald-500 transition-all">
                                <x-avatar :user="$intern" size="10" border="1" circular="true" textSize="xs" />
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-extrabold text-gray-800 dark:text-gray-100 truncate uppercase tracking-normal">{{ $intern->first_name }} {{ $intern->last_name }}</p>
                                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider truncate">{{ $intern->office->name ?? 'Unassigned' }}</p>
                                </div>
                                <div class="text-right">
                                    <span class="text-[10px] font-bold text-emerald-500 uppercase tracking-wider">Officially Endorsed</span>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full">
                                <x-empty-state title="No interns are currently awaiting endorsement." compact="true" />
                            </div>
                        @endforelse
                    </div>
                </x-card-container>
            </div>

            {{-- DEPARTMENT WORKLOAD OVERVIEW --}}
            <x-card-container 
                title="Department Workload Overview"
                headerActionLink="{{ route('head-supervisor.onboarding.index', ['#offices']) }}"
                headerActionText="Manage Offices"
                padding="p-8 lg:p-12"
            >
                {{-- Skeletons --}}
                <div x-show="loading" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-8">
                    @for($i = 0; $i < 4; $i++)
                        <div class="p-6 rounded-3xl border border-gray-100 dark:border-gray-800">
                            <x-skeleton class="h-4 w-24 rounded-lg mb-4" />
                            <div class="flex items-end justify-between mb-4">
                                <x-skeleton class="h-8 w-12 rounded-lg" />
                                <x-skeleton class="h-3 w-10 rounded-lg" />
                            </div>
                            <x-skeleton class="h-1.5 w-full rounded-full" />
                        </div>
                    @endfor
                </div>

                <div x-show="!loading" x-transition.opacity.duration.500ms class="max-h-[400px] overflow-y-auto custom-scrollbar pr-2">
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-8">
                        @foreach($officeLoad as $office)
                            @php
                                $limit = $office->intern_limit ?: 10;
                                $percent = min(100, round(($office->intern_details_count / $limit) * 100));
                                $barColor = $percent > 80 ? 'bg-rose-500' : ($percent > 40 ? 'bg-[#0B5694]' : 'bg-blue-300');
                            @endphp
                            <div class="p-6 rounded-3xl bg-gray-50/50 dark:bg-gray-800/30 border border-gray-50 dark:border-gray-800 group hover:border-[#0B5694] transition-all">
                                <p class="text-[13px] font-extrabold text-gray-800 dark:text-gray-200 uppercase truncate mb-4 tracking-normal">{{ $office->name }}</p>
                                <div class="flex items-end justify-between mb-4">
                                    <div class="flex flex-col">
                                        <h5 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-normal">{{ $office->intern_details_count }}</h5>
                                        <span class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-normal">of {{ $limit }} slots</span>
                                    </div>
                                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Interns</span>
                                </div>
                                <div class="w-full bg-gray-200 dark:bg-gray-700 h-1.5 rounded-full overflow-hidden shadow-inner shadow-gray-200 dark:shadow-gray-900">
                                    <div class="h-full {{ $barColor }} transition-all duration-1000" style="width: {{ $percent }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </x-card-container>
        </div>
    </div>
</x-page-layout>
@endsection
