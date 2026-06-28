@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<x-page-layout 
    portalType="Supervisor" 
    userName="{{ Auth::user()->supervisorDetails->first_name }}" 
    userRole="{{ Auth::user()->supervisorDetails->office->name }}"
    fullHeight="true"
>
    <div x-data="supervisorDashboardManager" class="flex flex-col h-full">
        {{-- HEADER --}}
        <x-page-header
            title="Welcome back,"
            highlight="{{ Auth::user()->supervisorDetails->first_name }}"
            subtitle="Operational Oversight — {{ Auth::user()->supervisorDetails->office->name }}"
            compact="true"
        />

        {{-- ACTION QUEUES GRID --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-4 md:gap-8 mb-10 flex-1 min-h-0">
        
        {{-- UPCOMING DEADLINES (Left - 7 cols) --}}
        <x-card-container 
            class="col-span-1 lg:col-span-7"
            title="Fulfillment Pipeline"
            headerActionLink="{{ route('supervisor.tasks') }}"
            headerActionText="View Pipeline"
            padding="p-6"
        >
            {{-- Skeleton --}}
            <template x-if="loading">
                <div class="space-y-6">
                    @for($i = 0; $i < min(count($upcomingDeadlines) ?: 3, 3); $i++)
                        <div class="flex items-center gap-6 p-5 rounded-[30px] border border-gray-50 dark:border-gray-800 bg-gray-50/30 dark:bg-gray-800/10">
                            <x-skeleton class="w-14 h-14 rounded-2xl shrink-0" />
                            <div class="flex-1 space-y-2">
                                <x-skeleton class="h-5 w-3/4 rounded-lg" />
                                <x-skeleton class="h-3 w-1/2 rounded-lg" />
                            </div>
                        </div>
                    @endfor
                </div>
            </template>

            <div x-show="!loading" x-transition.opacity.duration.500ms class="flex-1 flex flex-col min-h-0">
                @if($upcomingDeadlines->isEmpty())
                    <x-empty-state 
                        title="No Approaching Deadlines"
                        :actionLink="route('supervisor.tasks') . '#assignment-form'"
                        actionText="Assign New Task"
                        compact="true"
                        class="py-0 h-full w-full"
                    />
                @else
                    <div class="overflow-y-auto custom-scrollbar flex-1 flex flex-col pr-2">
                        @foreach($upcomingDeadlines as $task)
                        @php
                            $dueDate = \Carbon\Carbon::parse($task->due_date);
                            $isUrgent = $dueDate->diffInDays(now()) <= 1;
                            $isOverdue = $dueDate->isPast() && $task->status->value !== 'completed';
                            $isDueToday = $dueDate->isToday() && $task->status->value !== 'completed';
                        @endphp
                        <a href="{{ route('supervisor.tasks.intern', $task->internDetails->user) }}" class="flex items-center gap-6 p-5 rounded-[30px] border border-gray-50 dark:border-gray-800 bg-gray-50/30 dark:bg-gray-800/10 hover:border-[#0B5694]/20 hover:bg-white dark:hover:bg-gray-800/30 transition-all group/deadline hover:shadow-lg mb-6 last:mb-0 shrink-0">
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
                    @endforeach
                </div>
            @endif
            </div>
        </x-card-container>

        {{-- RIGHT COLUMN (5 cols on desktop, full width on tablet, stacked on mobile) --}}
        <div class="col-span-1 md:col-span-1 lg:col-span-5 flex flex-col gap-4 md:gap-8 overflow-hidden">
            {{-- PENDING VERIFICATIONS --}}
            <x-card-container 
                title="Pending Verifications"
                headerActionLink="{{ route('supervisor.tasks') }}"
                headerActionText="View Queue"
                padding="p-6"
                class="flex-1 min-h-0"
            >
                {{-- Skeleton --}}
                <template x-if="loading">
                    <div class="space-y-6">
                        @for($i = 0; $i < min(count($submissionsNeedingReview) ?: 2, 2); $i++)
                            <div class="flex items-center gap-5">
                                <x-skeleton class="w-10 h-10 rounded-2xl shrink-0" />
                                <div class="flex-1 space-y-2">
                                    <x-skeleton class="h-4 w-2/3 rounded-lg" />
                                    <x-skeleton class="h-3 w-1/2 rounded-lg" />
                                </div>
                            </div>
                        @endfor
                    </div>
                </template>

                <div x-show="!loading" x-transition.opacity.duration.500ms class="flex-1 flex flex-col min-h-0">
                    @if($submissionsNeedingReview->isEmpty())
                        <x-empty-state title="Zero submissions." compact="true" :showIcon="false" class="py-0 h-full w-full" />
                    @else
                        <div class="overflow-y-auto custom-scrollbar flex-1 flex flex-col pr-2">
                            @foreach($submissionsNeedingReview as $task)
                                <a href="{{ route('supervisor.tasks.show', $task) }}" class="flex items-center gap-5 group/item transition-all hover:translate-x-1 mb-6 last:mb-0 shrink-0">
                                    <div class="w-10 h-10 bg-blue-50 dark:bg-blue-900/20 rounded-2xl flex items-center justify-center shrink-0 border border-blue-100 dark:border-blue-800 transition-transform group-hover/item:scale-110">
                                        <svg class="text-[#0B5694] dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><path d="m9 15 3 3 5-5"/></svg>
                                    </div>
                                    <div class="flex-1 min-w-0 text-left">
                                        <p class="text-[15px] font-black text-gray-800 dark:text-gray-100 truncate uppercase tracking-tight leading-tight group-hover/item:text-[#0B5694] transition-colors">{{ $task->title }}</p>
                                        <p class="text-[11px] text-gray-400 font-bold uppercase tracking-widest mt-1 truncate">
                                            {{ $task->internDetails->first_name }} • {{ $task->updated_at->diffForHumans() }}
                                        </p>
                                    </div>
                                    <div class="p-2.5 bg-[#0B5694] dark:bg-blue-600 text-white rounded-xl group-hover/item:scale-105 active:scale-95 transition-all shadow-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </x-card-container>

            {{-- ACTIVE ATTENDANCE --}}
            <x-card-container 
                title="Active Attendance"
                padding="p-6"
                class="flex-1 min-h-0"
            >
                {{-- Skeleton --}}
                <template x-if="loading">
                    <div class="space-y-6">
                        @for($i = 0; $i < min(count($activeAttendance) ?: 2, 2); $i++)
                            <div class="flex items-center gap-5">
                                <x-skeleton class="w-10 h-10 rounded-full shrink-0" />
                                <div class="flex-1 space-y-2">
                                    <x-skeleton class="h-4 w-2/3 rounded-lg" />
                                    <x-skeleton class="h-3 w-1/2 rounded-lg" />
                                </div>
                            </div>
                        @endfor
                    </div>
                </template>

                <div x-show="!loading" x-transition.opacity.duration.500ms class="flex-1 flex flex-col min-h-0">
                    @if($activeAttendance->isEmpty())
                        <x-empty-state title="Currently no active sessions." compact="true" :showIcon="false" class="py-0 h-full w-full" />
                    @else
                        <div class="overflow-y-auto custom-scrollbar flex-1 flex flex-col pr-2">
                            @foreach($activeAttendance as $attendance)
                                <div class="flex items-center gap-5 group/att mb-6 last:mb-0 shrink-0">
                                    <div class="relative shrink-0 transition-transform group-hover/att:scale-110">
                                        <x-avatar :user="$attendance->internDetails" size="10" border="1" circular="true" background="bg-emerald-500 dark:bg-emerald-600" textSize="xs" />
                                        <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-white dark:bg-gray-950 rounded-full flex items-center justify-center">
                                            <div class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></div>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0 text-left">
                                        <p class="text-[15px] font-black text-gray-800 dark:text-gray-200 truncate uppercase tracking-tight leading-tight hover:text-[#0B5694] dark:hover:text-blue-400 transition-colors">
                                            <a href="{{ route('supervisor.interns.show', $attendance->internDetails->user) }}">
                                                {{ $attendance->internDetails->first_name }} {{ $attendance->internDetails->last_name }}
                                            </a>
                                        </p>
                                        <p class="text-[12px] text-gray-400 font-bold uppercase mt-1 tracking-widest">Clocked in at {{ $attendance->clock_in->format('h:i A') }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if($attendanceIssues > 0)
                    <div class="m-6 mt-0 p-4 bg-orange-50 dark:bg-orange-950/20 border border-orange-100 dark:border-orange-900/50 rounded-[30px] flex items-center gap-4 shrink-0 text-left">
                        <div class="w-8 h-8 bg-orange-500 rounded-lg flex items-center justify-center text-white shrink-0 shadow-lg shadow-orange-500/20">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        </div>
                        <p class="text-[11px] font-black text-orange-700 dark:text-orange-400 uppercase tracking-[0.2em] italic leading-tight">
                            {{ $attendanceIssues }} Critical Attendance Alert(s) Recorded
                        </p>
                    </div>
                    @endif
                </div>
            </x-card-container>
        </div>
    </div>

    {{-- INTERN PERFORMANCE FOOTER --}}
    <x-card-container title="Cohort Performance Monitoring" padding="p-8 lg:p-10" class="shrink-0 rounded-[50px]">
        {{-- Skeleton --}}
        <template x-if="loading">
            <div class="flex overflow-x-auto custom-scrollbar pb-4 gap-4 md:gap-6 lg:gap-10 snap-x snap-mandatory">
                @for($i = 0; $i < min(count($interns) ?: 3, 3); $i++)
                    <div class="shrink-0 w-[85vw] sm:w-[340px] lg:min-w-[340px] bg-gray-50/50 dark:bg-gray-800/30 p-5 md:p-6 rounded-[25px] md:rounded-[35px] border border-gray-100 dark:border-gray-700 space-y-4 snap-start">
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
        </template>

        <div x-show="!loading" x-transition.opacity.duration.500ms class="flex overflow-x-auto custom-scrollbar pb-4 gap-4 md:gap-6 lg:gap-10 snap-x snap-mandatory">
            @forelse($interns as $intern)
                @php
                    $total = $intern->tasks->count();
                    $done = $intern->tasks->where('status', 'completed')->count();
                    $percentage = $total > 0 ? round(($done / $total) * 100) : 0;
                    $targetHours = $intern->required_hours ?? 480;
                    $hoursPercent = $targetHours > 0 ? round(($intern->hours_rendered / $targetHours) * 100) : 0;
                    $statusColor = 'text-[#0B5694] dark:text-blue-400';
                @endphp
                <div class="flex flex-col group shrink-0 w-[85vw] sm:w-[340px] lg:min-w-[340px] xl:min-w-[calc(33.33%-2rem)] snap-start bg-gray-50/50 dark:bg-gray-800/30 p-5 md:p-6 rounded-[25px] md:rounded-[35px] border border-gray-100 dark:border-gray-700 transition-all hover:border-[#0B5694]/10 hover:shadow-xl hover:bg-white dark:hover:bg-gray-800/50">
                    <div class="flex justify-between items-start mb-6">
                        <div class="min-w-0 flex-1 pr-6">
                            <p class="text-[15px] font-extrabold text-gray-800 dark:text-gray-100 group-hover:text-[#0B5694] transition-colors uppercase tracking-normal truncate leading-tight">
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
</x-page-layout>
@endsection
