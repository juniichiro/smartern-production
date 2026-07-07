@extends('layouts.app')

@section('title', 'My Certificate')

@section('content')
<div class="flex min-h-screen bg-gray-50 dark:bg-gray-950 font-sans text-[14px]">
    {{-- SIDEBAR --}}
    <x-sidebar portalType="Intern" 
               userName="{{ Auth::user()->internDetails->first_name ?? 'Intern' }}" 
               userRole="{{ Auth::user()->internDetails->office->name ?? 'Office' }}"/>

    {{-- MAIN CONTENT --}}
    <main class="flex-1 ml-0 lg:ml-64 mr-0 lg:mr-80 p-4 md:p-6 lg:p-10 pt-20 lg:pt-10 text-left">
        {{-- Header & Progress tracking --}}
        <header class="mb-12 flex flex-col md:flex-row justify-between items-start md:items-end gap-6 shrink-0">
            <div>
                <h2 class="text-2xl md:text-4xl font-extrabold text-gray-900 dark:text-gray-100 tracking-normal uppercase leading-tight">Certificate Tracker</h2>
                <p class="text-[10px] md:text-[11px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-[0.3em] mt-1">Official Internship Completion Tracking</p>
            </div>
        </header>

        {{-- Meta Data Cards --}}
        @php
            $internDetails = Auth::user()->internDetails;
            $hasReachedHours = $internDetails && ($internDetails->hours_rendered >= $internDetails->required_hours);
            
            // Resolve tracker status
            $status = 'not_initiated';
            if ($certificate) {
                $status = $certificate->status; // 'pending', 'endorsed', 'published'
            } elseif ($hasReachedHours) {
                $status = 'hours_completed';
            }

            // Step Preparation (Active if hours met but draft not created yet; Completed if draft is created)
            $step1Active = ($status === 'hours_completed');
            $step1Completed = in_array($status, ['pending', 'endorsed', 'published']);

            // Step Supervisor Endorsement (Active if draft is created but not yet endorsed; Completed if endorsed or published)
            $step2Active = ($status === 'pending');
            $step2Completed = in_array($status, ['endorsed', 'published']);

            // Step Head Supervisor Approval (Active if endorsed; Completed if published)
            $step3Active = ($status === 'endorsed');
            $step3Completed = ($status === 'published');

            // Desktop connecting line percentage
            $stepPercent = 0;
            if ($status === 'hours_completed') $stepPercent = 15;
            elseif ($status === 'pending') $stepPercent = 50;
            elseif ($status === 'endorsed') $stepPercent = 83;
            elseif ($status === 'published') $stepPercent = 100;
        @endphp

        {{-- Meta Data Cards (Double-Bezel Architecture) --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-12">
            {{-- Card 1 --}}
            <div class="bg-gray-100/40 dark:bg-gray-800/10 p-1.5 rounded-[24px] border border-gray-200/40 dark:border-gray-800/40 transition-all duration-700 ease-[cubic-bezier(0.32,0.72,0,1)] hover:scale-[1.01] hover:shadow-lg hover:shadow-gray-500/5">
                <div class="bg-white dark:bg-gray-900 p-6 rounded-[calc(24px-6px)] border border-gray-100 dark:border-gray-800/50 text-center shadow-[inset_0_1px_1px_rgba(255,255,255,0.2)] dark:shadow-[inset_0_1px_1px_rgba(255,255,255,0.05)]">
                    <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em] mb-2">Certificate ID</p>
                    <p class="text-base font-extrabold text-gray-800 dark:text-gray-100 font-mono tracking-wide uppercase">{{ $certificate->verification_code ?? 'PENDING' }}</p>
                </div>
            </div>

            {{-- Card 2 --}}
            <div class="bg-gray-100/40 dark:bg-gray-800/10 p-1.5 rounded-[24px] border border-gray-200/40 dark:border-gray-800/40 transition-all duration-700 ease-[cubic-bezier(0.32,0.72,0,1)] hover:scale-[1.01] hover:shadow-lg hover:shadow-gray-500/5">
                <div class="bg-white dark:bg-gray-900 p-6 rounded-[calc(24px-6px)] border border-gray-100 dark:border-gray-800/50 text-center shadow-[inset_0_1px_1px_rgba(255,255,255,0.2)] dark:shadow-[inset_0_1px_1px_rgba(255,255,255,0.05)]">
                    <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em] mb-2">Training Hours</p>
                    <p class="text-base font-extrabold text-gray-800 dark:text-gray-100 tracking-tight">
                        {{ (float)($internDetails->hours_rendered ?? 0) }}
                    </p>
                </div>
            </div>

            {{-- Card 3 --}}
            <div class="bg-gray-100/40 dark:bg-gray-800/10 p-1.5 rounded-[24px] border border-gray-200/40 dark:border-gray-800/40 transition-all duration-700 ease-[cubic-bezier(0.32,0.72,0,1)] hover:scale-[1.01] hover:shadow-lg hover:shadow-gray-500/5">
                <div class="bg-white dark:bg-gray-900 p-6 rounded-[calc(24px-6px)] border border-gray-100 dark:border-gray-800/50 text-center shadow-[inset_0_1px_1px_rgba(255,255,255,0.2)] dark:shadow-[inset_0_1px_1px_rgba(255,255,255,0.05)]">
                    <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em] mb-2">Current Status</p>
                    @if($status === 'published')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-50 text-green-700 border border-green-200/60 dark:bg-green-950/20 dark:text-green-400 dark:border-green-800/30 uppercase tracking-wider">Ready for Pickup</span>
                    @elseif($status === 'endorsed')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200/60 dark:bg-blue-950/20 dark:text-blue-400 dark:border-blue-800/30 uppercase tracking-wider">Awaiting Approval</span>
                    @elseif($status === 'pending')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200/60 dark:bg-amber-950/20 dark:text-amber-400 dark:border-amber-800/30 uppercase tracking-wider">Under Review</span>
                    @elseif($status === 'hours_completed')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200/60 dark:bg-indigo-950/20 dark:text-indigo-400 dark:border-indigo-800/30 uppercase tracking-wider">Awaiting Draft</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-50 text-gray-500 border border-gray-200/60 dark:bg-gray-900/50 dark:text-gray-400 dark:border-gray-800/50 uppercase tracking-wider">In Progress</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Progress Tracking Card (Double Bezel & Premium Spacing) --}}
        <div class="bg-gray-100/40 dark:bg-gray-800/10 p-2 md:p-3 rounded-[32px] border border-gray-200/40 dark:border-gray-800/40 shadow-[0_30px_60px_rgba(0,0,0,0.02)] dark:shadow-none max-w-4xl mx-auto mb-10 transition-all duration-700 ease-[cubic-bezier(0.32,0.72,0,1)]">
            <div class="bg-white dark:bg-gray-900 p-8 md:p-12 rounded-[calc(32px-10px)] border border-gray-100 dark:border-gray-800/50 shadow-[inset_0_1px_1px_rgba(255,255,255,0.2)] dark:shadow-[inset_0_1px_1px_rgba(255,255,255,0.05)]">
                <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100 uppercase tracking-[0.2em] mb-12 text-center">Certificate Timeline</h3>

                {{-- Stepper Progress Bar --}}
                <div class="relative flex flex-col md:flex-row justify-between items-start md:items-center gap-10 md:gap-4 mb-14">
                    {{-- Connecting line (desktop) --}}
                    <div class="absolute top-1/2 left-0 right-0 h-[3px] bg-gray-100 dark:bg-gray-800 -translate-y-1/2 hidden md:block z-0"></div>
                    
                    {{-- Active indicator line (desktop) --}}
                    <div class="absolute top-1/2 left-0 h-[3px] bg-[#0B5694] -translate-y-1/2 hidden md:block transition-all duration-700 ease-[cubic-bezier(0.32,0.72,0,1)] z-0" style="width: {{ $stepPercent }}%;"></div>

                    {{-- Step 1 --}}
                    <div class="relative z-10 flex flex-row md:flex-col items-center md:text-center gap-4 md:gap-0 w-full md:w-auto">
                        <div class="w-14 h-14 rounded-full flex items-center justify-center p-1 transition-all duration-700 ease-[cubic-bezier(0.32,0.72,0,1)]
                            {{ $step1Completed ? 'bg-blue-50/50 dark:bg-blue-900/10 border border-blue-200/50 dark:border-blue-800/20' : ($step1Active ? 'bg-blue-500/20 dark:bg-blue-900/30 border border-[#0B5694] animate-pulse' : 'bg-gray-100 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700') }}">
                            <div class="w-full h-full rounded-full flex items-center justify-center font-extrabold text-sm transition-all duration-300
                                {{ ($step1Active || $step1Completed) ? 'bg-[#0B5694] text-white shadow-md shadow-blue-500/20' : 'bg-gray-50 dark:bg-gray-900 text-gray-400' }}">
                                @if($step1Completed)
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                @else
                                    1
                                @endif
                            </div>
                        </div>
                        <div class="text-left md:text-center mt-0 md:mt-4">
                            <p class="text-xs font-extrabold uppercase tracking-wider {{ ($step1Active || $step1Completed) ? 'text-gray-800 dark:text-gray-200' : 'text-gray-400' }}">1. Preparation</p>
                            <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-0.5 max-w-[170px]">Completion profile reviewed & details drafted</p>
                        </div>
                    </div>

                    {{-- Step 2 --}}
                    <div class="relative z-10 flex flex-row md:flex-col items-center md:text-center gap-4 md:gap-0 w-full md:w-auto">
                        <div class="w-14 h-14 rounded-full flex items-center justify-center p-1 transition-all duration-700 ease-[cubic-bezier(0.32,0.72,0,1)]
                            {{ $step2Completed ? 'bg-blue-50/50 dark:bg-blue-900/10 border border-blue-200/50 dark:border-blue-800/20' : ($step2Active ? 'bg-blue-500/20 dark:bg-blue-900/30 border border-[#0B5694] animate-pulse' : 'bg-gray-100 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700') }}">
                            <div class="w-full h-full rounded-full flex items-center justify-center font-extrabold text-sm transition-all duration-300
                                {{ ($step2Active || $step2Completed) ? 'bg-[#0B5694] text-white shadow-md shadow-blue-500/20' : 'bg-gray-50 dark:bg-gray-900 text-gray-400' }}">
                                @if($step2Completed)
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                @else
                                    2
                                @endif
                            </div>
                        </div>
                        <div class="text-left md:text-center mt-0 md:mt-4">
                            <p class="text-xs font-extrabold uppercase tracking-wider {{ ($step2Active || $step2Completed) ? 'text-gray-800 dark:text-gray-200' : 'text-gray-400' }}">2. Endorsement</p>
                            <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-0.5 max-w-[170px]">Endorsed by Office Supervisor to Ms. Michelle A. Mal-in</p>
                        </div>
                    </div>

                    {{-- Step 3 --}}
                    <div class="relative z-10 flex flex-row md:flex-col items-center md:text-center gap-4 md:gap-0 w-full md:w-auto">
                        <div class="w-14 h-14 rounded-full flex items-center justify-center p-1 transition-all duration-700 ease-[cubic-bezier(0.32,0.72,0,1)]
                            {{ $step3Completed ? 'bg-green-100/50 dark:bg-green-950/20 border border-green-200/50 dark:border-green-800/30' : ($step3Active ? 'bg-blue-500/20 dark:bg-blue-900/30 border border-[#0B5694] animate-pulse' : 'bg-gray-100 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700') }}">
                            <div class="w-full h-full rounded-full flex items-center justify-center font-extrabold text-sm transition-all duration-300
                                {{ $step3Completed ? 'bg-green-600 text-white shadow-md shadow-green-500/20' : ($step3Active ? 'bg-[#0B5694] text-white shadow-md shadow-blue-500/20' : 'bg-gray-50 dark:bg-gray-900 text-gray-400') }}">
                                @if($step3Completed)
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                @else
                                    3
                                @endif
                            </div>
                        </div>
                        <div class="text-left md:text-center mt-0 md:mt-4">
                            <p class="text-xs font-extrabold uppercase tracking-wider {{ ($step3Active || $step3Completed) ? 'text-gray-800 dark:text-gray-200' : 'text-gray-400' }}">3. Physical Pickup</p>
                            <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-0.5 max-w-[170px]">Approved, signed & ready to collect physically</p>
                        </div>
                    </div>
                </div>

                {{-- Status Guidance Alert Panel (Refined Layout with Double-Bezel Inside) --}}
                <div class="p-1 rounded-2xl border border-dashed transition duration-700 ease-[cubic-bezier(0.32,0.72,0,1)]
                    @if($status === 'not_initiated')
                        bg-gray-50/50 dark:bg-gray-950/20 border-gray-200 dark:border-gray-800 text-gray-600 dark:text-gray-400
                    @elseif($status === 'hours_completed')
                        bg-blue-50/30 dark:bg-blue-950/5 border-blue-200/60 dark:border-blue-900/30 text-blue-800 dark:text-blue-300
                    @elseif($status === 'pending')
                        bg-amber-50/40 dark:bg-amber-950/5 border-amber-200/60 dark:border-amber-900/30 text-amber-800 dark:text-amber-300
                    @elseif($status === 'endorsed')
                        bg-blue-50/40 dark:bg-blue-950/5 border-blue-200/60 dark:border-blue-900/30 text-blue-800 dark:text-blue-300
                    @elseif($status === 'published')
                        bg-green-50/30 dark:bg-green-950/5 border-green-200/60 dark:border-green-900/30 text-green-800 dark:text-green-300
                    @endif">
                    
                    <div class="p-6 rounded-[calc(1rem-2px)] text-center">
                        @if($status === 'not_initiated')
                            <div class="flex flex-col items-center">
                                <svg class="w-10 h-10 text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                </svg>
                                <h4 class="font-bold text-xs uppercase tracking-[0.15em] text-gray-700 dark:text-gray-300 mb-1">Internship In Progress</h4>
                                <p class="text-xs text-gray-400 dark:text-gray-500 max-w-md mx-auto leading-relaxed">
                                    You have rendered <strong class="text-gray-800 dark:text-white">{{ $internDetails->hours_rendered ?? 0 }}</strong> of your required <strong class="text-gray-800 dark:text-white">{{ $internDetails->required_hours ?? 0 }} hours</strong>. The official certificate timeline starts automatically once target hours are completed.
                                </p>
                            </div>
                        @elseif($status === 'hours_completed')
                            <div class="flex flex-col items-center">
                                <svg class="w-10 h-10 text-[#0B5694] mb-3 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <h4 class="font-bold text-xs uppercase tracking-[0.15em] text-[#0B5694] mb-1">Hours Rendered — Awaiting Draft</h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400 max-w-md mx-auto leading-relaxed">
                                    Incredible job! You have fully rendered your required hours ({{ $internDetails->hours_rendered }} hrs). Your Office Supervisor is currently gathering evaluation stats to formulate the certificate draft.
                                </p>
                            </div>
                        @elseif($status === 'pending')
                            <div class="flex flex-col items-center">
                                <svg class="w-10 h-10 text-amber-500 mb-3 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                                </svg>
                                <h4 class="font-bold text-xs uppercase tracking-[0.15em] text-amber-800 dark:text-amber-200 mb-1">Under Office Review</h4>
                                <p class="text-xs text-amber-600/80 dark:text-amber-400/70 max-w-md mx-auto leading-relaxed">
                                    Your details are verified. The draft is being formatted and evaluated by your Office Supervisor. Once approved locally, it moves to supervisor endorsement.
                                </p>
                            </div>
                        @elseif($status === 'endorsed')
                            <div class="flex flex-col items-center">
                                <svg class="w-10 h-10 text-blue-500 mb-3 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <h4 class="font-bold text-xs uppercase tracking-[0.15em] text-blue-800 dark:text-blue-200 mb-1">Endorsed & Awaiting Approval</h4>
                                <p class="text-xs text-blue-600/80 dark:text-blue-400/70 max-w-md mx-auto leading-relaxed">
                                    Excellent! Your supervisor has endorsed your completion certificate. It has been securely sent to the Head Supervisor, <span class="font-bold text-blue-900 dark:text-blue-300">Ms. Michelle A. Mal-in</span>, for review and physical signing.
                                </p>
                            </div>
                        @elseif($status === 'published')
                            <div class="flex flex-col items-center">
                                <div class="w-14 h-14 bg-green-500/10 dark:bg-green-950/20 rounded-full flex items-center justify-center text-green-600 mb-4 border border-green-200/50 dark:border-green-900/30 shadow-sm shadow-green-500/5">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <h4 class="font-bold text-sm uppercase tracking-[0.15em] text-green-800 dark:text-green-300 mb-2">Ready for Physical Pickup!</h4>
                                <p class="text-xs text-green-700/80 dark:text-green-400/70 max-w-lg mx-auto mb-6">
                                    Your official signed Certificate of Completion has been finalized and published.
                                </p>
                                
                                {{-- Double bezel inner card for instructions --}}
                                <div class="bg-gray-50/50 dark:bg-gray-900/50 p-1.5 rounded-2xl border border-gray-100 dark:border-gray-800/80 max-w-md w-full">
                                    <div class="bg-white dark:bg-gray-900 p-5 rounded-[calc(1rem-2px)] border border-gray-200/40 dark:border-gray-800/60 shadow-sm text-left">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold bg-green-50 text-green-700 dark:bg-green-950/20 dark:text-green-400 uppercase tracking-widest mb-3">Directions</span>
                                        <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed">
                                            Please coordinate with the office of the Head Supervisor, <strong class="text-gray-900 dark:text-white font-extrabold">Ms. Michelle A. Mal-in</strong>, at the Schools Division Office Quezon City to claim your physical copy.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </main>

    <x-notification-sidebar :notifications="$notifications" />
</div>
@endsection
