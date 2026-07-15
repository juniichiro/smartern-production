@extends('layouts.app')

@section('title', 'Placement Recommendations')

@section('content')
<x-page-layout 
    portalType="Supervisor" 
    userName="{{ Auth::user()->supervisorDetails->first_name }}" 
    userRole="{{ Auth::user()->supervisorDetails->office->name }}"
>
    <div class="flex flex-col space-y-6 sm:space-y-8">
        {{-- HEADER --}}
        <x-page-header
            title="Intern Placement"
            highlight="Recommendations"
            subtitle="Top 5 candidates matching the requirements for {{ $office->name }}."
            compact="true"
        />

        {{-- MAIN CARD CONTAINER --}}
        <x-card-container 
            title="Top Candidate Matches" 
            padding="p-6 sm:p-8"
        >
            @if($topMatches->isEmpty())
                <x-empty-state 
                    title="No Matching Candidates Found"
                    subtitle="Run matching calculations or wait for onboarding applicants to complete their profile assessments."
                    compact="false"
                />
            @else
                <div class="space-y-6">
                    @foreach($topMatches as $match)
                        @php
                            $breakdown = json_decode($match->breakdown, true) ?? [];
                            $degree = $breakdown['degree_alignment'] ?? 0;
                            $cert = $breakdown['certification_score'] ?? 0;
                            $exp = $breakdown['experience_score'] ?? 0;
                            $skills = $breakdown['skills_breakdown'] ?? [];
                            
                            // Color formatting based on match score
                            if ($match->score >= 80) {
                                $scoreBg = 'bg-emerald-50 dark:bg-emerald-950/20 text-emerald-600 border-emerald-100 dark:border-emerald-900/50';
                                $badgeColor = 'bg-emerald-500';
                            } elseif ($match->score >= 60) {
                                $scoreBg = 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 border-blue-100 dark:border-blue-900/50';
                                $badgeColor = 'bg-blue-500';
                            } elseif ($match->score >= 40) {
                                $scoreBg = 'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 border-indigo-100 dark:border-indigo-900/50';
                                $badgeColor = 'bg-indigo-500';
                            } else {
                                $scoreBg = 'bg-amber-50 dark:bg-amber-900/20 text-amber-600 border-amber-100 dark:border-amber-900/50';
                                $badgeColor = 'bg-amber-500';
                            }
                        @endphp
                        
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between p-6 sm:p-8 rounded-[30px] border border-gray-100 dark:border-gray-800/80 bg-gray-50/20 dark:bg-gray-900/40 hover:shadow-xl hover:border-blue-100 dark:hover:border-blue-900/50 transition-all duration-300 gap-6">
                            
                            {{-- LEFT: Candidate Details & Scores --}}
                            <div class="flex items-start gap-5">
                                <div class="w-16 h-16 shrink-0 rounded-2xl flex flex-col items-center justify-center {{ $scoreBg }} border shadow-sm font-black text-xl">
                                    {{ $match->score }}%
                                </div>
                                <div class="space-y-1.5 text-left">
                                    <h4 class="text-base font-extrabold text-gray-800 dark:text-gray-100 tracking-tight leading-tight">{{ $match->applicant_name }}</h4>
                                    <p class="text-xs text-gray-400 font-bold uppercase tracking-wider">{{ $match->school }}</p>
                                    
                                    {{-- Multi-Factor Badges --}}
                                    <div class="flex flex-wrap items-center gap-2 mt-3 pt-1">
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 dark:bg-blue-900/20 text-blue-600 border border-blue-100 dark:border-blue-900/30">
                                            Degree Fit: {{ $degree }}%
                                        </span>
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 border border-indigo-100 dark:border-indigo-900/30">
                                            Certs Fit: {{ $cert }}%
                                        </span>
                                        @if($exp > 0)
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 border border-emerald-100 dark:border-emerald-900/30">
                                                Exp Boost: {{ $exp }}%
                                            </span>
                                        @endif
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-gray-50 dark:bg-gray-800/80 text-gray-600 dark:text-gray-400 border border-gray-100 dark:border-gray-700/80">
                                            Confidence: {{ $match->confidence }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- CENTER: Triangulated Overlaps Preview --}}
                            <div class="flex-1 lg:max-w-md text-left space-y-2">
                                <span class="text-[10px] font-extrabold uppercase tracking-wider text-gray-400 dark:text-gray-500">Matched Competencies</span>
                                <div class="flex flex-wrap gap-1.5 max-h-24 overflow-y-auto custom-scrollbar pr-1">
                                    @php
                                        $matchedSkills = collect($skills)->filter(fn($s) => $s['resume_match'] || $s['interview_match'])->take(3);
                                    @endphp
                                    @if($matchedSkills->isEmpty())
                                        <p class="text-xs font-semibold text-gray-400 dark:text-gray-500">Based mostly on general credentials and self-reported metrics.</p>
                                    @else
                                        @foreach($matchedSkills as $s)
                                            <span class="px-2 py-0.5 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-[10px] font-semibold rounded-lg truncate max-w-[200px]" title="{{ $s['skill'] }}">
                                                {{ $s['skill'] }}
                                            </span>
                                        @endforeach
                                        @if(count($skills) > 3)
                                            <span class="px-2 py-0.5 bg-blue-50/50 dark:bg-blue-900/10 text-blue-500 text-[10px] font-bold rounded-lg shrink-0">
                                                +{{ count($skills) - 3 }} more
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>

                            {{-- RIGHT: Supervisor Request Action --}}
                            <div class="shrink-0 flex items-center justify-end">
                                @if($match->requested_by_supervisor)
                                    <div class="flex items-center gap-2 px-6 py-3.5 bg-emerald-50 dark:bg-emerald-950/20 text-emerald-600 border border-emerald-100 dark:border-emerald-900/30 rounded-2xl text-xs font-extrabold uppercase tracking-wider shadow-sm select-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><polyline points="20 6 9 17 4 12"/></svg>
                                        Requested Nomination
                                    </div>
                                @else
                                    <form action="{{ route('supervisor.office-matching.request', $match->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="w-full sm:w-auto px-6 py-3.5 bg-[#0B5694] hover:bg-[#09477b] dark:bg-blue-600 dark:hover:bg-blue-700 text-white rounded-2xl text-xs font-extrabold uppercase tracking-wider transition-all duration-300 shadow-md hover:shadow-lg focus:ring-4 focus:ring-blue-100 dark:focus:ring-blue-900/50 active:scale-[0.98]">
                                            Nominate Intern
                                        </button>
                                    </form>
                                @endif
                            </div>

                        </div>
                    @endforeach
                </div>
            @endif
        </x-card-container>
    </div>
</x-page-layout>
@endsection
