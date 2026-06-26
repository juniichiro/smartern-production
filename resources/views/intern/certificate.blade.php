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
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-12">
            <div class="bg-white dark:bg-gray-900 p-6 rounded-[20px] shadow-sm border border-gray-100 dark:border-gray-800 text-center">
                <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Certificate ID</p>
                <p class="text-lg font-black text-gray-800 dark:text-gray-100 font-mono">{{ $certificate->verification_code ?? 'PENDING' }}</p>
            </div>
            <div class="bg-white dark:bg-gray-900 p-6 rounded-[20px] shadow-sm border border-gray-100 dark:border-gray-800 text-center">
                <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Training Hours</p>
                <p class="text-lg font-black text-gray-800 dark:text-gray-100 tracking-tight">{{ $certificate->completed_hours ?? 0 }} Hours</p>
            </div>
            <div class="bg-white dark:bg-gray-900 p-6 rounded-[20px] shadow-sm border border-gray-100 dark:border-gray-800 text-center">
                <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Status</p>
                @if($certificate && $certificate->status === 'published')
                    <p class="text-lg font-black text-green-600 dark:text-green-500 uppercase tracking-wider">Ready for Pickup</p>
                @elseif($certificate && $certificate->status === 'endorsed')
                    <p class="text-lg font-black text-blue-500 uppercase tracking-wider">Awaiting Approval</p>
                @elseif($certificate && $certificate->status === 'pending')
                    <p class="text-lg font-black text-amber-500 uppercase tracking-wider">Under Review</p>
                @else
                    <p class="text-lg font-black text-gray-500 uppercase tracking-wider">Not Initiated</p>
                @endif
            </div>
        </div>

        {{-- Progress Tracking Card --}}
        <div class="bg-white dark:bg-gray-900 p-8 rounded-[24px] shadow-md border border-gray-100 dark:border-gray-800 max-w-4xl mx-auto mb-10">
            <h3 class="text-lg font-black text-gray-800 dark:text-gray-100 uppercase tracking-wider mb-8 text-center">Certificate Processing Status</h3>

            {{-- Stepper Progress Bar --}}
            <div class="relative flex flex-col md:flex-row justify-between items-center gap-8 md:gap-4 mb-12">
                {{-- Connecting line (desktop) --}}
                <div class="absolute top-1/2 left-0 right-0 h-1 bg-gray-200 dark:bg-gray-800 -translate-y-1/2 hidden md:block z-0"></div>
                
                {{-- Active indicator line (desktop) --}}
                @php
                    $stepPercent = 0;
                    if ($certificate) {
                        if ($certificate->status === 'pending') $stepPercent = 25;
                        elseif ($certificate->status === 'endorsed') $stepPercent = 60;
                        elseif ($certificate->status === 'published') $stepPercent = 100;
                    }
                @endphp
                <div class="absolute top-1/2 left-0 h-1 bg-[#0B5694] -translate-y-1/2 hidden md:block transition-all duration-500 z-0" style="width: {{ $stepPercent }}%;"></div>

                {{-- Step 1 --}}
                @php
                    $step1Active = $certificate ? true : false;
                    $step1Completed = $certificate && in_array($certificate->status, ['pending', 'endorsed', 'published']);
                @endphp
                <div class="relative z-10 flex flex-col items-center text-center">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center border-4 font-black transition-all duration-300
                        {{ $step1Completed ? 'bg-[#0B5694] border-[#0B5694] text-white' : ($step1Active ? 'bg-white border-[#0B5694] text-[#0B5694]' : 'bg-gray-100 dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-400') }}">
                        @if($step1Completed)
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        @else
                            1
                        @endif
                    </div>
                    <p class="mt-3 text-xs font-bold uppercase tracking-wider {{ $step1Active ? 'text-gray-800 dark:text-gray-200' : 'text-gray-400' }}">Preparation</p>
                    <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-1 max-w-[150px]">Certificate details prepared by supervisor</p>
                </div>

                {{-- Step 2 --}}
                @php
                    $step2Active = $certificate && in_array($certificate->status, ['pending', 'endorsed', 'published']);
                    $step2Completed = $certificate && in_array($certificate->status, ['endorsed', 'published']);
                @endphp
                <div class="relative z-10 flex flex-col items-center text-center">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center border-4 font-black transition-all duration-300
                        {{ $step2Completed ? 'bg-[#0B5694] border-[#0B5694] text-white' : ($step2Active ? 'bg-white border-[#0B5694] text-[#0B5694]' : 'bg-gray-100 dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-400') }}">
                        @if($step2Completed)
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        @else
                            2
                        @endif
                    </div>
                    <p class="mt-3 text-xs font-bold uppercase tracking-wider {{ $step2Active ? 'text-gray-800 dark:text-gray-200' : 'text-gray-400' }}">Supervisor Endorsement</p>
                    <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-1 max-w-[150px]">Endorsed to Head Supervisor for signature</p>
                </div>

                {{-- Step 3 --}}
                @php
                    $step3Active = $certificate && in_array($certificate->status, ['endorsed', 'published']);
                    $step3Completed = $certificate && $certificate->status === 'published';
                @endphp
                <div class="relative z-10 flex flex-col items-center text-center">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center border-4 font-black transition-all duration-300
                        {{ $step3Completed ? 'bg-green-600 border-green-600 text-white' : ($step3Active ? 'bg-white border-[#0B5694] text-[#0B5694]' : 'bg-gray-100 dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-400') }}">
                        @if($step3Completed)
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        @else
                            3
                        @endif
                    </div>
                    <p class="mt-3 text-xs font-bold uppercase tracking-wider {{ $step3Active ? 'text-gray-800 dark:text-gray-200' : 'text-gray-400' }}">Head Supervisor Approval</p>
                    <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-1 max-w-[150px]">Signed and ready for physical pickup</p>
                </div>
            </div>

            {{-- Status Guidance Alert Panel --}}
            <div class="mt-10 border border-dashed rounded-2xl p-6 text-center transition duration-300
                @if(!$certificate)
                    bg-gray-50 dark:bg-gray-900/50 border-gray-200 dark:border-gray-800 text-gray-600 dark:text-gray-400
                @elseif($certificate->status === 'pending')
                    bg-amber-50/50 dark:bg-amber-950/10 border-amber-200 dark:border-amber-900/30 text-amber-800 dark:text-amber-300
                @elseif($certificate->status === 'endorsed')
                    bg-blue-50/50 dark:bg-blue-950/10 border-blue-200 dark:border-blue-900/30 text-blue-800 dark:text-blue-300
                @elseif($certificate->status === 'published')
                    bg-green-50 dark:bg-green-950/10 border-green-200 dark:border-green-900/30 text-green-800 dark:text-green-300
                @endif">
                
                @if(!$certificate)
                    <div class="flex flex-col items-center">
                        <svg class="w-12 h-12 text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                        <h4 class="font-bold text-base uppercase tracking-wider text-gray-800 dark:text-gray-200 mb-1">Process Not Yet Initiated</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400 max-w-md mx-auto">Your certificate preparation has not yet started. Your Office Supervisor will generate the record once your target internship hours are verified and completed.</p>
                    </div>
                @elseif($certificate->status === 'pending')
                    <div class="flex flex-col items-center">
                        <svg class="w-12 h-12 text-amber-500 mb-3 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        <h4 class="font-bold text-base uppercase tracking-wider text-amber-800 dark:text-amber-200 mb-1">Under Office Review</h4>
                        <p class="text-sm max-w-md mx-auto">Your details are currently being compiled and reviewed by your Office Supervisor. Once they complete verification, it will be endorsed upward.</p>
                    </div>
                @elseif($certificate->status === 'endorsed')
                    <div class="flex flex-col items-center">
                        <svg class="w-12 h-12 text-blue-500 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h4 class="font-bold text-base uppercase tracking-wider text-blue-800 dark:text-blue-200 mb-1">Endorsed & Awaiting Approval</h4>
                        <p class="text-sm max-w-md mx-auto">Good news! Your supervisor has endorsed your certificate of completion. It is now queued for review and signature by the Head Supervisor, Ms. Michelle A. Mal-in.</p>
                    </div>
                @elseif($certificate->status === 'published')
                    <div class="flex flex-col items-center">
                        <div class="w-16 h-16 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center text-green-600 mb-4 border border-green-200 dark:border-green-800/50 shadow-sm shadow-green-500/10">
                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h4 class="font-black text-xl uppercase tracking-wider text-green-950 dark:text-green-100 mb-2">Ready for Physical Pickup!</h4>
                        <p class="text-sm text-green-800 dark:text-green-200 max-w-xl mx-auto font-semibold mb-2">
                            Your official Certificate of Completion has been signed, finalized, and published.
                        </p>
                        <div class="mt-4 p-4 bg-white dark:bg-gray-900 border border-green-100 dark:border-green-900/50 rounded-xl shadow-sm max-w-lg">
                            <p class="text-xs uppercase font-bold text-gray-400 dark:text-gray-500 tracking-widest mb-1">Instructions</p>
                            <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                                Please proceed to the office of the Head Supervisor, <strong class="text-gray-900 dark:text-white font-extrabold">Ms. Michelle A. Mal-in</strong> (or Ms. Mal-in), at the Schools Division Office Quezon City to claim your official physical certificate.
                            </p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </main>

    <x-notification-sidebar :notifications="$notifications" />
</div>
@endsection
