@extends('layouts.app')

@section('title', 'Placement Recommendations')

@section('content')
<x-page-layout 
    portalType="Supervisor" 
    userName="{{ Auth::user()->supervisorDetails->first_name }}" 
    userRole="{{ Auth::user()->supervisorDetails->office->name }}"
>
    @php
        $casedSkillsMap = \App\Models\AssessmentItem::active()
            ->get(['competency_text'])
            ->mapWithKeys(fn($item) => [strtolower(trim($item->competency_text)) => $item->competency_text])
            ->toArray();
    @endphp
    <div class="flex flex-col space-y-6 sm:space-y-8" x-data="{ activeCompetencies: [], activeCandidateName: '', showSkillsModal: false, casedSkills: @js($casedSkillsMap) }">
        {{-- HEADER --}}
        <x-page-header
            title="Office Placement"
            highlight="Nominations"
            subtitle="Review and request candidate assignments recommended for {{ $office->name }}."
            compact="true"
        />

        {{-- MAIN CARD CONTAINER --}}
        <x-card-container 
            title="Recommended Candidates" 
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
                        
                        <div class="grid grid-cols-1 lg:grid-cols-12 items-center p-6 sm:p-8 rounded-[30px] border border-gray-100 dark:border-gray-800/80 bg-gray-50/20 dark:bg-gray-900/40 hover:shadow-xl hover:border-blue-100 dark:hover:border-blue-900/50 transition-all duration-300 gap-6">
                            
                            {{-- LEFT: Candidate Details & Scores (5 Columns) --}}
                            <div class="lg:col-span-5 flex items-start gap-5">
                                <div class="w-16 h-16 shrink-0 rounded-2xl flex flex-col items-center justify-center {{ $scoreBg }} border shadow-sm font-black text-xl">
                                    {{ $match->score }}%
                                </div>
                                <div class="space-y-1.5 text-left">
                                    <h4 class="text-base font-extrabold text-gray-800 dark:text-gray-100 tracking-tight leading-tight">{{ $match->applicant_name }}</h4>
                                    <p class="text-xs text-gray-400 font-bold uppercase tracking-wider">{{ $match->school }}</p>
                                    
                                    {{-- Multi-Factor Badges --}}
                                    <div class="flex flex-wrap items-center gap-2 mt-3 pt-1">
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 dark:bg-blue-900/20 text-blue-600 border border-blue-100 dark:border-blue-900/30">
                                            Program Relevance: {{ $degree }}%
                                        </span>
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 border border-indigo-100 dark:border-indigo-900/30">
                                            Certification Match: {{ $cert }}%
                                        </span>
                                        @if($exp > 0)
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 border border-emerald-100 dark:border-emerald-900/30">
                                                Prior Experience: {{ $exp }}%
                                            </span>
                                        @endif
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-gray-50 dark:bg-gray-800/80 text-gray-600 dark:text-gray-400 border border-gray-100 dark:border-gray-700/80">
                                            Match Quality: {{ $match->confidence }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- CENTER: Triangulated Overlaps Preview (4 Columns) --}}
                            <div class="lg:col-span-4 text-left space-y-2">
                                <span class="text-[10px] font-extrabold uppercase tracking-wider text-gray-400 dark:text-gray-500">Verified Competencies</span>
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
                                            <span @click="activeCompetencies = {{ json_encode($skills) }}; activeCandidateName = '{{ $match->applicant_name }}'; showSkillsModal = true"
                                                  class="px-2 py-0.5 bg-blue-50 hover:bg-blue-100 dark:bg-blue-950/40 dark:hover:bg-blue-900/50 text-blue-500 hover:text-blue-600 text-[10px] font-bold rounded-lg shrink-0 cursor-pointer transition shadow-sm select-none">
                                                +{{ count($skills) - 3 }} more
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>

                            {{-- RIGHT: Supervisor Request Action (3 Columns) --}}
                            <div class="lg:col-span-3 flex items-center justify-end w-full lg:w-auto lg:justify-self-end">
                                @if($match->requested_by_supervisor)
                                    <div class="flex items-center gap-2 px-6 py-3.5 bg-emerald-50 dark:bg-emerald-950/20 text-emerald-600 border border-emerald-100 dark:border-emerald-900/30 rounded-2xl text-xs font-extrabold uppercase tracking-wider shadow-sm select-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><polyline points="20 6 9 17 4 12"/></svg>
                                        Intern Requested
                                    </div>
                                @else
                                    <form action="{{ route('supervisor.office-matching.request', $match->id) }}" method="POST" class="w-full lg:w-auto">
                                        @csrf
                                        <button type="submit" class="w-full lg:w-auto px-6 py-3.5 bg-[#0B5694] hover:bg-[#09477b] dark:bg-blue-600 dark:hover:bg-blue-700 text-white rounded-2xl text-xs font-extrabold uppercase tracking-wider transition-all duration-300 shadow-md hover:shadow-lg focus:ring-4 focus:ring-blue-100 dark:focus:ring-blue-900/50 active:scale-[0.98]">
                                            Request Intern
                                        </button>
                                    </form>
                                @endif
                            </div>

                        </div>
                    @endforeach
                </div>
            @endif
        </x-card-container>

        {{-- SKILLS BREAKDOWN MODAL --}}
        <div x-show="showSkillsModal" 
             x-cloak
             class="fixed inset-0 z-[300] flex items-center justify-center p-3 sm:p-4 bg-gray-900/60 dark:bg-black/80 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @keydown.escape.window="showSkillsModal = false"
             style="display: none;"
        >
            <div class="w-full max-w-[calc(100%-1rem)] sm:max-w-2xl md:max-w-3xl lg:max-w-5xl bg-white dark:bg-gray-900 rounded-[24px] sm:rounded-[32px] border border-gray-100 dark:border-gray-800 shadow-2xl overflow-hidden flex flex-col max-h-[80vh] transition-all duration-300 text-left"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 @click.away="showSkillsModal = false"
            >
                {{-- Modal Header --}}
                <div class="px-5 sm:px-8 py-4 sm:py-6 border-b border-gray-100 dark:border-gray-800 shrink-0 flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <h2 class="text-sm sm:text-base font-black text-gray-900 dark:text-white uppercase tracking-wider truncate">
                            Competency Profile &bull; <span x-text="activeCandidateName"></span>
                        </h2>
                    </div>
                    <button @click="showSkillsModal = false" class="p-2 shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors active:scale-90">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>

                {{-- Modal Content --}}
                <div class="p-5 sm:p-8 overflow-y-auto custom-scrollbar flex-1 space-y-4">
                    <template x-for="skill in activeCompetencies" :key="skill.skill">
                        <div class="p-5 rounded-[22px] bg-gray-50/50 dark:bg-gray-800/20 border border-gray-100 dark:border-gray-800/80 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="space-y-2">
                                <h4 class="text-sm sm:text-[15px] font-bold text-gray-800 dark:text-gray-200 leading-relaxed normal-case" x-text="casedSkills[skill.skill.trim().toLowerCase()] || skill.skill"></h4>
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <template x-if="skill.resume_match">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-50 dark:bg-blue-950/40 text-blue-500 border border-blue-200/50 rounded-lg text-[10px] font-bold uppercase tracking-wider">
                                            Resume Match
                                        </span>
                                    </template>
                                    <template x-if="skill.interview_match">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-500 border border-emerald-200/50 rounded-lg text-[10px] font-bold uppercase tracking-wider">
                                            Interview Match
                                        </span>
                                    </template>
                                </div>
                            </div>
                            <div class="shrink-0 flex items-center gap-3 sm:self-center">
                                <div class="text-right">
                                    <span class="block text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-wider">Self-Reported</span>
                                    <span class="text-sm font-black text-gray-700 dark:text-gray-300" x-text="skill.self_reported + ' / 5'"></span>
                                </div>
                                <template x-if="skill.verified !== null">
                                    <div class="text-right pl-3 border-l border-gray-200 dark:border-gray-800">
                                        <span class="block text-[9px] font-black text-emerald-600 dark:text-emerald-500 uppercase tracking-wider">Verified</span>
                                        <span class="text-sm font-black text-emerald-600 dark:text-emerald-500" x-text="skill.verified + ' / 5'"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Modal Footer --}}
                <div class="px-5 sm:px-8 py-4 sm:py-6 border-t border-gray-100 dark:border-gray-800 shrink-0 bg-gray-50 dark:bg-gray-900/50 flex justify-end">
                    <button @click="showSkillsModal = false" type="button" class="px-6 py-2.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 font-bold uppercase text-[10px] tracking-wider rounded-xl transition-all shadow-md active:scale-95">Close</button>
                </div>
            </div>
        </div>
    </div>
</x-page-layout>
@endsection
