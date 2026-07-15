@extends('layouts.app')

@section('title', $intern->first_name . ' ' . $intern->last_name)

@section('content')
<div x-data="internProfileManager" class="flex min-h-screen bg-gray-50 dark:bg-gray-950 text-[14px] overflow-x-hidden">
    
    {{-- Sidebar (Left) --}}
    <x-sidebar portalType="Supervisor" userName="{{ Auth::user()->supervisorDetails->first_name }}" userRole="{{ Auth::user()->supervisorDetails->office->name }}" />

    {{-- Main Content (Middle) --}}
    <main class="flex-1 ml-0 lg:ml-64 mr-0 lg:mr-80 p-3 sm:p-4 md:p-6 lg:p-10 pt-20 lg:pt-10 transition-all duration-300">
        
        @php 
            $isCompleted = optional($intern->certificate)->status === 'published';
            $targetHours = $intern->required_hours ?? 480;
            $hourPercentage = $targetHours > 0 ? min(100, ($intern->hours_rendered / $targetHours) * 100) : 0; 
        @endphp

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-8">
            <div>
                <h1 class="text-xl md:text-3xl font-bold text-gray-800 dark:text-gray-100 tracking-tight uppercase">Intern Profile</h1>
                <p class="text-sm md:text-base text-gray-400 dark:text-gray-500 font-medium italic mt-1">Detailed performance and internship records</p>
            </div>
            <a href="{{ route('supervisor.interns') }}" class="flex items-center justify-center gap-2 px-5 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition shadow-sm w-fit uppercase tracking-widest">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                BACK
            </a>
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

        <div x-show="!loading" x-transition.opacity.duration.500ms>
            {{-- Overview Section --}}
            <div class="bg-white dark:bg-gray-900 rounded-[25px] p-4 md:p-8 shadow-sm border border-gray-100 dark:border-gray-800 mb-4 md:mb-6">
                <p class="text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-4 md:mb-8">Overview</p>
                
                <div class="flex flex-col sm:flex-row gap-4 md:gap-8 items-center sm:items-start mb-6 md:mb-10 text-center sm:text-left">
                    <x-avatar :user="$intern" size="32" circular="false" border="4" textSize="4xl" />
                    
                    <div class="space-y-1 text-center sm:text-left min-w-0 flex-1">
                        <h2 class="text-xl md:text-3xl font-extrabold text-gray-900 dark:text-gray-100 leading-tight">{{ $intern->first_name }} {{ $intern->last_name }}</h2>
                        
                        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-x-3 md:gap-x-4 gap-y-1 mt-2">
                            <p class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400 font-bold flex items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                                <span class="hidden sm:inline">{{ $intern->user->email }}</span>
                                <span class="sm:hidden truncate max-w-[180px]">{{ $intern->user->email }}</span>
                            </p>
                            <span class="w-1 h-1 bg-gray-300 dark:bg-gray-700 rounded-full hidden sm:block"></span>
                            <p class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400 font-bold flex items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                                {{ $intern->phone_number ?? 'No Phone' }}
                            </p>
                        </div>

                        <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400 font-medium pt-1">School: <span class="text-gray-700 dark:text-gray-200">{{ $intern->school }}</span></p>
                        <p class="text-[10px] text-gray-400 dark:text-gray-500 italic uppercase tracking-widest font-black">ID: {{ $intern->student_id ?? 'N/A' }}</p>
                        
                        <div class="flex gap-2 mt-4 justify-center sm:justify-start">
                            @if($isCompleted)
                                <span class="px-3 md:px-4 py-1.5 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 text-[10px] font-black rounded-full border border-emerald-100 dark:border-emerald-900/30 uppercase tracking-widest">Completed</span>
                            @else
                                <span class="px-3 md:px-4 py-1.5 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 text-[10px] font-black rounded-full border border-blue-100 dark:border-blue-900/30 uppercase tracking-widest">Active</span>
                            @endif
                            <span class="px-3 md:px-4 py-1.5 bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-400 text-[10px] font-black rounded-full border border-gray-100 dark:border-gray-700 uppercase tracking-widest">Intern</span>
                        </div>
                    </div>
                </div>

                {{-- Stats Grid --}}
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4">
                    <div class="p-4 md:p-6 bg-white dark:bg-gray-800/50 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm">
                        <p class="text-[9px] md:text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1 md:mb-2">Hours</p>
                        <p class="text-lg md:text-3xl font-black text-[#0B5694] dark:text-blue-400">{{ (int)$intern->hours_rendered }}/{{ $intern->required_hours ?? 480 }}</p>
                        <div class="w-full bg-gray-100 dark:bg-gray-800 h-2 rounded-full mt-3 md:mt-4 overflow-hidden">
                            <div class="{{ $isCompleted ? 'bg-emerald-500 dark:bg-emerald-400' : 'bg-[#0B5694] dark:bg-blue-600' }} h-full transition-all duration-1000" style="width: {{ $hourPercentage }}%"></div>
                        </div>
                    </div>

                    <div class="p-4 md:p-6 bg-white dark:bg-gray-800/50 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm">
                        <p class="text-[9px] md:text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1 md:mb-2">Tasks</p>
                        <p class="text-lg md:text-3xl font-black text-gray-800 dark:text-gray-100">{{ $completedTasks }}/{{ $totalTasks }}</p>
                        <p class="text-[9px] md:text-[10px] text-gray-400 dark:text-gray-500 mt-3 md:mt-4 font-bold uppercase">Completion</p>
                    </div>

                    <div class="p-4 md:p-6 bg-white dark:bg-gray-800/50 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm">
                        <p class="text-[9px] md:text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1 md:mb-2">Attendance</p>
                        <p class="text-lg md:text-3xl font-black text-red-500 dark:text-red-400">{{ $absents }} Absent</p>
                        <p class="text-[9px] md:text-[10px] text-gray-400 dark:text-gray-500 mt-3 md:mt-4 font-bold uppercase">Total Absences</p>
                    </div>

                    <div class="p-4 md:p-6 bg-white dark:bg-gray-800/50 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm">
                        <p class="text-[9px] md:text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1 md:mb-2">Logs</p>
                        <p class="text-lg md:text-3xl font-black text-orange-500 dark:text-orange-400">{{ $intern->attendances->where('status', 'late')->count() }} Late</p>
                        <p class="text-[9px] md:text-[10px] text-gray-400 dark:text-gray-500 mt-3 md:mt-4 font-bold uppercase">Punctuality</p>
                    </div>
                </div>
            </div>

            {{-- Info Section --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6 mb-4 md:mb-6">
                <div class="bg-white dark:bg-gray-900 rounded-[25px] p-4 md:p-8 shadow-sm border border-gray-100 dark:border-gray-800">
                    <p class="text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-4 md:mb-6">Internship Information</p>
                    <div class="rounded-2xl border border-gray-100 dark:border-gray-800 overflow-hidden">
                        <div class="overflow-x-auto custom-scrollbar">
                            <table class="w-full text-sm">
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                    <tr class="flex flex-col sm:table-row">
                                        <td class="bg-gray-50/50 dark:bg-gray-800/50 p-3 md:p-5 font-bold text-gray-400 dark:text-gray-500 uppercase text-[10px] sm:w-1/3">Office</td>
                                        <td class="p-3 md:p-5 font-bold text-gray-800 dark:text-gray-100 text-xs md:text-sm">{{ $intern->office->name }}</td>
                                    </tr>
                                    @if($schoolSchedule && $schoolSchedule->start_date)
                                    <tr class="flex flex-col sm:table-row">
                                        <td class="bg-gray-50/50 dark:bg-gray-800/50 p-3 md:p-5 font-bold text-[#0B5694] dark:text-blue-400 uppercase text-[10px] sm:w-1/3">Official Start</td>
                                        <td class="p-3 md:p-5 font-black text-gray-800 dark:text-gray-100 uppercase text-xs md:text-sm">{{ $schoolSchedule->start_date->format('F d, Y') }}</td>
                                    </tr>
                                    <tr class="flex flex-col sm:table-row">
                                        <td class="bg-gray-50/50 dark:bg-gray-800/50 p-3 md:p-5 font-bold text-[#0B5694] dark:text-blue-400 uppercase text-[10px] sm:w-1/3">Target Finish Date</td>
                                        <td class="p-3 md:p-5 font-black text-gray-800 dark:text-gray-100 uppercase text-xs md:text-sm">
                                             {{ $intern->end_date ? \Carbon\Carbon::parse($intern->end_date)->format('F d, Y') : ($schoolSchedule->target_end_date ? $schoolSchedule->target_end_date->format('F d, Y') : 'Not Set') }}
                                        </td>
                                    </tr>
                                    @endif
                                    @php
                                        $pendingExtension = \App\Models\InternExtension::where('intern_detail_id', $intern->id)
                                            ->where('status', 'pending')
                                            ->first();
                                        $hasApprovedExtension = \App\Models\InternExtension::where('intern_detail_id', $intern->id)
                                            ->where('status', 'approved')
                                            ->exists();
                                    @endphp
                                    @if($hasApprovedExtension)
                                    <tr class="flex flex-col sm:table-row bg-rose-50/30 dark:bg-rose-950/10">
                                        <td class="bg-rose-50/50 dark:bg-rose-950/20 p-3 md:p-5 font-bold text-rose-600 dark:text-rose-400 uppercase text-[10px] sm:w-1/3">Extension Limit</td>
                                        <td class="p-3 md:p-5 text-rose-700 dark:text-rose-455 text-xs md:text-sm font-black uppercase tracking-wider">
                                            Max Extensions Reached
                                        </td>
                                    </tr>
                                    @endif
                                    @if($pendingExtension)
                                    <tr class="flex flex-col sm:table-row bg-amber-50/30 dark:bg-amber-950/10">
                                        <td class="bg-amber-50/50 dark:bg-amber-950/20 p-3 md:p-5 font-bold text-amber-600 dark:text-amber-400 uppercase text-[10px] sm:w-1/3">Extension Pending</td>
                                        <td class="p-3 md:p-5 text-gray-850 dark:text-gray-100 text-xs md:text-sm">
                                            <span class="font-black text-amber-600 dark:text-amber-400 block sm:inline">Proposed Finish:</span> {{ \Carbon\Carbon::parse($pendingExtension->new_end_date)->format('F d, Y') }}
                                            <p class="text-[11px] text-gray-400 dark:text-gray-500 font-bold uppercase tracking-wider mt-1 leading-normal">Reason: {{ $pendingExtension->reason }}</p>
                                        </td>
                                    </tr>
                                    @endif
                                    <tr class="flex flex-col sm:table-row">
                                        <td class="bg-gray-50/50 dark:bg-gray-800/50 p-3 md:p-5 font-bold text-gray-400 dark:text-gray-500 uppercase text-[10px] sm:w-1/3">Emergency</td>
                                        <td class="p-3 md:p-5 font-bold text-red-500 dark:text-red-400 text-xs md:text-sm">
                                            {{ $intern->emergency_contact ?? 'Not Provided' }}
                                            @if($intern->emergency_contact_number)
                                                <span class="text-gray-400 dark:text-gray-600 mx-2">|</span>
                                                <span class="text-xs tracking-widest font-black uppercase text-gray-500 dark:text-gray-400">{{ $intern->emergency_contact_number }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr class="flex flex-col sm:table-row">
                                        <td class="bg-gray-50/50 dark:bg-gray-800/50 p-3 md:p-5 font-bold text-gray-400 dark:text-gray-500 uppercase text-[10px] sm:w-1/3">Total Requirement</td>
                                        <td class="p-3 md:p-5 font-bold text-gray-800 dark:text-gray-100 text-xs md:text-sm">{{ $intern->required_hours ?? 480 }} Working Hours</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Extension Request Action --}}
                    @if(!$isCompleted && !$pendingExtension && !$hasApprovedExtension && $intern->hours_rendered < $targetHours)
                        <div x-data="{ openRequest: false }" class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800">
                            <button @click="openRequest = !openRequest" type="button" class="w-full px-5 py-3 bg-[#0B5694] hover:bg-[#0B5694]/90 text-white font-bold text-xs uppercase tracking-widest rounded-xl transition active:scale-95 text-center flex items-center justify-center gap-2 cursor-pointer shadow-md">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="M12 14v6"/><path d="M9 17h6"/></svg>
                                Request Date Extension
                            </button>
                            
                            <div x-show="openRequest" x-collapse x-cloak class="mt-4 p-4 bg-gray-50 dark:bg-gray-800/30 border border-gray-100 dark:border-gray-800 rounded-xl space-y-4">
                                <form action="{{ route('supervisor.interns.extend', $intern->user) }}" method="POST" class="space-y-4">
                                    @csrf
                                    <div>
                                        <label class="block text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1.5">New Expected End Date</label>
                                        <input type="date" name="new_end_date" required min="{{ date('Y-m-d', strtotime('+1 day')) }}" value="{{ $projectedEndDate ? $projectedEndDate->format('Y-m-d') : ($intern->end_date ?? ($schoolSchedule && $schoolSchedule->target_end_date ? $schoolSchedule->target_end_date->format('Y-m-d') : '')) }}"
                                            class="w-full bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-xs focus:ring-1 focus:ring-[#0B5694] focus:outline-none font-semibold text-gray-700 dark:text-gray-300">
                                        <span class="text-[10px] text-gray-400 block mt-1.5">Recommended base date on remaining hours: <span class="font-bold text-[#0B5694] dark:text-blue-400">{{ $projectedEndDate ? $projectedEndDate->format('M d, Y') : 'N/A' }}</span></span>
                                    </div>
                                    <div>
                                        <label class="block text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1.5">Reason for Extension</label>
                                        <textarea name="reason" required placeholder="Briefly describe the reason for the extension request (e.g. absences, suspension, school requirement changes)..." rows="3"
                                            class="w-full bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-xs focus:ring-1 focus:ring-[#0B5694] focus:outline-none font-semibold text-gray-700 dark:text-gray-300 placeholder-gray-400 dark:placeholder-gray-500"></textarea>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button type="submit" class="flex-1 px-4 py-2.5 bg-[#0B5694] hover:bg-[#0B5694]/90 text-white font-bold text-[10px] uppercase tracking-widest rounded-lg transition active:scale-95 text-center cursor-pointer shadow-sm">Submit Request</button>
                                        <button @click="openRequest = false" type="button" class="px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-250 dark:border-gray-700 text-gray-650 dark:text-gray-300 font-bold text-[10px] uppercase tracking-widest rounded-lg hover:bg-gray-50 transition active:scale-95 text-center cursor-pointer">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="bg-white dark:bg-gray-900 rounded-[25px] p-4 md:p-8 shadow-sm border border-gray-100 dark:border-gray-800">
                    <p class="text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-4 md:mb-6">Performance Summary</p>
                    
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
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6">
                <div class="bg-white dark:bg-gray-900 rounded-[25px] p-4 md:p-8 shadow-sm border border-gray-100 dark:border-gray-800">
                    <p class="text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-4 md:mb-6">Recent Tasks</p>
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

                <div class="bg-white dark:bg-gray-900 rounded-[25px] p-4 md:p-8 shadow-sm border border-gray-100 dark:border-gray-800">
                    <p class="text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-4 md:mb-6">Attendance Snapshot</p>
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
        </div>
    </main>

    {{-- Right Sidebar --}}
    <x-notification-sidebar :notifications="$notifications" />
</div>
@endsection