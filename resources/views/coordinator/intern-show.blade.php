@extends('layouts.app')

@section('title', $intern->first_name . ' ' . $intern->last_name)

@section('content')
<x-page-layout 
    portalType="School Coordinator" 
    userName="{{ Auth::user()->schoolCoordinatorDetails->first_name }}" 
    userRole="School Coordinator"
>
    <div x-data="internProfileManager" class="w-full text-[14px]">
        
        @php 
            $isCompleted = optional($intern->certificate)->status === 'published';
            $targetHours = $intern->required_hours ?? 480;
            $hourPercentage = $targetHours > 0 ? min(100, ($intern->hours_rendered / $targetHours) * 100) : 0; 
            $recentTasks = $intern->tasks()->latest()->take(5)->get();
            $recentAttendance = $attendanceHistory->take(5);
        @endphp

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-8">
            <div>
                <h1 class="text-xl md:text-3xl font-bold text-gray-800 dark:text-gray-100 tracking-tight uppercase">Student Profile</h1>
                <p class="text-sm md:text-base text-gray-400 dark:text-gray-500 font-medium italic mt-1">Detailed training progress and internship records</p>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="window.print()" class="flex items-center justify-center gap-2 px-5 py-2.5 bg-[#0B5694] dark:bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-750 dark:hover:bg-blue-700 transition shadow-sm w-fit uppercase tracking-widest active:scale-95">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                    Print Report
                </button>
                <a href="{{ route('coordinator.interns') }}" class="flex items-center justify-center gap-2 px-5 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition shadow-sm w-fit uppercase tracking-widest">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    BACK
                </a>
            </div>
        </div>

        {{-- Skeletons --}}
        <template x-if="loading">
            <div class="space-y-6">
                <x-skeleton class="h-64 w-full rounded-[25px]" />
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                    <x-skeleton class="h-64 rounded-[25px]" />
                    <x-skeleton class="h-64 rounded-[25px]" />
                </div>
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                    <x-skeleton class="h-80 rounded-[25px]" />
                    <x-skeleton class="h-80 rounded-[25px]" />
                </div>
            </div>
        </template>

        <div x-show="!loading" x-transition.opacity.duration.500ms class="space-y-6">
            {{-- Overview Section --}}
            <div class="bg-white dark:bg-gray-900 rounded-[25px] p-6 md:p-8 shadow-sm border border-gray-100 dark:border-gray-800">
                <p class="text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-8">Overview</p>
                
                <div class="flex flex-col md:flex-row gap-8 items-center md:items-start mb-10 text-center md:text-left">
                    <x-avatar :user="$intern" size="32" circular="false" border="4" textSize="4xl" />
                    
                    <div class="space-y-1 text-center md:text-left min-w-0 flex-1">
                        <h2 class="text-2xl md:text-3xl font-extrabold text-gray-900 dark:text-gray-100 leading-tight">{{ $intern->first_name }} {{ $intern->last_name }}</h2>
                        
                        <div class="flex flex-wrap items-center justify-center md:justify-start gap-x-4 gap-y-1 mt-2">
                            <p class="text-[11px] sm:text-xs text-gray-500 dark:text-gray-400 font-bold flex items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                                {{ $intern->user->email }}
                            </p>
                            <span class="w-1 h-1 bg-gray-300 dark:bg-gray-700 rounded-full hidden sm:block"></span>
                            <p class="text-[11px] sm:text-xs text-gray-500 dark:text-gray-400 font-bold flex items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                                {{ $intern->phone_number ?? 'No Phone' }}
                            </p>
                        </div>

                        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium pt-1">School: <span class="text-gray-700 dark:text-gray-200">{{ $intern->school }}</span></p>
                        <p class="text-[10px] text-gray-400 dark:text-gray-500 italic uppercase tracking-widest font-black">ID: {{ $intern->student_id ?? 'N/A' }}</p>
                        
                        <div class="flex gap-2 mt-4 justify-center md:justify-start">
                            @if($isCompleted)
                                <span class="px-4 py-1.5 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 text-[10px] font-black rounded-full border border-emerald-100 dark:border-emerald-900/30 uppercase tracking-widest">Completed</span>
                            @else
                                <span class="px-4 py-1.5 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 text-[10px] font-black rounded-full border border-blue-100 dark:border-blue-900/30 uppercase tracking-widest">Active</span>
                            @endif
                            <span class="px-4 py-1.5 bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-400 text-[10px] font-black rounded-full border border-gray-100 dark:border-gray-700 uppercase tracking-widest">Intern</span>
                        </div>
                    </div>
                </div>

                {{-- Stats Grid --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="p-6 bg-white dark:bg-gray-800/50 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm">
                        <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-2">Hours</p>
                        <p class="text-2xl md:text-3xl font-black text-[#0B5694] dark:text-blue-400">{{ (int)$intern->hours_rendered }}/{{ $targetHours }}</p>
                        <div class="w-full bg-gray-100 dark:bg-gray-800 h-2 rounded-full mt-4 overflow-hidden">
                            <div class="{{ $isCompleted ? 'bg-emerald-500 dark:bg-emerald-400' : 'bg-[#0B5694] dark:bg-blue-600' }} h-full transition-all duration-1000" style="width: {{ $hourPercentage }}%"></div>
                        </div>
                    </div>

                    <div class="p-6 bg-white dark:bg-gray-800/50 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm">
                        <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-2">Tasks</p>
                        <p class="text-2xl md:text-3xl font-black text-gray-800 dark:text-gray-100">{{ $completedTasks }}/{{ $totalTasks }}</p>
                        <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-4 font-bold uppercase">Completion</p>
                    </div>

                    <div class="p-6 bg-white dark:bg-gray-800/50 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm">
                        <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-2">Attendance</p>
                        <p class="text-2xl md:text-3xl font-black text-red-500 dark:text-red-400">{{ $absents }} Absent</p>
                        <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-4 font-bold uppercase">Total Absences</p>
                    </div>

                    <div class="p-6 bg-white dark:bg-gray-800/50 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm">
                        <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-2">Logs</p>
                        <p class="text-2xl md:text-3xl font-black text-orange-500 dark:text-orange-400">{{ $lates }} Late</p>
                        <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-4 font-bold uppercase">Punctuality</p>
                    </div>
                </div>
            </div>

            {{-- Info Section --}}
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-900 rounded-[25px] p-6 md:p-8 shadow-sm border border-gray-100 dark:border-gray-800">
                    <p class="text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-6">Internship Information</p>
                    <div class="rounded-2xl border border-gray-100 dark:border-gray-800 overflow-hidden">
                        <div class="overflow-x-auto custom-scrollbar">
                            <table class="w-full text-sm">
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                    <tr>
                                        <td class="bg-gray-50/50 dark:bg-gray-800/50 p-5 font-bold text-gray-400 dark:text-gray-500 uppercase text-[10px] w-1/3">Office</td>
                                        <td class="p-5 font-bold text-gray-800 dark:text-gray-100">{{ $intern->office->name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="bg-gray-50/50 dark:bg-gray-800/50 p-5 font-bold text-gray-400 dark:text-gray-500 uppercase text-[10px]">Supervisor</td>
                                        <td class="p-5 font-bold text-gray-800 dark:text-gray-100">{{ $supervisor ? $supervisor->first_name . ' ' . $supervisor->last_name : 'No Supervisor Assigned' }}</td>
                                    </tr>
                                    @if($schoolSchedule && $schoolSchedule->start_date)
                                    <tr>
                                        <td class="bg-gray-50/50 dark:bg-gray-800/50 p-5 font-bold text-[#0B5694] dark:text-blue-400 uppercase text-[10px]">Official Start</td>
                                        <td class="p-5 font-black text-gray-800 dark:text-gray-100 uppercase">{{ $schoolSchedule->start_date->format('F d, Y') }}</td>
                                    </tr>
                                    @if($schoolSchedule->target_end_date)
                                    <tr>
                                        <td class="bg-gray-50/50 dark:bg-gray-800/50 p-5 font-bold text-[#0B5694] dark:text-blue-400 uppercase text-[10px]">Target Finish</td>
                                        <td class="p-5 font-black text-gray-800 dark:text-gray-100 uppercase">{{ $schoolSchedule->target_end_date->format('F d, Y') }}</td>
                                    </tr>
                                    @endif
                                    @endif
                                    <tr>
                                        <td class="bg-gray-50/50 dark:bg-gray-800/50 p-5 font-bold text-gray-400 dark:text-gray-500 uppercase text-[10px]">Emergency</td>
                                        <td class="p-5 font-bold text-red-500 dark:text-red-400">
                                            {{ $intern->emergency_contact ?? 'Not Provided' }}
                                            @if($intern->emergency_contact_number)
                                                <span class="text-gray-400 dark:text-gray-600 mx-2">|</span>
                                                <span class="text-xs tracking-widest font-black uppercase text-gray-500 dark:text-gray-400">{{ $intern->emergency_contact_number }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="bg-gray-50/50 dark:bg-gray-800/50 p-5 font-bold text-gray-400 dark:text-gray-500 uppercase text-[10px]">Total Requirement</td>
                                        <td class="p-5 font-bold text-gray-800 dark:text-gray-100">{{ $targetHours }} Working Hours</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-900 rounded-[25px] p-6 md:p-8 shadow-sm border border-gray-100 dark:border-gray-800">
                    <p class="text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-6">Performance Summary</p>
                    
                    @if($intern->certificate && $intern->certificate->evaluation)
                        <div class="space-y-4 max-h-[220px] overflow-y-auto pr-2 custom-scrollbar">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-gray-50 dark:bg-gray-800/50 p-3.5 rounded-xl border border-gray-100/50 dark:border-gray-800/50">
                                    <span class="text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest block mb-1">Technical Proficiency</span>
                                    <span class="text-xs font-extrabold text-gray-800 dark:text-gray-100 uppercase">{{ $intern->certificate->evaluation->technical_proficiency }}</span>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-800/50 p-3.5 rounded-xl border border-gray-100/50 dark:border-gray-800/50">
                                    <span class="text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest block mb-1">Professionalism & Ethics</span>
                                    <span class="text-xs font-extrabold text-gray-800 dark:text-gray-100 uppercase">{{ $intern->certificate->evaluation->professionalism_ethics }}</span>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-800/50 p-3.5 rounded-xl border border-gray-100/50 dark:border-gray-800/50">
                                    <span class="text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest block mb-1">Communication Skills</span>
                                    <span class="text-xs font-extrabold text-gray-800 dark:text-gray-100 uppercase">{{ $intern->certificate->evaluation->communication_skills }}</span>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-800/50 p-3.5 rounded-xl border border-gray-100/50 dark:border-gray-800/50">
                                    <span class="text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest block mb-1">Team Collaboration</span>
                                    <span class="text-xs font-extrabold text-gray-800 dark:text-gray-100 uppercase">{{ $intern->certificate->evaluation->team_collaboration }}</span>
                                </div>
                            </div>
                            @if($intern->certificate->supervisor_feedback)
                                <div class="bg-blue-50/30 dark:bg-blue-950/10 p-4 rounded-xl border border-blue-100/50 dark:border-blue-900/30 mt-2">
                                    <span class="text-[9px] font-black text-[#0B5694] dark:text-blue-400 uppercase tracking-widest block mb-1">Supervisor's Feedback</span>
                                    <p class="text-xs font-bold text-gray-700 dark:text-gray-300 leading-relaxed italic">
                                        "{{ $intern->certificate->supervisor_feedback }}"
                                    </p>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="bg-gray-50/50 dark:bg-gray-800/50 rounded-2xl p-6 md:p-10 text-center flex flex-col items-center justify-center border border-dashed border-gray-200 dark:border-gray-700 h-auto min-h-[210px]">
                            <svg class="text-gray-300 dark:text-gray-600 mb-3" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4 9.5-9.5z"/></svg>
                            <p class="text-xs text-gray-400 dark:text-gray-500 italic font-medium px-4 md:px-10">Evaluation module pending. Performance metrics will sync once the assessment is submitted.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Recent Tables Section --}}
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-900 rounded-[25px] p-6 md:p-8 shadow-sm border border-gray-100 dark:border-gray-800">
                    <p class="text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-6">Recent Tasks</p>
                    <div class="rounded-2xl border border-gray-100 dark:border-gray-800 overflow-hidden">
                        <div class="overflow-x-auto custom-scrollbar">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 dark:bg-gray-800/50 text-gray-400 dark:text-gray-500 uppercase text-[10px] font-black">
                                    <tr><th class="py-4 px-6 text-left">Task</th><th class="py-4 px-6 text-right">Status</th></tr>
                                </thead>
                                <tbody class="text-gray-600 dark:text-gray-400 divide-y divide-gray-50 dark:divide-gray-800">
                                    @forelse($recentTasks as $task)
                                        <tr>
                                            <td class="py-4 px-6 text-left font-bold text-gray-700 dark:text-gray-200 whitespace-nowrap">{{ $task->title }}</td>
                                            <td class="py-4 px-6 text-right whitespace-nowrap">
                                                <span class="font-black uppercase text-[10px] {{ $task->status->value == 'completed' ? 'text-green-500 dark:text-emerald-400' : 'text-orange-400' }}">
                                                    {{ $task->status->label() }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="2" class="py-10 text-center text-gray-400 dark:text-gray-500 italic">No tasks found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-900 rounded-[25px] p-6 md:p-8 shadow-sm border border-gray-100 dark:border-gray-800">
                    <p class="text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-6">Attendance Snapshot</p>
                    <div class="rounded-2xl border border-gray-100 dark:border-gray-800 overflow-hidden">
                        <div class="overflow-x-auto custom-scrollbar">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 dark:bg-gray-800/50 text-gray-400 dark:text-gray-500 uppercase text-[10px] font-black">
                                    <tr><th class="py-4 px-6 text-left">Date</th><th class="py-4 px-6 text-center">Time</th><th class="py-4 px-6 text-right">Hours</th></tr>
                                </thead>
                                <tbody class="text-gray-600 dark:text-gray-400 divide-y divide-gray-50 dark:divide-gray-800">
                                    @forelse($recentAttendance as $log)
                                        <tr>
                                            <td class="py-4 px-6 text-left font-bold text-gray-700 dark:text-gray-200 whitespace-nowrap">
                                                {{ isset($log['clock_in']) && $log['clock_in'] ? \Carbon\Carbon::parse($log['clock_in'])->format('M d, Y') : (isset($log['date']) && $log['date'] ? \Carbon\Carbon::parse($log['date'])->format('M d, Y') : '--') }}
                                            </td>
                                            <td class="py-4 px-6 text-center text-[10px] font-black text-gray-400 dark:text-gray-500 whitespace-nowrap">
                                                @if(isset($log['clock_in']) && $log['clock_in'])
                                                    {{ \Carbon\Carbon::parse($log['clock_in'])->format('h:i A') }} - {{ (isset($log['clock_out']) && $log['clock_out']) ? \Carbon\Carbon::parse($log['clock_out'])->format('h:i A') : 'Active' }}
                                                @else
                                                    --
                                                @endif
                                            </td>
                                            <td class="py-4 px-6 text-right font-black uppercase text-[10px] {{ (isset($log['clock_out']) && $log['clock_out']) ? 'text-emerald-500 dark:text-emerald-400' : 'text-blue-500 dark:text-blue-400' }} whitespace-nowrap">
                                                {{ isset($log['duration']) && $log['duration'] !== null ? (float)$log['duration'] . ' HRS' : 'Pending' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="py-10 text-center text-gray-400 dark:text-gray-500 italic">No attendance records.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Journal Entries Section --}}
            <div class="bg-white dark:bg-gray-900 rounded-[25px] p-6 md:p-8 shadow-sm border border-gray-100 dark:border-gray-800">
                <div class="flex justify-between items-center mb-6">
                    <p class="text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Student Journals</p>
                    <span class="px-3 py-1 bg-gray-100 dark:bg-gray-800 rounded-full text-[10px] font-black text-gray-500 dark:text-slate-400 uppercase tracking-widest">
                        {{ $journals->count() }} Entries
                    </span>
                </div>

                <div class="space-y-6">
                    @forelse($journals as $journal)
                        <div class="p-6 bg-gray-50/50 dark:bg-gray-950/20 rounded-[20px] border border-gray-100 dark:border-gray-850">
                            <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800/80 pb-4 mb-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-xl bg-white dark:bg-gray-900 flex flex-col items-center justify-center border border-gray-150 dark:border-gray-800 shadow-sm">
                                        <span class="text-[9px] font-black text-gray-400 dark:text-slate-500 uppercase leading-none mb-0.5">{{ $journal->created_at->format('M') }}</span>
                                        <span class="text-base font-black text-gray-800 dark:text-gray-100 leading-none tracking-tight">{{ $journal->created_at->format('d') }}</span>
                                    </div>
                                    <div class="text-left">
                                        <h5 class="font-black text-gray-900 dark:text-gray-100 uppercase tracking-tight text-sm">
                                            {{ $journal->title }}
                                        </h5>
                                        <p class="text-[10px] text-gray-400 dark:text-slate-500 font-bold uppercase mt-0.5">
                                            Submitted {{ $journal->created_at->format('h:i A') }} • Week {{ $journal->created_at->weekOfMonth }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="p-5 bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 text-xs text-gray-700 dark:text-gray-300 leading-relaxed font-semibold">
                                {!! nl2br(e($journal->content)) !!}
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-gray-400 dark:text-gray-500 italic">No journals submitted yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-page-layout>
@endsection
