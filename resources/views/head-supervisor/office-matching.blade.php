@extends('layouts.app')

@section('title', 'Office Matching')

@section('content')
<x-page-layout 
    portalType="Head Supervisor" 
    userName="{{ Auth::user()->supervisorDetails->first_name }}" 
    userRole="{{ Auth::user()->supervisorDetails->office->name }}"
>
    <div x-data="{ 
        expanded: null, 
        evaluating: null, 
        selectedApplicants: [], 
        batchOfficeId: '', 
        batchOfficeName: 'Select Assignment', 
        isDropdownOpen: false,
        showImport: false,
        resumeUrl: '',
        showResumePreview: false,
        showBreakdown: null,
        showLegends: false,
        showResetModal: false,
        isSelected(name) { return this.selectedApplicants.includes(name); },
        selectAll(allNames) {
            if (this.selectedApplicants.length === allNames.length) {
                this.selectedApplicants = [];
            } else {
                this.selectedApplicants = [...allNames];
            }
        },
        openResume(url) {
            this.resumeUrl = url;
            this.showResumePreview = true;
        }
     }" class="flex flex-col h-full transition-all duration-300 ease-out">
        
        <header class="flex flex-col lg:flex-row justify-between items-start gap-6 mb-10 transition-all duration-300 ease-out">
            <div class="min-w-0">
                <h3 class="text-xl sm:text-2xl lg:text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight uppercase">Office Matching</h3>
                <p class="text-[10px] sm:text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mt-1">Manage intern assignments and analyze compatibility</p>
            </div>
            <div class="flex items-center gap-1.5 sm:gap-2 w-full lg:w-auto transition-all duration-300 ease-out">
                {{-- Mobile: action buttons as icon bar --}}
                <div class="flex items-center gap-1.5 sm:gap-2 flex-wrap">
                    {{-- Import Button --}}
                    <button @click="showImport = !showImport" 
                            title="Import CSV"
                            class="flex items-center justify-center bg-gray-50 dark:bg-gray-800 p-2.5 sm:px-5 sm:py-3 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-xl font-bold text-gray-500 dark:text-gray-400 hover:border-[#0B5694]/30 hover:text-[#0B5694] dark:hover:text-blue-400 transition uppercase tracking-wider active:scale-95 min-w-[44px] min-h-[44px]"
                            :class="showImport ? 'border-[#0B5694] text-[#0B5694] bg-blue-50' : ''">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                        <span class="hidden md:inline ml-2 text-[10px] lg:text-xs">Import CSV</span>
                    </button>

                    {{-- Algorithm Guide Button --}}
                    <a href="{{ route('head-supervisor.matching.guide') }}" title="Algorithm Guide"
                       class="flex items-center justify-center bg-blue-50 dark:bg-blue-900/20 p-2.5 sm:px-5 sm:py-3 border border-blue-100 dark:border-blue-800 rounded-xl font-bold text-[#0B5694] dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/40 transition uppercase tracking-wider active:scale-95 min-w-[44px] min-h-[44px]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                        <span class="hidden md:inline ml-2 text-[10px] lg:text-xs">Algorithm Guide</span>
                    </a>

                    {{-- Export Data Button --}}
                    <a href="{{ route('head-supervisor.matching.export') }}" title="Export Data"
                       class="flex items-center justify-center bg-white dark:bg-gray-900 p-2.5 sm:px-5 sm:py-3 border border-gray-200 dark:border-gray-800 rounded-xl font-bold text-[#0B5694] dark:text-blue-400 shadow-sm hover:bg-blue-50 dark:hover:bg-blue-950/30 transition uppercase tracking-wider active:scale-95 min-w-[44px] min-h-[44px]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/></svg>
                        <span class="hidden md:inline ml-2 text-[10px] lg:text-xs">Export Data</span>
                    </a>

                    {{-- Reset Pending Button --}}
                    @if(\App\Models\MatchResult::where('is_confirmed', false)->exists())
                    <button type="button" @click="showResetModal = true" title="Reset Pending" class="group flex items-center justify-center text-red-400 hover:text-red-600 transition-colors min-w-[44px] min-h-[44px]">
                        <div class="p-2.5 sm:p-3 rounded-xl bg-red-50 dark:bg-red-950/30 group-hover:bg-red-100 dark:group-hover:bg-red-900/50 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                        </div>
                        <span class="hidden md:inline ml-1.5 sm:ml-2 text-[10px] lg:text-xs font-bold uppercase tracking-wider text-red-500">Reset Pending</span>
                    </button>
                    @endif
                </div>
            </div>
        </header>

        {{-- BATCH IMPORT SECTION (Moved to top) --}}
        <div x-show="showImport" x-transition.scale.95.duration.300ms x-cloak class="mb-10 bg-white dark:bg-gray-900 rounded-[25px] sm:rounded-[35px] border-2 border-dashed border-[#0B5694]/20 dark:border-blue-500/20 p-6 sm:p-10 shadow-2xl shadow-blue-500/5 transition-all duration-300 ease-out">
            <div class="flex flex-col md:flex-row items-center gap-6 sm:gap-10">
                <div class="flex-1 text-center md:text-left">
                    <h5 class="text-sm font-black uppercase tracking-widest text-[#0B5694] dark:text-blue-400">Batch Import Tool</h5>
                    <p class="text-xs font-bold text-gray-400 mt-2">Upload a CSV file to analyze multiple intern applicants at once. Use this for legacy data or external lists.</p>
                    <div class="mt-6">
                        <a href="{{ route('head-supervisor.template') }}" class="text-[10px] font-bold text-[#0B5694] dark:text-blue-400 hover:underline uppercase tracking-wider flex items-center gap-2 justify-center md:justify-start">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                            Download CSV Template
                        </a>
                    </div>
                </div>
                    <div class="w-full md:w-auto">
                    <form action="{{ route('head-supervisor.upload') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="bg-gray-50 dark:bg-gray-950 border border-gray-100 dark:border-gray-800 rounded-3xl p-6 sm:p-8 text-center">
                            <input type="file" name="assessment_csv" id="importInput" class="hidden" accept=".csv" onchange="this.form.submit()">
                            <button type="button" onclick="document.getElementById('importInput').click()" class="bg-[#0B5694] dark:bg-blue-600 text-white px-8 sm:px-10 py-3 sm:py-4 rounded-2xl text-[10px] sm:text-xs font-bold uppercase tracking-wider shadow-xl shadow-blue-500/20 active:scale-95 transition-all w-full sm:w-auto">Select CSV File</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @if(isset($applicants) && $applicants->count() > 0)
            {{-- MAIN ANALYSIS TABLE --}}
            <div class="bg-white dark:bg-gray-900 rounded-[25px] sm:rounded-[35px] shadow-xl dark:shadow-none border border-gray-100 dark:border-gray-800 p-4 sm:p-8 lg:p-10 mb-10 animate-in fade-in slide-in-from-bottom-4 duration-500 transition-all duration-300 ease-out">
                
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 mb-8">
                    <h4 class="text-lg sm:text-xl font-extrabold text-gray-900 dark:text-white tracking-tight uppercase">Applicant Analysis</h4>

                    {{-- Batch Finalize --}}
                    <form action="{{ route('head-supervisor.matching.finalize') }}" method="POST" x-show="selectedApplicants.length > 0" x-cloak class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full lg:w-auto transition-all duration-300 ease-out">
                        @csrf
                        <template x-for="name in selectedApplicants" :key="name">
                            <input type="hidden" name="applicant_names[]" :value="name">
                        </template>
                        
                        <div class="relative w-full sm:w-72" @keydown.escape.window="isDropdownOpen = false">
                            <input type="hidden" name="office_id" :value="batchOfficeId" required>
                            <button type="button" @click="isDropdownOpen = !isDropdownOpen" @click.away="isDropdownOpen = false"
                                    class="w-full flex items-center justify-between bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 text-[#0B5694] dark:text-blue-400 text-[11px] font-bold uppercase tracking-wider rounded-xl focus:ring-2 focus:ring-[#0B5694]/20 px-5 min-h-[48px] shadow-sm transition-all hover:border-gray-300">
                                <span class="truncate" x-text="batchOfficeName"></span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" :class="isDropdownOpen ? 'rotate-180' : ''" class="shrink-0 transition-transform duration-200 ml-2"><path d="M6 9l6 6 6-6"/></svg>
                            </button>
                            <div x-show="isDropdownOpen" x-transition.opacity class="absolute z-50 left-0 right-0 sm:right-auto w-full mt-2 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl shadow-2xl overflow-hidden py-2 min-w-0">
                                @foreach($offices as $office)
                                    <div @click="batchOfficeId = '{{ $office->id }}'; batchOfficeName = '{{ $office->name }}'; isDropdownOpen = false"
                                         class="px-5 sm:px-6 py-3.5 sm:py-4 text-[11px] font-bold uppercase tracking-[0.1em] text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-blue-900/40 hover:text-[#0B5694] dark:hover:text-blue-400 cursor-pointer transition-all border-b border-gray-50 dark:border-gray-800/50 last:border-none flex items-center justify-between group min-h-[48px]">
                                        <span class="truncate">{{ $office->name }}</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 opacity-0 group-hover:opacity-100 transition-opacity ml-3"><polyline points="20 6 9 17 4 12"/></svg>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <button type="submit" class="w-full sm:w-auto bg-[#0B5694] dark:bg-blue-600 text-white px-8 min-h-[48px] rounded-xl text-xs font-bold uppercase tracking-wider flex items-center justify-center gap-2 hover:bg-[#084577] transition shadow-lg shadow-blue-500/20 disabled:opacity-50 active:scale-95" :disabled="!batchOfficeId">
                            Confirm Selected (<span x-text="selectedApplicants.length"></span>)
                        </button>
                    </form>
                </div>

                {{-- LEGENDS --}}
                <div class="mb-4 sm:mb-8 lg:mb-10 px-0 sm:px-2">
                    <button @click="showLegends = !showLegends" 
                            class="flex md:hidden items-center gap-2 text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-2 active:scale-95 transition-transform min-h-[44px]">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        <span x-text="showLegends ? 'Hide Legend' : 'Show Legend'"></span>
                        <span class="ml-1 text-[9px] text-gray-400">(Recommendations & Verification)</span>
                    </button>

                    <div :class="showLegends ? 'flex' : 'hidden md:flex'"
                         class="flex-col gap-3 sm:gap-4 lg:gap-6">
                        <div class="flex flex-wrap items-center gap-x-3 sm:gap-x-4 lg:gap-x-6 gap-y-2">
                            <span class="text-[9px] sm:text-[10px] lg:text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider border-r border-gray-200 dark:border-gray-800 pr-3 sm:pr-6 shrink-0">Recommendation</span>
                            <div class="flex items-center gap-1.5 sm:gap-2 text-[9px] sm:text-[10px] lg:text-xs font-bold uppercase tracking-wider text-emerald-500">
                                <div class="w-2 h-2 sm:w-2.5 sm:h-2.5 rounded-full bg-emerald-500 shrink-0"></div> Strong (80%+)
                            </div>
                            <div class="flex items-center gap-1.5 sm:gap-2 text-[9px] sm:text-[10px] lg:text-xs font-bold uppercase tracking-wider text-[#0B5694] dark:text-blue-400">
                                <div class="w-2 h-2 sm:w-2.5 sm:h-2.5 rounded-full bg-[#0B5694] dark:bg-blue-400 shrink-0"></div> Good (60-79%)
                            </div>
                            <div class="flex items-center gap-1.5 sm:gap-2 text-[9px] sm:text-[10px] lg:text-xs font-bold uppercase tracking-wider text-indigo-500">
                                <div class="w-2 h-2 sm:w-2.5 sm:h-2.5 rounded-full bg-indigo-500 shrink-0"></div> Fair (40-59%)
                            </div>
                            <div class="flex items-center gap-1.5 sm:gap-2 text-[9px] sm:text-[10px] lg:text-xs font-bold uppercase tracking-wider text-amber-500">
                                <div class="w-2 h-2 sm:w-2.5 sm:h-2.5 rounded-full bg-amber-500 shrink-0"></div> Poor (&lt; 40%)
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-x-3 sm:gap-x-4 lg:gap-x-6 gap-y-2">
                            <span class="text-[9px] sm:text-[10px] lg:text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider border-r border-gray-200 dark:border-gray-800 pr-3 sm:pr-6 shrink-0">Verification</span>
                            <div class="flex items-center gap-1.5 sm:gap-2 text-[9px] sm:text-[10px] lg:text-[11px] font-bold uppercase tracking-wider text-gray-500">
                                <span class="bg-emerald-50 dark:bg-emerald-950/30 text-emerald-500 px-2 sm:px-2.5 py-0.5 sm:py-1 rounded text-[8px] sm:text-[9px] lg:text-[10px] border border-emerald-500/20">High</span> Interviewed
                            </div>
                            <div class="flex items-center gap-1.5 sm:gap-2 text-[9px] sm:text-[10px] lg:text-[11px] font-bold uppercase tracking-wider text-gray-500">
                                <span class="bg-blue-50 dark:bg-blue-950/30 text-blue-500 px-2 sm:px-2.5 py-0.5 sm:py-1 rounded text-[8px] sm:text-[9px] lg:text-[10px] border border-blue-500/20">Med</span> Resume Found
                            </div>
                            <div class="flex items-center gap-1.5 sm:gap-2 text-[9px] sm:text-[10px] lg:text-[11px] font-bold uppercase tracking-wider text-gray-500">
                                <span class="bg-amber-50 dark:bg-amber-950/30 text-amber-500 px-2 sm:px-2.5 py-0.5 sm:py-1 rounded text-[8px] sm:text-[9px] lg:text-[10px] border border-amber-500/20">Low</span> Self-Reported
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto -mx-4 sm:-mx-8 lg:-mx-10 px-4 sm:px-8 lg:px-10 hidden lg:block transition-all duration-300 ease-out">
                    <table class="min-w-[700px] w-full text-left transition-all duration-300 ease-out">
                        <thead class="bg-gray-50 dark:bg-gray-950 text-[9px] sm:text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100 dark:border-gray-800">
                            <tr>
                                <th class="px-3 sm:px-6 py-4 sm:py-6 w-10 sm:w-12 text-center">
                                    <input type="checkbox" 
                                           @click="selectAll({{ json_encode($applicants->filter(fn($a) => !\App\Models\MatchResult::where('applicant_name', $a->full_name)->where('is_confirmed', true)->exists())->pluck('full_name')->toArray()) }})"
                                           :checked="selectedApplicants.length === {{ $applicants->filter(fn($a) => !\App\Models\MatchResult::where('applicant_name', $a->full_name)->where('is_confirmed', true)->exists())->count() }} && selectedApplicants.length > 0"
                                           class="w-5 h-5 rounded-lg border-gray-300 accent-[#0B5694] cursor-pointer shadow-sm transition-all">
                                </th>
                                <th class="px-3 sm:px-6 py-4 sm:py-6">Applicant</th>
                                <th class="px-3 sm:px-6 py-4 sm:py-6">Department Match</th>
                                <th class="px-3 sm:px-6 py-4 sm:py-6">Score</th>
                                <th class="px-3 sm:px-6 py-4 sm:py-6">Verification</th>
                                <th class="px-3 sm:px-6 py-4 sm:py-6 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-[13px] sm:text-[15px] divide-y divide-gray-50 dark:divide-gray-800">
                            @foreach($applicants as $applicant)
                                @php 
                                    $matches = $results->get($applicant->full_name, collect());
                                    $confirmedMatch = $matches->where('is_confirmed', true)->first();
                                    $bestMatch = $matches->sortByDesc('score')->first();
                                    $displayMatch = $confirmedMatch ?? $bestMatch;

                                    $confidenceText = $displayMatch->confidence ?? 'Low';
                                    if ($confidenceText === 'High') {
                                        $confidenceColor = 'text-emerald-500'; $confidenceBg = 'bg-emerald-50 dark:bg-emerald-950/30';
                                    } elseif ($confidenceText === 'Medium') {
                                        $confidenceColor = 'text-blue-500'; $confidenceBg = 'bg-blue-50 dark:bg-blue-950/30';
                                    } else {
                                        $confidenceColor = 'text-amber-500'; $confidenceBg = 'bg-amber-50 dark:bg-amber-950/30';
                                    }

                                     if (!$displayMatch) {
                                         $scoreText = 'N/A'; $scoreColor = 'text-gray-400'; $barColor = 'bg-gray-200';
                                     } elseif ($displayMatch->score >= 80) {
                                         $scoreColor = 'text-emerald-500'; $barColor = 'bg-emerald-500';
                                     } elseif ($displayMatch->score >= 60) {
                                         $scoreColor = 'text-blue-500'; $barColor = 'bg-blue-500';
                                     } elseif ($displayMatch->score >= 40) {
                                         $scoreColor = 'text-indigo-500'; $barColor = 'bg-indigo-500';
                                     } else {
                                         $scoreColor = 'text-amber-500'; $barColor = 'bg-amber-500';
                                     }
                                @endphp
                                
                                <tr class="transition-colors" :class="isSelected(@js($applicant->full_name)) ? 'bg-blue-50/30 dark:bg-blue-950/10' : ''">
                                    <td class="px-3 sm:px-6 py-4 sm:py-5 text-center">
                                        @if(!$confirmedMatch)
                                            <input type="checkbox" value="{{ $applicant->full_name }}" x-model="selectedApplicants" class="w-5 h-5 rounded-lg border-gray-300 accent-[#0B5694] cursor-pointer shadow-sm transition-all">
                                        @else
                                            <div class="flex justify-center text-emerald-600" title="Assigned">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-3 sm:px-6 py-4 sm:py-6 font-bold text-gray-800 dark:text-gray-100">
                                        <div class="flex items-center gap-2">
                                            {{ $applicant->full_name }}
                                            @if(!empty($applicant->resume_path))
                                                <button @click="openResume('{{ route('head-supervisor.matching.resume', $applicant) }}')" class="text-blue-500 hover:text-blue-700" title="Preview Resume">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/></svg>
                                                </button>
                                            @endif
                                        </div>
                                        <p class="text-xs text-gray-500 font-bold uppercase tracking-tight mt-1">{{ $applicant->university }}</p>
                                    </td>
                                    <td class="px-3 sm:px-6 py-4 sm:py-6 font-black text-gray-800 dark:text-gray-100 uppercase tracking-tight text-xs sm:text-sm">
                                        {{ $displayMatch ? $displayMatch->office->name : 'No Results' }}
                                    </td>
                                    <td class="px-3 sm:px-6 py-4 sm:py-6">
                                        @if($displayMatch)
                                            <div class="flex items-center gap-3">
                                                <div class="w-24 h-2.5 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden border border-gray-100 dark:border-gray-800">
                                                    <div class="h-full {{ $barColor }}" style="width: {{ $displayMatch->score }}%"></div>
                                                </div>
                                                <span class="{{ $scoreColor }} font-bold text-sm">{{ $displayMatch->score }}%</span>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-3 sm:px-6 py-4 sm:py-6">
                                        <span class="{{ $confidenceBg }} {{ $confidenceColor }} text-[10px] sm:text-[11px] font-bold px-3 sm:px-4 py-1.5 sm:py-2 rounded-full uppercase tracking-wider border border-current shadow-sm whitespace-nowrap">
                                            {{ $confidenceText }}
                                        </span>
                                    </td>
                                    <td class="px-3 sm:px-6 py-4 sm:py-6 text-right">
                                        <div class="flex justify-end gap-2 sm:gap-3">
                                            <button @click="if (expanded === '{{ $applicant->applicant_id }}') { expanded = null; } else { expanded = '{{ $applicant->applicant_id }}'; evaluating = null; }" class="bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 px-3 sm:px-5 py-2 sm:py-2.5 rounded-xl text-[10px] sm:text-xs font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 dark:hover:text-white dark:hover:border-gray-500 transition shadow-sm uppercase tracking-wider active:scale-95">Analysis</button>
                                            @if(!$confirmedMatch)
                                                <button @click="if (evaluating === '{{ $applicant->applicant_id }}') { evaluating = null; } else { evaluating = '{{ $applicant->applicant_id }}'; expanded = null; }" class="bg-[#0B5694] dark:bg-blue-600 text-white px-3 sm:px-5 py-2 sm:py-2.5 rounded-xl text-[10px] sm:text-xs font-bold hover:bg-[#084577] transition shadow-sm uppercase tracking-wider active:scale-95">Evaluate</button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>

                                {{-- EVALUATION --}}
                                <tr x-show="evaluating === '{{ $applicant->applicant_id }}'" x-cloak class="bg-blue-50/20 dark:bg-blue-900/10 border-b border-gray-100 dark:border-gray-800">
                                    <td colspan="6" class="px-4 sm:px-12 py-6 sm:py-10">
                                        <div class="bg-white dark:bg-gray-900 p-6 sm:p-10 rounded-[25px] sm:rounded-[35px] border border-blue-100 dark:border-blue-900/50 shadow-lg" x-data="{ skillFilter: 'all' }">
                                            @php
                                                $skillToCategory = \App\Models\AssessmentItem::active()->pluck('category', 'competency_text')->mapWithKeys(fn($cat, $txt) => [trim($txt) => $cat])->toArray();
                                            @endphp
                                            <form action="{{ route('head-supervisor.matching.evaluate', $applicant) }}" method="POST" enctype="multipart/form-data">
                                                @csrf
                                                <div class="space-y-6">
                                                    {{-- Skills Verification Table --}}
                                                    <div class="space-y-4">
                                                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                                            <h5 class="text-xs font-bold uppercase tracking-wider text-[#0B5694] dark:text-blue-400">Skill Verification</h5>
                                                            {{-- Category Filter Dropdown --}}
                                                            <div class="relative w-full sm:w-72">
                                                                <select x-model="skillFilter" 
                                                                        class="w-full bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 text-gray-700 dark:text-gray-300 text-xs font-bold uppercase tracking-wider rounded-xl px-4 py-3 shadow-sm focus:ring-2 focus:ring-[#0B5694]/20 outline-none transition-all cursor-pointer">
                                                                    <option value="all">All Sections</option>
                                                                    <option value="I. Digital and Technical Skills">I. Digital & Technical Skills</option>
                                                                    <option value="II. Administrative and Office Skills">II. Administrative & Office Skills</option>
                                                                    <option value="III. Analytical and Problem-Solving Skills">III. Analytical & Problem-Solving</option>
                                                                    <option value="IV. Communication and Interpersonal Skills">IV. Communication & Interpersonal</option>
                                                                    <option value="V. Work Attitude and Professional Values">V. Work Attitude & Values</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        @php $verifiedSkills = $applicant->evaluation?->verified_skills ?? []; @endphp
                                                        <div class="overflow-x-auto max-h-64 overflow-y-auto custom-scrollbar border border-gray-100 dark:border-gray-800 rounded-2xl">
                                                            <table class="w-full text-left border-collapse">
                                                                <thead class="bg-gray-50 dark:bg-gray-950 sticky top-0 z-10">
                                                                    <tr class="border-b border-gray-100 dark:border-gray-800">
                                                                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-wider text-gray-500">Skill Name</th>
                                                                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-wider text-gray-500 text-center">Self-Reported</th>
                                                                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-wider text-gray-500 text-right">Verified Score</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800 bg-white dark:bg-gray-900">
                                                                    @foreach(array_merge($applicant->tech_skills, $applicant->soft_skills) as $skill => $score)
                                                                        @php
                                                                            $itemCategory = $skillToCategory[trim($skill)] ?? 'Other';
                                                                        @endphp
                                                                        <tr x-show="skillFilter === 'all' || skillFilter === '{{ $itemCategory }}'">
                                                                            <td class="px-6 py-3.5 text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-tight">{{ $skill }}</td>
                                                                            <td class="px-6 py-3.5 text-xs font-bold text-gray-500 text-center">
                                                                                <span class="inline-block bg-gray-100 dark:bg-gray-800 px-2.5 py-1 rounded-lg text-[10px] font-extrabold">{{ $score }} / 5</span>
                                                                            </td>
                                                                            <td class="px-6 py-3.5 text-right">
                                                                                <select name="verified_skills[{{ $skill }}]" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl pl-4 pr-10 py-2 text-xs font-bold focus:ring-2 focus:ring-[#0B5694]/20 outline-none transition-all cursor-pointer shadow-sm">
                                                                                    @for($i=1; $i<=5; $i++)
                                                                                        <option value="{{ $i }}" {{ ($verifiedSkills[$skill] ?? $score) == $i ? 'selected' : '' }}>{{ $i }} - {{ ['Poor', 'Fair', 'Good', 'Excellent', 'Superior'][$i-1] }}</option>
                                                                                    @endfor
                                                                                </select>
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>

                                                    {{-- Interview Notes, Resume, and Behavioral Scores --}}
                                                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8 border-t border-gray-100 dark:border-gray-800 pt-6">
                                                        {{-- Left Side: Interview Notes --}}
                                                        <div class="space-y-4">
                                                            <h5 class="text-xs font-bold uppercase tracking-wider text-[#0B5694] dark:text-blue-400">Interview Notes</h5>
                                                            <textarea name="interview_notes" rows="6" class="w-full bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-2xl p-5 text-sm font-bold focus:ring-2 focus:ring-[#0B5694]/20 outline-none transition-all" placeholder="Add qualitative observations...">{{ $applicant->evaluation?->interview_notes }}</textarea>
                                                        </div>

                                                        {{-- Right Side: Resume, Behavior Score & Action --}}
                                                        <div class="space-y-6">
                                                            <div>
                                                                <h5 class="text-xs font-bold uppercase tracking-wider text-[#0B5694] dark:text-blue-400 mb-3">Resume Document</h5>
                                                                @if(!empty($applicant->resume_path))
                                                                    <div class="flex items-center gap-3 p-4 bg-emerald-50 dark:bg-emerald-950/20 rounded-2xl border border-emerald-100 dark:border-emerald-900/40 mb-3">
                                                                        <svg xmlns="http://www.w3.org/2000/svg" class="text-emerald-500" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/></svg>
                                                                        <span class="text-xs font-bold text-emerald-700 dark:text-emerald-400 uppercase tracking-tight">Resume PDF attached</span>
                                                                    </div>
                                                                @endif
                                                                <input type="file" name="resume_pdf" accept="application/pdf" class="w-full text-xs font-bold text-gray-500 dark:text-gray-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-extrabold file:bg-blue-50 file:text-[#0B5694] dark:file:bg-blue-900/30 dark:file:text-blue-400 hover:file:bg-blue-100 transition cursor-pointer">
                                                                <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-2 italic">Accepts PDF only. Adding/updating a resume triggers automated parsing.</p>
                                                            </div>

                                                            <div class="grid grid-cols-2 gap-4">
                                                                <div>
                                                                    <label class="block text-[10px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-2">Behavioral Score</label>
                                                                    <select name="behavioral_score" class="w-full bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-xl p-3.5 text-xs font-bold uppercase tracking-wider focus:ring-2 focus:ring-[#0B5694]/20 outline-none transition-all cursor-pointer">
                                                                        @for($i=1; $i<=5; $i++)
                                                                            <option value="{{ $i }}" {{ ($applicant->evaluation?->behavioral_score ?? 3) == $i ? 'selected' : '' }}>{{ $i }} - {{ ['Poor', 'Fair', 'Good', 'Excellent', 'Superior'][$i-1] }}</option>
                                                                        @endfor
                                                                    </select>
                                                                </div>
                                                                <div>
                                                                    <label class="block text-[10px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-2">Recommendation</label>
                                                                    <select name="is_recommended" class="w-full bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-xl p-3.5 text-xs font-bold uppercase tracking-wider focus:ring-2 focus:ring-[#0B5694]/20 outline-none transition-all cursor-pointer">
                                                                        <option value="1" {{ ($applicant->evaluation?->is_recommended ?? true) ? 'selected' : '' }}>Recommended</option>
                                                                        <option value="0" {{ !($applicant->evaluation?->is_recommended ?? true) ? 'selected' : '' }}>Needs Training</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="border-t border-gray-100 dark:border-gray-800 pt-6 flex justify-end">
                                                        <button type="submit" class="px-8 py-4 bg-[#0B5694] dark:bg-blue-600 hover:bg-[#084577] dark:hover:bg-blue-500 text-white font-bold uppercase text-xs tracking-wider rounded-xl transition-all shadow-md active:scale-95 shadow-blue-500/10">Update Evaluation</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                                {{-- ANALYSIS --}}
                                <tr x-show="expanded === '{{ $applicant->applicant_id }}'" x-cloak class="bg-gray-50/50 dark:bg-gray-950/50 border-b border-gray-100 dark:border-gray-800">
                                    <td colspan="6" class="px-4 sm:px-12 py-6 sm:py-8">
                                        @php
                                            $top5Matches = $matches->sortByDesc('score')->take(5);
                                        @endphp
                                        <div class="bg-white dark:bg-gray-900 p-6 sm:p-8 rounded-[25px] sm:rounded-[35px] border border-gray-100 dark:border-gray-800 shadow-lg space-y-6 sm:space-y-8"
                                             x-data="{ activeMatchId: 'all' }">
                                            <div class="text-left border-b border-gray-50 dark:border-gray-800 pb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                                <div>
                                                    <h5 class="text-xs font-bold uppercase tracking-wider text-[#0B5694] dark:text-blue-400">Match Compatibility Analysis</h5>
                                                    <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mt-1">Select an office to view its compatibility bar and detailed calculation.</p>
                                                </div>
                                                
                                                {{-- Filter Dropdown --}}
                                                <div class="relative w-full sm:w-64">
                                                    <select x-model="activeMatchId" 
                                                            class="w-full bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 text-gray-700 dark:text-gray-300 text-xs font-bold uppercase tracking-wider rounded-xl px-4 py-3 shadow-sm focus:ring-2 focus:ring-[#0B5694]/20 outline-none transition-all cursor-pointer">
                                                        <option value="all">All Top 5 Offices</option>
                                                        @foreach($top5Matches as $match)
                                                            <option value="{{ $match->id }}">{{ $match->office->name }} ({{ $match->score }}%)</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="space-y-5 max-w-3xl mx-auto">
                                                @if($matches->count() > 0)
                                                    {{-- Statistical Vertical Bar Chart --}}
                                                    <div class="relative w-full max-w-xl mx-auto pt-4 pb-2">
                                                        {{-- Chart Area: height exactly h-48 (192px) --}}
                                                        <div class="relative h-48 w-full">
                                                            {{-- Y-Axis Gridlines Background --}}
                                                            <div class="absolute inset-0 flex flex-col justify-between pointer-events-none text-[9px] font-bold text-gray-400 dark:text-gray-500 pb-1.5">
                                                                <div class="w-full border-t border-dashed border-gray-150 dark:border-gray-800/60 pt-1 flex justify-between"><span>100%</span></div>
                                                                <div class="w-full border-t border-dashed border-gray-150 dark:border-gray-800/60 pt-1 flex justify-between"><span>75%</span></div>
                                                                <div class="w-full border-t border-dashed border-gray-150 dark:border-gray-800/60 pt-1 flex justify-between"><span>50%</span></div>
                                                                <div class="w-full border-t border-dashed border-gray-150 dark:border-gray-800/60 pt-1 flex justify-between"><span>25%</span></div>
                                                                <div class="w-full border-b border-gray-200 dark:border-gray-800 pb-1 flex justify-between"><span>0%</span></div>
                                                            </div>

                                                            {{-- ALL BARS SIDE-BY-SIDE --}}
                                                            <div x-show="activeMatchId === 'all'" 
                                                                 x-transition.opacity.duration.300ms
                                                                 class="absolute inset-0 flex items-end justify-around z-10 pb-0.5 px-6">
                                                                 @foreach($top5Matches as $match)
                                                                     @php
                                                                         if ($match->score >= 80) {
                                                                             $barBg = 'background: linear-gradient(to top, #047857, #10b981);';
                                                                             $textColor = 'text-emerald-500';
                                                                         } elseif ($match->score >= 60) {
                                                                             $barBg = 'background: linear-gradient(to top, #084577, #3b82f6);';
                                                                             $textColor = 'text-[#0B5694] dark:text-blue-400';
                                                                         } elseif ($match->score >= 40) {
                                                                             $barBg = 'background: linear-gradient(to top, #4338ca, #6366f1);';
                                                                             $textColor = 'text-indigo-500';
                                                                         } else {
                                                                             $barBg = 'background: linear-gradient(to top, #b45309, #f59e0b);';
                                                                             $textColor = 'text-amber-500';
                                                                         }
                                                                     @endphp
                                                                     <div class="flex flex-col items-center w-14 justify-end group h-full relative">
                                                                         {{-- Score display above the bar --}}
                                                                         <span class="absolute text-[10px] font-black {{ $textColor }} pointer-events-none transform -translate-y-5 transition-all group-hover:scale-110"
                                                                               style="bottom: {{ $match->score }}%;">
                                                                             {{ $match->score }}%
                                                                         </span>
                                                                         
                                                                         {{-- The Bar itself --}}
                                                                         <div @click="showBreakdown = '{{ $match->id }}'; $nextTick(() => { if (window.MathJax) { MathJax.typesetPromise(); } })"
                                                                              class="w-10 cursor-pointer rounded-t-lg transition-all duration-300 ease-out shadow-md hover:ring-4 hover:ring-blue-500/20 active:scale-95"
                                                                              style="height: {{ max(5, $match->score) }}%; {{ $barBg }}"
                                                                              title="Click to view breakdown for {{ $match->office->name }}">
                                                                         </div>
                                                                     </div>
                                                                 @endforeach
                                                             </div>

                                                             {{-- FOCUSED SINGLE BAR --}}
                                                             @foreach($top5Matches as $match)
                                                                 @php
                                                                     if ($match->score >= 80) {
                                                                         $barBg = 'background: linear-gradient(to top, #047857, #10b981);';
                                                                         $textColor = 'text-emerald-500';
                                                                     } elseif ($match->score >= 60) {
                                                                         $barBg = 'background: linear-gradient(to top, #084577, #3b82f6);';
                                                                         $textColor = 'text-[#0B5694] dark:text-blue-400';
                                                                     } elseif ($match->score >= 40) {
                                                                         $barBg = 'background: linear-gradient(to top, #4338ca, #6366f1);';
                                                                         $textColor = 'text-indigo-500';
                                                                     } else {
                                                                         $barBg = 'background: linear-gradient(to top, #b45309, #f59e0b);';
                                                                         $textColor = 'text-amber-500';
                                                                     }
                                                                 @endphp
                                                                 <div x-show="activeMatchId === '{{ $match->id }}'" 
                                                                      x-transition.opacity.duration.300ms
                                                                      class="absolute inset-0 flex items-end justify-center z-10 pb-0.5">
                                                                     
                                                                     <div class="flex flex-col items-center w-24 justify-end group h-full relative">
                                                                         {{-- Score display above the bar --}}
                                                                         <span class="absolute text-xs font-black {{ $textColor }} pointer-events-none transform -translate-y-5 transition-all group-hover:scale-115"
                                                                               style="bottom: {{ $match->score }}%;">
                                                                             {{ $match->score }}%
                                                                         </span>
                                                                         
                                                                         {{-- The Bar itself --}}
                                                                         <div @click="showBreakdown = '{{ $match->id }}'; $nextTick(() => { if (window.MathJax) { MathJax.typesetPromise(); } })"
                                                                              class="w-16 cursor-pointer rounded-t-2xl transition-all duration-500 ease-out shadow-xl hover:ring-8 hover:ring-blue-500/10 active:scale-95"
                                                                              style="height: {{ max(5, $match->score) }}%; {{ $barBg }}"
                                                                              title="Click to view breakdown for {{ $match->office->name }}">
                                                                         </div>
                                                                     </div>
                                                                 </div>
                                                             @endforeach
                                                         </div>
                                                         
                                                         {{-- X-Axis Labels --}}
                                                         <div class="mt-6 border-t border-gray-200 dark:border-gray-800 pt-4 text-center">
                                                             {{-- Labels when 'All' is active --}}
                                                             <div x-show="activeMatchId === 'all'" 
                                                                  x-transition.opacity.duration.200ms
                                                                  class="flex justify-around px-6">
                                                                 @foreach($top5Matches as $match)
                                                                     <div class="w-14 text-center">
                                                                         <span class="block text-[9px] sm:text-[10px] font-black text-gray-700 dark:text-gray-300 uppercase tracking-wider truncate" title="{{ $match->office->name }}">
                                                                             {{ $match->office->name }}
                                                                         </span>
                                                                         @if($confirmedMatch && $confirmedMatch->id === $match->id)
                                                                             <span class="inline-block mt-1 bg-emerald-50 dark:bg-emerald-950/30 text-emerald-500 border border-emerald-500/20 px-1 py-0.5 rounded text-[7px] tracking-widest font-black uppercase shadow-sm">Assigned</span>
                                                                         @endif
                                                                     </div>
                                                                 @endforeach
                                                             </div>

                                                             {{-- Label when focused activeMatchId is chosen --}}
                                                             @foreach($top5Matches as $match)
                                                                 <div x-show="activeMatchId === '{{ $match->id }}'" 
                                                                      x-transition.opacity.duration.200ms
                                                                      class="w-full text-center">
                                                                     <span class="block text-xs font-black text-gray-800 dark:text-gray-200 uppercase tracking-widest leading-relaxed truncate" title="{{ $match->office->name }}">
                                                                         {{ $match->office->name }}
                                                                     </span>
                                                                     @if($confirmedMatch && $confirmedMatch->id === $match->id)
                                                                         <span class="inline-block mt-2 bg-emerald-50 dark:bg-emerald-950/30 text-emerald-500 border border-emerald-500/20 px-3 py-1 rounded-xl text-[9px] tracking-widest font-black uppercase shadow-sm">Current Assignment</span>
                                                                     @endif
                                                                 </div>
                                                             @endforeach
                                                         </div>
                                                     @foreach($top5Matches as $match)
                                                        @php
                                                            $breakdown = $match->breakdown ? json_decode($match->breakdown, true) : null;
                                                        @endphp
                                                        {{-- MODAL FOR FULL BREAKDOWN --}}
                                                        @if($breakdown)
                                                             <div x-show="showBreakdown === '{{ $match->id }}'" 
                                                                  x-cloak
                                                                  class="fixed inset-0 z-[300] flex items-center justify-center p-3 sm:p-4 bg-gray-900/60 dark:bg-black/80 backdrop-blur-sm"
                                                                  x-transition:enter="transition ease-out duration-300"
                                                                  x-transition:enter-start="opacity-0"
                                                                  x-transition:enter-end="opacity-100"
                                                                  x-transition:leave="transition ease-in duration-200"
                                                                  x-transition:leave-start="opacity-100"
                                                                  x-transition:leave-end="opacity-0"
                                                                  @keydown.escape.window="showBreakdown = null"
                                                                  style="display: none;"
                                                             >
                                                                 <div class="w-full max-w-[calc(100%-0.5rem)] sm:max-w-lg lg:max-w-2xl bg-white dark:bg-gray-900 rounded-[24px] sm:rounded-[32px] border border-gray-100 dark:border-gray-800 shadow-2xl overflow-hidden flex flex-col max-h-[85vh] transition-all duration-300 text-left"
                                                                      x-transition:enter="transition ease-out duration-300"
                                                                      x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                                                                      x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                                                      @click.away="showBreakdown = null"
                                                                 >
                                                                     {{-- Modal Header --}}
                                                                     <div class="px-5 sm:px-8 py-4 sm:py-6 border-b border-gray-100 dark:border-gray-800 shrink-0 flex items-center justify-between gap-3">
                                                                         <div class="min-w-0">
                                                                             <h2 class="text-xs sm:text-sm font-black text-gray-900 dark:text-white uppercase tracking-tight truncate">{{ $applicant->full_name }} &bull; {{ $match->office->name }}</h2>
                                                                         </div>
                                                                         <button @click="showBreakdown = null" class="p-2 shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors active:scale-90">
                                                                             <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                                                         </button>
                                                                     </div>
  
                                                                     {{-- Modal Body (Scrollable) --}}
                                                                     <div class="px-5 sm:px-8 py-4 sm:py-6 overflow-y-auto custom-scrollbar flex-1 text-sm text-gray-600 dark:text-gray-300 space-y-6 leading-relaxed font-sans">
                                                                        {{-- Status Pills --}}
                                                                        <div class="flex flex-wrap gap-2">
                                                                            @php
                                                                                $score = $match->score;
                                                                                if ($score >= 80) {
                                                                                    $recommendationText = "Highly Recommended";
                                                                                    $qualityClass = "bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/50";
                                                                                } elseif ($score >= 60) {
                                                                                    $recommendationText = "Recommended";
                                                                                    $qualityClass = "bg-blue-50/70 dark:bg-blue-950/30 text-blue-600 dark:text-blue-400 border border-blue-100 dark:border-blue-900/50";
                                                                                } elseif ($score >= 40) {
                                                                                    $recommendationText = "Needs Training";
                                                                                    $qualityClass = "bg-indigo-50 dark:bg-indigo-950/30 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-900/50";
                                                                                } else {
                                                                                    $recommendationText = "Not Recommended";
                                                                                    $qualityClass = "bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-400 border border-amber-100 dark:border-amber-900/50";
                                                                                }
                                                                            @endphp
                                                                            <span class="px-3 py-1 {{ $qualityClass }} font-extrabold uppercase rounded-full text-[9px] tracking-wider">Recommendation: {{ $recommendationText }}</span>
 
                                                                            @if(!$breakdown['is_recommended'] || $breakdown['is_recommended'] === 'Needs More Training' || $breakdown['is_recommended'] === false)
                                                                                <span class="px-3 py-1 bg-rose-50 dark:bg-rose-950/30 text-rose-600 dark:text-rose-400 font-extrabold uppercase rounded-full border border-rose-100 dark:border-rose-900 text-[9px] tracking-wider">50% Penalty Applied</span>
                                                                            @endif
 
                                                                            <span class="px-3 py-1 bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300 font-extrabold uppercase rounded-full border border-gray-200 dark:border-gray-700 text-[9px] tracking-wider">Verification: {{ $match->confidence }}</span>
                                                                        </div>
 
                                                                         {{-- Structured HTML Breakdown --}}
                                                                         <div class="markdown-content text-left dark:text-gray-300 mt-4 overflow-x-auto">
                                                                            <div class="p-5 bg-blue-50/50 dark:bg-blue-950/20 border border-blue-100 dark:border-blue-900/50 rounded-2xl mb-6 space-y-3">
                                                                                <h4 class="text-xs font-bold uppercase tracking-wider text-[#0B5694] dark:text-blue-400">TOPSIS Concept Definition</h4>
                                                                                <div class="space-y-3 text-sm font-semibold leading-relaxed text-gray-600 dark:text-gray-300">
                                                                                    <div class="flex items-start gap-2">
                                                                                        <span class="text-[#0B5694] dark:text-blue-400 font-extrabold mt-0.5">&bull;</span>
                                                                                        <span><strong>Positive Ideal Solution (\(PIS\) / \(v^*\)):</strong> The theoretical perfect score. In this engine, it is configured at <strong>7.5</strong> (a max skill score of 5 boosted by the 1.5x supervisor verification multiplier).</span>
                                                                                    </div>
                                                                                    <div class="flex items-start gap-2">
                                                                                        <span class="text-[#0B5694] dark:text-blue-400 font-extrabold mt-0.5">&bull;</span>
                                                                                        <span><strong>Negative Ideal Solution (\(NIS\) / \(v^-\)):</strong> The theoretical worst possible score, configured at <strong>0.0</strong>.</span>
                                                                                    </div>
                                                                                    <div class="flex items-start gap-2">
                                                                                        <span class="text-[#0B5694] dark:text-blue-400 font-extrabold mt-0.5">&bull;</span>
                                                                                        <span><strong>Closeness Coefficient (\(CC_i\)):</strong> The final relative closeness score ranging from 0% to 100%. A higher percentage indicates that the candidate's scores are closer to the Positive Ideal than the Negative Ideal.</span>
                                                                                    </div>
                                                                                </div>
                                                                            </div>

                                                                            @if(isset($breakdown['skills_breakdown']) && count($breakdown['skills_breakdown']) > 0)
                                                                                <h3>1. Intern Scores & Multipliers</h3>
                                                                                <ul>
                                                                                    @foreach($breakdown['skills_breakdown'] as $skillItem)
                                                                                        @php
                                                                                            $base = $skillItem['self_reported'] ?? 0;
                                                                                            $verified = $skillItem['verified'] ?? null;
                                                                                            $isVerified = ($verified !== null);
                                                                                            
                                                                                            $sourceDesc = "";
                                                                                            if ($isVerified) {
                                                                                                $sourceDesc = "verified at <strong>" . $verified . "</strong> (giving " . $verified . " &times; 1.5 = " . $skillItem['effective_score'] . " due to supervisor verification)";
                                                                                            } elseif ($skillItem['resume_match'] ?? false) {
                                                                                                if ($base > 0) {
                                                                                                    $sourceDesc = "self-reported at <strong>" . $base . "</strong> with resume match (giving " . $base . " &times; 1.2 = " . $skillItem['effective_score'] . " due to resume triangulation)";
                                                                                                } else {
                                                                                                    $sourceDesc = "found in resume text (giving 0 &times; 1.2 = 0)";
                                                                                                }
                                                                                            } else {
                                                                                                $sourceDesc = "self-reported at <strong>" . $base . "</strong> (giving " . $base . " &times; 1.0 = " . $skillItem['effective_score'] . ")";
                                                                                            }
                                                                                        @endphp
                                                                                        <li><code>{{ strtoupper($skillItem['skill']) }}</code> is {!! $sourceDesc !!}.</li>
                                                                                    @endforeach
                                                                                </ul>

                                                                                @php
                                                                                    $pisTerms = [];
                                                                                    $nisTerms = [];
                                                                                    $pisSimpTerms = [];
                                                                                    $nisSimpTerms = [];
                                                                                    $pisSumSq = 0;
                                                                                    $nisSumSq = 0;
                                                                                    foreach($breakdown['skills_breakdown'] as $skillItem) {
                                                                                        $wj = $skillItem['priority'];
                                                                                        $xij = $skillItem['effective_score'];
                                                                                        
                                                                                        $diffPis = round(7.5 - $xij, 2);
                                                                                        $diffNis = round($xij - 0.0, 2);
                                                                                        
                                                                                        $termPisVal = ($wj * $diffPis) ** 2;
                                                                                        $termNisVal = ($wj * $diffNis) ** 2;
                                                                                        
                                                                                        $pisSumSq += $termPisVal;
                                                                                        $nisSumSq += $termNisVal;
                                                                                        
                                                                                        $pisTerms[] = "({$wj} \\cdot (7.5 - {$xij}))^2";
                                                                                        $nisTerms[] = "({$wj} \\cdot ({$xij} - 0.0))^2";
                                                                                        
                                                                                        $pisSimpTerms[] = round($wj * $diffPis, 2) . "^2";
                                                                                        $nisSimpTerms[] = round($wj * $diffNis, 2) . "^2";
                                                                                    }
                                                                                    
                                                                                    $dPlus = round(sqrt($pisSumSq), 2);
                                                                                    $dMinus = round(sqrt($nisSumSq), 2);
                                                                                    
                                                                                    $cc = 0;
                                                                                    if (($dPlus + $dMinus) > 0) {
                                                                                        $cc = round(($dMinus / ($dPlus + $dMinus)) * 100, 2);
                                                                                    }
                                                                                    
                                                                                    $behScore = $breakdown['behavioral_score'] ?? 3;
                                                                                    $behPct = $breakdown['behavioral_percentage'] ?? 60.0;
                                                                                    
                                                                                    $skillContribution = round($cc * 0.8, 2);
                                                                                    $behavioralContribution = round($behPct * 0.2, 2);
                                                                                    $rawScore = round($skillContribution + $behavioralContribution, 2);
                                                                                    
                                                                                    $isRecommended = $breakdown['is_recommended'] ?? true;
                                                                                    $recommendedText = ($isRecommended === true || $isRecommended === 1 || $isRecommended === "1" || $isRecommended === "Recommended") ? "Recommended" : "Needs More Training";
                                                                                @endphp

                                                                                 <h3>2. Geometric Distances</h3>
                                                                                 <p>Distance to Positive Ideal (\(D^+\shrink\)):</p>
                                                                                 <div class="my-3 overflow-x-auto overflow-y-hidden text-center font-semibold text-gray-800 dark:text-gray-200 pb-2">
                                                                                     $$D^+ = \sqrt{ {!! implode(" + ", $pisTerms) !!} } = \sqrt{ {!! implode(" + ", $pisSimpTerms) !!} } = \sqrt{ {{ round($pisSumSq, 2) }} } \approx {{ $dPlus }}$$
                                                                                 </div>

                                                                                 <p>Distance to Negative Ideal (\(D^-\shrink\)):</p>
                                                                                 <div class="my-3 overflow-x-auto overflow-y-hidden text-center font-semibold text-gray-800 dark:text-gray-200 pb-2">
                                                                                     $$D^- = \sqrt{ {!! implode(" + ", $nisTerms) !!} } = \sqrt{ {!! implode(" + ", $nisSimpTerms) !!} } = \sqrt{ {{ round($nisSumSq, 2) }} } \approx {{ $dMinus }}$$
                                                                                 </div>

                                                                                 <h3>3. Closeness Coefficient (\(CC_i\))</h3>
                                                                                 <div class="my-3 overflow-x-auto overflow-y-hidden text-center font-semibold text-gray-800 dark:text-gray-200 pb-2">
                                                                                     $$CC_i = \frac{D^-}{D^+ + D^-} = \frac{ {{ $dMinus }} }{ {{ $dPlus }} + {{ $dMinus }} } = \frac{ {{ $dMinus }} }{ {{ round($dPlus + $dMinus, 2) }} } \approx {{ $cc }}\%$$
                                                                                 </div>

                                                                                <h3>4. Behavioral Integration</h3>
                                                                                <p>Behavioral score is <strong><code>{{ $behScore }}</code></strong> ({{ $behPct }}%).</p>

                                                                                 <h3>5. Weighted Score Formula</h3>
                                                                                 <div class="my-3 overflow-x-auto overflow-y-hidden text-center font-semibold text-gray-800 dark:text-gray-200 pb-2">
                                                                                     $$\text{Base Score} = ({{ $cc }}\% \times 0.8) + ({{ $behPct }}\% \times 0.2) = {{ $skillContribution }}\% + {{ $behavioralContribution }}\% = {{ $rawScore }}\%$$
                                                                                 </div>

                                                                                @if($recommendedText === "Needs More Training")
                                                                                    @php $finalScore = round($rawScore * 0.5); @endphp
                                                                                     <h3>6. Recommendation Penalty (Needs More Training)</h3>
                                                                                     <p>"Needs More Training" triggers a strict <strong>50% reduction</strong>:</p>
                                                                                     <div class="my-3 overflow-x-auto overflow-y-hidden text-center font-semibold text-gray-800 dark:text-gray-200 pb-2">
                                                                                         $$\text{Final Score} = {{ $rawScore }}\% \times 0.5 = {{ round($rawScore * 0.5, 2) }}\% \rightarrow \mathbf{ {{ $finalScore }}\% }$$
                                                                                     </div>
                                                                                 @else
                                                                                     @php $finalScore = round($rawScore); @endphp
                                                                                     <h3>6. Final Score</h3>
                                                                                     <div class="my-3 overflow-x-auto overflow-y-hidden text-center font-semibold text-gray-800 dark:text-gray-200 pb-2">
                                                                                         $$\text{Final Score} = {{ $rawScore }}\% \rightarrow \mathbf{ {{ $finalScore }}\% }$$
                                                                                     </div>
                                                                                 @endif
                                                                            @else
                                                                                <div class="p-5 bg-amber-50/50 dark:bg-amber-950/20 border border-amber-100 dark:border-amber-900/50 rounded-2xl mb-6">
                                                                                    <h4 class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Direct Assignment / Fallback Score</h4>
                                                                                    <p class="text-sm font-semibold text-gray-600 dark:text-gray-300 mt-2 leading-relaxed">
                                                                                        This matching result was created directly or manually mapped. The TOPSIS mathematical analysis breakdown is not applicable for this record.
                                                                                    </p>
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                    </div>

                                                                     {{-- Modal Footer --}}
                                                                     <div class="px-5 sm:px-8 py-4 sm:py-6 border-t border-gray-100 dark:border-gray-800 shrink-0 bg-gray-50 dark:bg-gray-900/50 flex flex-col sm:flex-row items-center justify-between gap-4">
                                                                         <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider shrink-0">Final Score: {{ $match->score }}%</span>
                                                                         <div class="flex items-center gap-2 sm:gap-3 w-full sm:w-auto justify-end">
                                                                            @if(!$confirmedMatch)
                                                                                <form action="{{ route('head-supervisor.matching.finalize') }}" method="POST" class="inline">
                                                                                    @csrf
                                                                                    <input type="hidden" name="applicant_names[]" value="{{ $applicant->full_name }}">
                                                                                    <input type="hidden" name="office_id" value="{{ $match->office_id }}">
                                                                                    <button type="submit" class="px-6 py-2.5 bg-[#0B5694] hover:bg-[#084577] dark:bg-blue-600 dark:hover:bg-blue-500 text-white font-bold uppercase text-[10px] tracking-wider rounded-xl transition-all shadow-md active:scale-95">Assign</button>
                                                                                </form>
                                                                            @elseif($confirmedMatch->id === $match->id)
                                                                                <span class="text-[10px] font-bold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider bg-emerald-100 dark:bg-emerald-950/50 px-4 py-2 rounded-xl border border-emerald-200 dark:border-emerald-800">Current Assignment</span>
                                                                            @endif
                                                                            <button @click="showBreakdown = null" type="button" class="px-6 py-2.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 font-bold uppercase text-[10px] tracking-wider rounded-xl transition-all shadow-md active:scale-95">Close</button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                @else
                                                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">No matching office configurations found.</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{-- CLOSE hidden lg:block wrapper --}}
                </div>

                {{-- MOBILE & TABLET CARD LAYOUT (hidden lg:block is the table) --}}
                @php
                    $unconfirmedNames = $applicants->filter(fn($a) => !\App\Models\MatchResult::where('applicant_name', $a->full_name)->where('is_confirmed', true)->exists())->pluck('full_name')->toArray();
                    $unconfirmedCount = count($unconfirmedNames);
                @endphp
                @if($unconfirmedCount > 0)
                <div class="lg:hidden flex items-center justify-between gap-3 mb-3 sm:mb-4 px-1 transition-all duration-300 ease-out">
                    <label class="flex items-center gap-2.5 sm:gap-3 cursor-pointer min-h-[44px] select-none">
                        <input type="checkbox"
                               @click="selectAll({{ json_encode($unconfirmedNames) }})"
                               :checked="selectedApplicants.length === {{ $unconfirmedCount }} && selectedApplicants.length > 0"
                               class="w-[22px] h-[22px] rounded-lg border-gray-300 accent-[#0B5694] cursor-pointer shadow-sm transition-all">
                        <span class="text-[10px] sm:text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Select All</span>
                        <span class="text-[9px] sm:text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider" x-show="selectedApplicants.length > 0">
                            (<span x-text="selectedApplicants.length"></span> selected)
                        </span>
                    </label>
                    <button @click="selectedApplicants = []" 
                            x-show="selectedApplicants.length > 0"
                            class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-red-400 hover:text-red-600 transition-colors min-h-[44px] flex items-center px-2 active:scale-95">
                        Clear
                    </button>
                </div>
                @endif
                <div class="lg:hidden grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 transition-all duration-300 ease-out">
                    @foreach($applicants as $applicant)
                        @php 
                            $matches = $results->get($applicant->full_name, collect());
                            $confirmedMatch = $matches->where('is_confirmed', true)->first();
                            $bestMatch = $matches->sortByDesc('score')->first();
                            $displayMatch = $confirmedMatch ?? $bestMatch;

                            $confidenceText = $displayMatch->confidence ?? 'Low';
                            if ($confidenceText === 'High') {
                                $confidenceColor = 'text-emerald-500'; $confidenceBg = 'bg-emerald-50 dark:bg-emerald-950/30';
                            } elseif ($confidenceText === 'Medium') {
                                $confidenceColor = 'text-blue-500'; $confidenceBg = 'bg-blue-50 dark:bg-blue-950/30';
                            } else {
                                $confidenceColor = 'text-amber-500'; $confidenceBg = 'bg-amber-50 dark:bg-amber-950/30';
                            }

                             if (!$displayMatch) {
                                 $scoreText = 'N/A'; $scoreColor = 'text-gray-400'; $barColor = 'bg-gray-200';
                             } elseif ($displayMatch->score >= 80) {
                                 $scoreColor = 'text-emerald-500'; $barColor = 'bg-emerald-500';
                             } elseif ($displayMatch->score >= 60) {
                                 $scoreColor = 'text-blue-500'; $barColor = 'bg-blue-500';
                             } elseif ($displayMatch->score >= 40) {
                                 $scoreColor = 'text-indigo-500'; $barColor = 'bg-indigo-500';
                             } else {
                                 $scoreColor = 'text-amber-500'; $barColor = 'bg-amber-500';
                             }

                            $top5Matches = $matches->sortByDesc('score')->take(5);
                            $skillToCategory = \App\Models\AssessmentItem::active()->pluck('category', 'competency_text')->mapWithKeys(fn($cat, $txt) => [trim($txt) => $cat])->toArray();
                            $verifiedSkills = $applicant->evaluation?->verified_skills ?? [];
                        @endphp

                        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden transition-all flex flex-col"
                             :class="isSelected(@js($applicant->full_name)) ? 'ring-2 ring-[#0B5694] dark:ring-blue-500 bg-blue-50/20 dark:bg-blue-950/20' : ''">
                            
                            {{-- Card Header: Select + Name + Score --}}
                            <div class="p-3 sm:p-4 flex items-start gap-2 sm:gap-3">
                                <div class="shrink-0 mt-0.5 min-w-[22px] min-h-[22px] flex items-center justify-center">
                                    @if(!$confirmedMatch)
                                        <input type="checkbox" value="{{ $applicant->full_name }}" x-model="selectedApplicants" class="w-[22px] h-[22px] rounded-lg border-gray-300 accent-[#0B5694] cursor-pointer">
                                    @else
                                        <div class="text-emerald-600" title="Assigned">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-1.5 sm:gap-2">
                                        <p class="text-xs sm:text-sm font-extrabold text-gray-800 dark:text-gray-100 truncate uppercase tracking-tight">{{ $applicant->full_name }}</p>
                                        @if(!empty($applicant->resume_path))
                                            <button @click="openResume('{{ route('head-supervisor.matching.resume', $applicant) }}')" class="shrink-0 text-blue-500 active:scale-90 transition-transform min-w-[44px] min-h-[44px] flex items-center justify-center -m-2 sm:-m-1.5" aria-label="Preview resume">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/></svg>
                                            </button>
                                        @endif
                                    </div>
                                    <p class="text-[10px] sm:text-[11px] text-gray-400 dark:text-gray-500 font-bold uppercase tracking-wider mt-0.5 truncate">{{ $applicant->university }}</p>
                                </div>
                                @if($displayMatch)
                                    <div class="shrink-0 text-right">
                                        <span class="text-base sm:text-lg font-black {{ $scoreColor }}">{{ $displayMatch->score }}%</span>
                                    </div>
                                @endif
                            </div>

                            {{-- Card Body: Match + Score Bar + Verification --}}
                            <div class="px-3 sm:px-4 pb-1 sm:pb-2 space-y-1.5 sm:space-y-2 flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="text-[9px] sm:text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Match</span>
                                    <span class="text-[10px] sm:text-xs font-black text-gray-800 dark:text-gray-100 uppercase tracking-tight truncate ml-2">{{ $displayMatch ? $displayMatch->office->name : 'No Results' }}</span>
                                </div>
                                
                                @if($displayMatch)
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 h-2 sm:h-2.5 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                                        <div class="h-full {{ $barColor }} rounded-full transition-all duration-700" style="width: {{ $displayMatch->score }}%"></div>
                                    </div>
                                </div>
                                @endif

                                <div class="flex items-center justify-between pt-0.5 sm:pt-1">
                                    <span class="text-[9px] sm:text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Verification</span>
                                    <span class="{{ $confidenceBg }} {{ $confidenceColor }} text-[9px] sm:text-[10px] font-bold px-2 sm:px-3 py-0.5 sm:py-1 rounded-full uppercase tracking-wider border border-current">{{ $confidenceText }}</span>
                                </div>
                            </div>

                            {{-- Card Footer: Action Buttons (Thumb Zone at bottom) --}}
                            <div class="px-3 sm:px-4 pb-3 sm:pb-4 pt-2 sm:pt-3 mt-auto flex gap-2 border-t border-gray-50 dark:border-gray-800/50">
                                <button @click="if (expanded === '{{ $applicant->applicant_id }}') { expanded = null; } else { expanded = '{{ $applicant->applicant_id }}'; evaluating = null; }" 
                                        class="flex-1 min-h-[44px] flex items-center justify-center gap-1.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-[9px] sm:text-[10px] font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider active:bg-gray-100 dark:active:bg-gray-700 transition active:scale-[0.97]"
                                        :class="expanded === '{{ $applicant->applicant_id }}' ? 'border-[#0B5694] text-[#0B5694] dark:text-blue-400 dark:border-blue-500 bg-blue-50 dark:bg-blue-950/20' : ''">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><line x1="18" x2="18" y1="20" y2="10"/><line x1="12" x2="12" y1="20" y2="4"/><line x1="6" x2="6" y1="20" y2="14"/></svg>
                                    <span>Analysis</span>
                                </button>
                                @if(!$confirmedMatch)
                                <button @click="if (evaluating === '{{ $applicant->applicant_id }}') { evaluating = null; } else { evaluating = '{{ $applicant->applicant_id }}'; expanded = null; }" 
                                        class="flex-1 min-h-[44px] flex items-center justify-center gap-1.5 bg-[#0B5694] dark:bg-blue-600 text-white rounded-xl text-[9px] sm:text-[10px] font-bold uppercase tracking-wider active:bg-[#084577] dark:active:bg-blue-700 transition shadow-sm active:scale-[0.97]"
                                        :class="evaluating === '{{ $applicant->applicant_id }}' ? 'ring-2 ring-[#0B5694]/30' : ''">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    Evaluate
                                </button>
                                @endif
                            </div>

                            {{-- Evaluation Expandable (Mobile) --}}
                            <div x-show="evaluating === '{{ $applicant->applicant_id }}'" x-cloak class="border-t border-gray-100 dark:border-gray-800 bg-blue-50/20 dark:bg-blue-900/10">
                                <div class="p-3 sm:p-4">
                                    <form action="{{ route('head-supervisor.matching.evaluate', $applicant) }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div class="space-y-3 sm:space-y-4" x-data="{ skillFilter: 'all' }">
                                            <div class="flex flex-col gap-1.5 sm:gap-2">
                                                <h5 class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-[#0B5694] dark:text-blue-400">Skill Verification</h5>
                                                <select x-model="skillFilter" 
                                                        class="w-full bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 text-gray-700 dark:text-gray-300 text-[10px] font-bold uppercase tracking-wider rounded-xl px-4 py-2.5 shadow-sm focus:ring-2 focus:ring-[#0B5694]/20 outline-none transition-all">
                                                    <option value="all">All Sections</option>
                                                    <option value="I. Digital and Technical Skills">I. Digital & Technical Skills</option>
                                                    <option value="II. Administrative and Office Skills">II. Administrative & Office Skills</option>
                                                    <option value="III. Analytical and Problem-Solving Skills">III. Analytical & Problem-Solving</option>
                                                    <option value="IV. Communication and Interpersonal Skills">IV. Communication & Interpersonal</option>
                                                    <option value="V. Work Attitude and Professional Values">V. Work Attitude & Values</option>
                                                </select>
                                            </div>

                                            <div class="overflow-x-auto max-h-48 overflow-y-auto custom-scrollbar border border-gray-100 dark:border-gray-800 rounded-xl">
                                                <table class="min-w-[400px] w-full text-left">
                                                    <thead class="bg-gray-50 dark:bg-gray-950 sticky top-0 z-10">
                                                        <tr class="border-b border-gray-100 dark:border-gray-800">
                                                            <th class="px-3 py-2.5 text-[9px] font-black uppercase tracking-wider text-gray-500">Skill</th>
                                                            <th class="px-3 py-2.5 text-[9px] font-black uppercase tracking-wider text-gray-500 text-center">Self</th>
                                                            <th class="px-3 py-2.5 text-[9px] font-black uppercase tracking-wider text-gray-500 text-right">Score</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 bg-white dark:bg-gray-900 text-[11px]">
                                                        @foreach(array_merge($applicant->tech_skills, $applicant->soft_skills) as $skill => $score)
                                                            @php $itemCategory = $skillToCategory[trim($skill)] ?? 'Other'; @endphp
                                                            <tr x-show="skillFilter === 'all' || skillFilter === '{{ $itemCategory }}'">
                                                                <td class="px-3 py-2.5 font-bold text-gray-700 dark:text-gray-300 uppercase tracking-tight">{{ $skill }}</td>
                                                                <td class="px-3 py-2.5 text-center"><span class="inline-block bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded text-[9px] font-extrabold">{{ $score }}/5</span></td>
                                                                <td class="px-3 py-2.5 text-right">
                                                                    <select name="verified_skills[{{ $skill }}]" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-2 py-1.5 text-[10px] font-bold focus:ring-2 focus:ring-[#0B5694]/20 outline-none">
                                                                        @for($i=1; $i<=5; $i++)
                                                                            <option value="{{ $i }}" {{ ($verifiedSkills[$skill] ?? $score) == $i ? 'selected' : '' }}>{{ $i }}</option>
                                                                        @endfor
                                                                    </select>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>

                                            <div class="space-y-2 sm:space-y-3">
                                                <div>
                                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1.5">Interview Notes</label>
                                                    <textarea name="interview_notes" rows="3" class="w-full bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-xl p-3 text-xs font-bold focus:ring-2 focus:ring-[#0B5694]/20 outline-none" placeholder="Add observations...">{{ $applicant->evaluation?->interview_notes }}</textarea>
                                                </div>
                                                <div>
                                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1.5">Resume (PDF)</label>
                                                    <input type="file" name="resume_pdf" accept="application/pdf" class="w-full text-[10px] font-bold text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-[10px] file:font-extrabold file:bg-blue-50 file:text-[#0B5694] dark:file:bg-blue-900/30 dark:file:text-blue-400">
                                                </div>
                                                <div class="grid grid-cols-2 gap-2 sm:gap-3">
                                                    <div>
                                                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Behavioral</label>
                                                        <select name="behavioral_score" class="w-full bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg p-2.5 text-[10px] font-bold uppercase focus:ring-2 focus:ring-[#0B5694]/20 outline-none min-h-[44px]">
                                                            @for($i=1; $i<=5; $i++)
                                                                <option value="{{ $i }}" {{ ($applicant->evaluation?->behavioral_score ?? 3) == $i ? 'selected' : '' }}>{{ $i }}</option>
                                                            @endfor
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Recommend</label>
                                                        <select name="is_recommended" class="w-full bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg p-2.5 text-[10px] font-bold uppercase focus:ring-2 focus:ring-[#0B5694]/20 outline-none min-h-[44px]">
                                                            <option value="1" {{ ($applicant->evaluation?->is_recommended ?? true) ? 'selected' : '' }}>Yes</option>
                                                            <option value="0" {{ !($applicant->evaluation?->is_recommended ?? true) ? 'selected' : '' }}>No</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <button type="submit" class="w-full min-h-[48px] bg-[#0B5694] dark:bg-blue-600 hover:bg-[#084577] dark:hover:bg-blue-500 text-white font-bold uppercase text-[11px] tracking-wider rounded-xl transition-all active:scale-95 shadow-md shadow-blue-500/10 flex items-center justify-center gap-2">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                                Update Evaluation
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            {{-- Analysis Expandable (Mobile & Tablet - Compact) --}}
                            <div x-show="expanded === '{{ $applicant->applicant_id }}'" x-cloak class="border-t border-gray-100 dark:border-gray-800 bg-gray-50/30 dark:bg-gray-950/30">
                                <div class="p-3 sm:p-4" x-data="{ activeMatchId: 'all' }">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-3 mb-3">
                                        <h5 class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-[#0B5694] dark:text-blue-400">Match Analysis</h5>
                                        <select x-model="activeMatchId" 
                                                class="w-full sm:w-auto bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 text-gray-700 dark:text-gray-300 text-[10px] font-bold uppercase tracking-wider rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-[#0B5694]/20 outline-none min-h-[44px]">
                                            <option value="all">All Top 5</option>
                                            @foreach($top5Matches as $match)
                                                <option value="{{ $match->id }}">{{ $match->office->name }} ({{ $match->score }}%)</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    @if($matches->count() > 0)
                                        <div class="relative w-full max-w-sm mx-auto pt-2 pb-2">
                                            <div class="relative h-32 sm:h-36 w-full">
                                                <div class="absolute inset-0 flex flex-col justify-between pointer-events-none text-[7px] sm:text-[8px] font-bold text-gray-400 dark:text-gray-500 pb-0.5">
                                                    <div class="w-full border-t border-dashed border-gray-150 dark:border-gray-800/60 pt-0.5">100%</div>
                                                    <div class="w-full border-t border-dashed border-gray-150 dark:border-gray-800/60 pt-0.5">50%</div>
                                                    <div class="w-full border-b border-gray-200 dark:border-gray-800 pb-0.5">0%</div>
                                                </div>

                                                <div x-show="activeMatchId === 'all'" x-transition.opacity.duration.300ms
                                                     class="absolute inset-0 flex items-end justify-around z-10 pb-0.5 px-3 sm:px-4">
                                                    @foreach($top5Matches as $match)
                                                        @php
                                                            if ($match->score >= 80) {
                                                                $barBgMobile = 'background: linear-gradient(to top, #047857, #10b981);';
                                                                $textColorMobile = 'text-emerald-500';
                                                            } elseif ($match->score >= 60) {
                                                                $barBgMobile = 'background: linear-gradient(to top, #084577, #3b82f6);';
                                                                $textColorMobile = 'text-[#0B5694] dark:text-blue-400';
                                                            } elseif ($match->score >= 40) {
                                                                $barBgMobile = 'background: linear-gradient(to top, #4338ca, #6366f1);';
                                                                $textColorMobile = 'text-indigo-500';
                                                            } else {
                                                                $barBgMobile = 'background: linear-gradient(to top, #b45309, #f59e0b);';
                                                                $textColorMobile = 'text-amber-500';
                                                            }
                                                        @endphp
                                                        <div class="flex flex-col items-center w-8 sm:w-10 justify-end group h-full relative">
                                                            <span class="absolute text-[7px] sm:text-[8px] font-black {{ $textColorMobile }} transform -translate-y-3 sm:-translate-y-4"
                                                                  style="bottom: {{ $match->score }}%;">{{ $match->score }}%</span>
                                                            <div @click="showBreakdown = '{{ $match->id }}'; $nextTick(() => { if (window.MathJax) { MathJax.typesetPromise(); } })"
                                                                 class="w-6 sm:w-8 cursor-pointer rounded-t-md transition-all shadow-sm active:scale-95"
                                                                 style="height: {{ max(5, $match->score) }}%; {{ $barBgMobile }}"
                                                                 title="Tap for breakdown"></div>
                                                        </div>
                                                    @endforeach
                                                </div>

                                                @foreach($top5Matches as $match)
                                                    @php
                                                        if ($match->score >= 80) {
                                                            $barBgMobile = 'background: linear-gradient(to top, #047857, #10b981);';
                                                            $textColorMobile = 'text-emerald-500';
                                                        } elseif ($match->score >= 60) {
                                                            $barBgMobile = 'background: linear-gradient(to top, #084577, #3b82f6);';
                                                            $textColorMobile = 'text-[#0B5694] dark:text-blue-400';
                                                        } elseif ($match->score >= 40) {
                                                            $barBgMobile = 'background: linear-gradient(to top, #4338ca, #6366f1);';
                                                            $textColorMobile = 'text-indigo-500';
                                                        } else {
                                                            $barBgMobile = 'background: linear-gradient(to top, #b45309, #f59e0b);';
                                                            $textColorMobile = 'text-amber-500';
                                                        }
                                                    @endphp
                                                    <div x-show="activeMatchId === '{{ $match->id }}'" x-transition.opacity.duration.300ms
                                                         class="absolute inset-0 flex items-end justify-center z-10 pb-0.5">
                                                        <div class="flex flex-col items-center w-12 sm:w-16 justify-end group h-full relative">
                                                            <span class="absolute text-[9px] sm:text-[10px] font-black {{ $textColorMobile }} transform -translate-y-4 sm:-translate-y-5"
                                                                  style="bottom: {{ $match->score }}%;">{{ $match->score }}%</span>
                                                            <div @click="showBreakdown = '{{ $match->id }}'; $nextTick(() => { if (window.MathJax) { MathJax.typesetPromise(); } })"
                                                                 class="w-10 sm:w-12 cursor-pointer rounded-t-lg transition-all shadow-md active:scale-95"
                                                                 style="height: {{ max(5, $match->score) }}%; {{ $barBgMobile }}"
                                                                 title="Tap for breakdown"></div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>

                                            <div class="mt-3 sm:mt-4 border-t border-gray-200 dark:border-gray-800 pt-2 sm:pt-3 text-center">
                                                <div x-show="activeMatchId === 'all'" 
                                                     x-transition.opacity.duration.200ms
                                                     class="flex justify-around text-[7px] sm:text-[8px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                    @foreach($top5Matches as $match)
                                                        <span class="truncate max-w-[45px] sm:max-w-[60px]" title="{{ $match->office->name }}">{{ $match->office->name }}</span>
                                                    @endforeach
                                                </div>
                                                @foreach($top5Matches as $match)
                                                    <div x-show="activeMatchId === '{{ $match->id }}'" 
                                                         x-transition.opacity.duration.200ms
                                                         class="w-full text-center">
                                                        <span class="text-[9px] sm:text-[10px] font-black text-gray-700 dark:text-gray-200 uppercase tracking-widest truncate block" title="{{ $match->office->name }}">
                                                            {{ $match->office->name }}
                                                        </span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @else
                                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider text-center py-4">No matching configurations found.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if ($applicants->hasPages())
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 sm:gap-6 mt-8 sm:mt-10 pt-6 sm:pt-8 border-t border-gray-100 dark:border-gray-800 transition-all duration-300 ease-out">
                        <p class="text-[10px] sm:text-xs font-bold text-gray-400 uppercase tracking-wider text-center sm:text-left">Showing {{ $applicants->firstItem() }} to {{ $applicants->lastItem() }} of {{ $applicants->total() }} applicants</p>
                        <div class="w-full sm:w-auto overflow-x-auto">
                            {{ $applicants->links() }}
                        </div>
                    </div>
                @endif
            </div>
        @else
            <div class="bg-white dark:bg-gray-900 rounded-[25px] sm:rounded-[35px] border border-gray-100 dark:border-gray-800 p-12 sm:p-24 text-center">
                <div class="w-24 h-24 bg-gray-50 dark:bg-gray-950 rounded-[35px] flex items-center justify-center mx-auto mb-8 text-gray-300 border border-gray-100 dark:border-gray-800">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><line x1="10" y1="9" x2="8" y2="9"/></svg>
                </div>
                <h4 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight uppercase">No Data Found</h4>
                <p class="text-sm font-bold text-gray-400 uppercase tracking-wider mt-3">New applications will appear here automatically.</p>
            </div>
        @endif

    {{-- RESET PENDING MODAL --}}
    <div x-show="showResetModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[300] flex items-center justify-center p-4 sm:p-6 bg-gray-900/60 dark:bg-black/80 backdrop-blur-sm"
         @keydown.escape.window="showResetModal = false"
         @click.self="showResetModal = false"
         x-cloak>
        <div class="w-full max-w-md bg-white dark:bg-gray-900 rounded-[24px] sm:rounded-[32px] border border-gray-100 dark:border-gray-800 shadow-2xl overflow-hidden">
            <div class="px-6 sm:px-8 py-5 sm:py-6 text-center">
                <div class="w-14 h-14 sm:w-16 sm:h-16 bg-red-50 dark:bg-red-950/30 rounded-full flex items-center justify-center mx-auto mb-4 sm:mb-5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 sm:w-8 sm:h-8 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                </div>
                <h3 class="text-sm sm:text-base font-black text-gray-900 dark:text-white uppercase tracking-tight">Reset Pending Results?</h3>
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mt-2 leading-relaxed">This will clear all pending match results. Finalized assignments will be saved and unaffected.</p>
            </div>
            <div class="px-6 sm:px-8 py-4 sm:py-5 border-t border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-950/50 flex flex-col sm:flex-row-reverse gap-2 sm:gap-3">
                <form action="{{ route('head-supervisor.matching.clear') }}" method="POST" class="w-full sm:w-auto">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full sm:w-auto px-6 py-3 sm:py-3.5 bg-red-500 hover:bg-red-600 text-white font-bold uppercase text-[10px] sm:text-xs tracking-wider rounded-xl transition-all active:scale-95 shadow-md min-h-[44px]">
                        Yes, Reset Pending
                    </button>
                </form>
                <button @click="showResetModal = false" type="button" class="w-full sm:w-auto px-6 py-3 sm:py-3.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 font-bold uppercase text-[10px] sm:text-xs tracking-wider rounded-xl transition-all active:scale-95 shadow-sm min-h-[44px]">
                    Cancel
                </button>
            </div>
        </div>
    </div>

    {{-- RESUME PREVIEW MODAL --}}
    <div x-show="showResumePreview" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[100] flex items-center justify-center p-3 sm:p-4 lg:p-6 bg-black/60 backdrop-blur-sm"
         x-cloak>
        <div @click.away="showResumePreview = false" 
             class="bg-white dark:bg-gray-900 w-full max-w-[calc(100%-0.5rem)] sm:max-w-3xl lg:max-w-5xl h-[90vh] rounded-[24px] sm:rounded-[35px] shadow-2xl flex flex-col overflow-hidden animate-in zoom-in-95 duration-300">
            <div class="px-5 sm:px-8 py-4 sm:py-6 border-b border-gray-100 dark:border-gray-800 flex justify-between items-center bg-gray-50/50 dark:bg-gray-950/50 gap-3">
                <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                    <div class="p-2 sm:p-3 bg-blue-50 dark:bg-blue-900/30 rounded-xl sm:rounded-2xl text-blue-600 dark:text-blue-400 shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" class="sm:w-5 sm:h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/></svg>
                    </div>
                    <div class="min-w-0">
                        <h4 class="text-xs sm:text-sm font-black uppercase tracking-wider sm:tracking-widest text-gray-800 dark:text-white truncate">Resume Preview</h4>
                        <p class="text-[9px] sm:text-[10px] font-bold text-gray-400 uppercase tracking-wider mt-0.5">Secure Document Viewer</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                    <a :href="resumeUrl" download class="p-2.5 sm:p-3 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl sm:rounded-2xl text-gray-400 hover:text-[#0B5694] dark:hover:text-blue-400 transition shadow-sm active:scale-90" title="Download">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" class="sm:w-5 sm:h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                    </a>
                    <button @click="showResumePreview = false" class="p-2.5 sm:p-3 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl sm:rounded-2xl text-gray-400 hover:text-red-500 transition shadow-sm active:scale-90">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" class="sm:w-5 sm:h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>
                </div>
            </div>
            <div class="flex-1 bg-gray-100 dark:bg-gray-950 relative">
                <template x-if="resumeUrl">
                    <iframe :src="resumeUrl" class="w-full h-full border-none shadow-inner" loading="lazy"></iframe>
                </template>
                <div x-show="!resumeUrl" class="absolute inset-0 flex items-center justify-center">
                    <div class="animate-spin rounded-full h-12 w-12 border-4 border-[#0B5694] border-t-transparent"></div>
                </div>
            </div>
        </div>
    </div>
</div>
</x-page-layout>

<style>
@media (prefers-reduced-motion: reduce) {
    *, *::before, *::after {
        transition-duration: 0.01ms !important;
        animation-duration: 0.01ms !important;
    }
}
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
    height: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 99px;
}
.dark .custom-scrollbar::-webkit-scrollbar-thumb {
    background: #4b5563;
}
.markdown-content h3 {
    font-size: 13px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #0B5694;
    margin-top: 1.5rem;
    margin-bottom: 0.75rem;
    border-bottom: 1px solid rgba(229, 231, 235, 0.5);
    padding-bottom: 0.25rem;
}
.dark .markdown-content h3 {
    color: #60a5fa;
    border-color: rgba(75, 85, 99, 0.5);
}
.markdown-content p {
    margin-bottom: 0.75rem;
    line-height: 1.6;
    font-weight: 600;
}
.markdown-content ul {
    list-style-type: disc;
    padding-left: 1.5rem;
    margin-bottom: 1rem;
}
.markdown-content li {
    margin-bottom: 0.35rem;
    line-height: 1.5;
}
.markdown-content strong {
    font-weight: 800;
    color: #1f2937;
}
.dark .markdown-content strong {
    color: #f3f4f6;
}
.markdown-content code {
    background-color: #f3f4f6;
    color: #ef4444;
    padding: 0.125rem 0.25rem;
    border-radius: 0.25rem;
    font-family: monospace;
    font-size: 0.9em;
}
.dark .markdown-content code {
    background-color: #1f2937;
    color: #f87171;
}
</style>

{{-- MathJax Support for rendering formulas --}}
<script>
    window.MathJax = {
        startup: {
            typeset: false
        },
        tex: {
            inlineMath: [['\\(', '\\)']],
            displayMath: [['$$', '$$'], ['\\[', '\\]']]
        }
    };
</script>
<script id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>
@endsection
