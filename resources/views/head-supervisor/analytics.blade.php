@extends('layouts.app')

@section('title', 'Analytics & Reports')

@section('content')
<x-page-layout 
    portalType="Head Supervisor" 
    userName="{{ Auth::user()->supervisorDetails->first_name }}" 
    userRole="{{ Auth::user()->supervisorDetails->office->name }}"
>
    <div x-data="analyticsManager(
              @js($months), 
              @js($applicantsTrends), 
              @js($completionTrends), 
              @js($completionRateTrends), 
              @js($schoolLabels), 
              @js($schoolTotals),
              '{{ request('search') || request('page') || request('filter_type') ? 'reports' : 'analytics' }}'
          )" class="flex flex-col flex-1">
        
        {{-- Header --}}
        <x-page-header 
            title="Analytics" 
            highlight="& Reports"
            class="mb-12"
        >
            <x-slot:subtitle>
                <span x-text="activeTab === 'analytics' ? 'Comprehensive insights into internship programs' : 'Review student journals and total rendered hours'"></span>
            </x-slot:subtitle>
 
            <x-slot:actions>
                {{-- TAB NAVIGATION --}}
                <div class="flex p-1.5 bg-gray-100 dark:bg-gray-900 rounded-[22px] border border-gray-200/50 dark:border-gray-800 shrink-0 shadow-inner mr-2">
                    <button @click="switchTab('analytics')" 
                            :class="activeTab === 'analytics' ? 'bg-white dark:bg-gray-800 text-[#0B5694] dark:text-blue-400 shadow-sm' : 'text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300'"
                            class="px-6 py-2.5 rounded-[18px] text-[11px] font-bold uppercase tracking-wider transition-all duration-300">
                        Analytics
                    </button>
                    <button @click="switchTab('reports')" 
                            :class="activeTab === 'reports' ? 'bg-white dark:bg-gray-800 text-[#0B5694] dark:text-blue-400 shadow-sm' : 'text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300'"
                            class="px-6 py-2.5 rounded-[18px] text-[11px] font-bold uppercase tracking-wider transition-all duration-300">
                        Reports
                    </button>
                </div>
 
                {{-- ANALYTICS ACTIONS --}}
                <div x-show="activeTab === 'analytics'" x-cloak class="flex items-center gap-3">
                    <form id="pdfForm" action="{{ route('head-supervisor.analytics.pdf') }}" method="POST" class="flex-1 sm:flex-none">
                        @csrf
                        <input type="hidden" name="completion_chart_image" id="completionChartInput">
                        <input type="hidden" name="school_chart_image" id="schoolChartInput">
                        <button type="button" @click="exportThemedPDF()" class="w-full flex items-center justify-center gap-3 bg-white dark:bg-gray-900 px-8 py-3.5 border border-gray-100 dark:border-gray-800 rounded-xl text-[10px] font-bold text-gray-500 dark:text-gray-400 shadow-sm hover:shadow-md transition uppercase tracking-wider active:scale-95">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg> 
                            PDF Report
                        </button>
                    </form>
                    <a href="{{ route('head-supervisor.analytics.excel', request()->query()) }}" class="flex-1 sm:flex-none flex items-center justify-center gap-3 bg-emerald-50 dark:bg-emerald-950/30 px-8 py-3.5 border border-emerald-100 dark:border-emerald-900/50 rounded-xl text-[10px] font-bold text-emerald-600 dark:text-emerald-400 shadow-sm hover:shadow-md transition uppercase tracking-wider active:scale-95">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg> 
                        Excel Data
                    </a>
                </div>
 
                {{-- DETAILED REPORTS ACTIONS --}}
                <div x-show="activeTab === 'reports'" x-cloak class="flex items-center gap-3">
                    <a href="{{ route('head-supervisor.report.export', request()->query()) }}" class="flex items-center justify-center gap-3 bg-emerald-50 dark:bg-emerald-950/30 px-8 py-3.5 border border-emerald-100 dark:border-emerald-900/50 rounded-xl text-[10px] font-bold text-emerald-600 dark:text-emerald-400 shadow-sm hover:shadow-md transition-all uppercase tracking-wider active:scale-95">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                        Export Registry
                    </a>
                </div>
            </x-slot:actions>
        </x-page-header>
 
        {{-- Analytics Content Tab --}}
        <div x-show="activeTab === 'analytics'" class="space-y-8 animate-in fade-in duration-300">
            {{-- Summary Stats --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8 sm:mb-10">
                {{-- Skeletons --}}
                <template x-if="loading">
                    <div class="contents">
                        @for($i = 0; $i < 4; $i++)
                            <div class="p-8 rounded-[35px] bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 space-y-4">
                                <x-skeleton class="h-3 w-24 rounded-lg" />
                                <x-skeleton class="h-10 w-20 rounded-lg" />
                                <x-skeleton class="h-3 w-32 rounded-lg" />
                            </div>
                        @endfor
                    </div>
                </template>

                <template x-if="!loading">
                    <div class="contents">
                        <x-stat-card 
                            title="Total Applicants" 
                            :value="number_format($totalApplicants)" 
                            subtitle="+18% from last period"
                            padding="p-8"
                            class="rounded-[35px]"
                        />
                        <x-stat-card 
                            title="Active Interns" 
                            :value="number_format($activeInterns)" 
                            subtitle="Currently Deployed"
                            color="blue"
                            padding="p-8"
                            class="rounded-[35px]"
                        />
                        <x-stat-card 
                            title="Completion Rate" 
                            :value="round($completionRate) . '%'" 
                            subtitle="Success Rate"
                            padding="p-8"
                            class="rounded-[35px]"
                        >
                            <x-slot name="value">
                                <span class="text-emerald-500">{{ round($completionRate) }}%</span>
                            </x-slot>
                        </x-stat-card>
                        <x-stat-card 
                            title="Partner Schools" 
                            :value="$partnerCount" 
                            subtitle="Connected Entities"
                            padding="p-8"
                            class="rounded-[35px]"
                        />
                    </div>
                </template>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-10">
                {{-- Trends Chart --}}
                <x-card-container 
                    class="lg:col-span-7"
                    title="Fulfillment Trends"
                    subtitle="Monthly completion vs ongoing status"
                    padding="p-8"
                >
                    <x-slot:headerAction>
                        <div class="flex items-center bg-gray-100 dark:bg-gray-800 rounded-xl p-0.5 border border-gray-200/30 dark:border-gray-700/50 shadow-inner">
                            <button @click="setTrendFilter('rate')" 
                                    :class="trendFilter === 'rate' ? 'bg-white dark:bg-gray-750 text-[#0B5694] dark:text-blue-400 shadow-sm' : 'text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300'"
                                    class="px-2.5 py-1 rounded-lg text-[9px] font-extrabold uppercase tracking-wider transition-all duration-200">
                                Rate
                            </button>
                            <button @click="setTrendFilter('applicants')" 
                                    :class="trendFilter === 'applicants' ? 'bg-white dark:bg-gray-750 text-[#0B5694] dark:text-blue-400 shadow-sm' : 'text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300'"
                                    class="px-2.5 py-1 rounded-lg text-[9px] font-extrabold uppercase tracking-wider transition-all duration-200">
                                Applicants
                            </button>
                            <button @click="setTrendFilter('completions')" 
                                    :class="trendFilter === 'completions' ? 'bg-white dark:bg-gray-750 text-[#0B5694] dark:text-blue-400 shadow-sm' : 'text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300'"
                                    class="px-2.5 py-1 rounded-lg text-[9px] font-extrabold uppercase tracking-wider transition-all duration-200">
                                Completions
                            </button>
                        </div>
                    </x-slot:headerAction>

                    <div x-show="loading" class="h-[350px] flex items-center justify-center bg-gray-50/50 dark:bg-gray-950/50 rounded-3xl">
                        <x-skeleton class="h-full w-full rounded-3xl" />
                    </div>
                    <div x-show="!loading" x-transition.opacity.duration.500ms class="h-[350px]">
                        <canvas id="completionChart"></canvas>
                    </div>
                </x-card-container>

                {{-- School Distribution --}}
                <x-card-container 
                    class="lg:col-span-5"
                    title="Institutional Reach"
                    subtitle="Interns per university partner"
                    padding="p-8"
                >
                    <x-slot:headerAction>
                        <div class="flex items-center bg-gray-100 dark:bg-gray-800 rounded-xl p-0.5 border border-gray-200/30 dark:border-gray-700/50 shadow-inner">
                            <button @click="setSchoolFilter('3')" 
                                    :class="schoolFilter === '3' ? 'bg-white dark:bg-gray-750 text-[#0B5694] dark:text-blue-400 shadow-sm' : 'text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300'"
                                    class="px-2.5 py-1 rounded-lg text-[9px] font-extrabold uppercase tracking-wider transition-all duration-200">
                                Top 3
                            </button>
                            <button @click="setSchoolFilter('5')" 
                                    :class="schoolFilter === '5' ? 'bg-white dark:bg-gray-750 text-[#0B5694] dark:text-blue-400 shadow-sm' : 'text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300'"
                                    class="px-2.5 py-1 rounded-lg text-[9px] font-extrabold uppercase tracking-wider transition-all duration-200">
                                Top 5
                            </button>
                            <button @click="setSchoolFilter('10')" 
                                    :class="schoolFilter === '10' ? 'bg-white dark:bg-gray-750 text-[#0B5694] dark:text-blue-400 shadow-sm' : 'text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300'"
                                    class="px-2.5 py-1 rounded-lg text-[9px] font-extrabold uppercase tracking-wider transition-all duration-200">
                                Top 10
                            </button>
                            <button @click="setSchoolFilter('all')" 
                                    :class="schoolFilter === 'all' ? 'bg-white dark:bg-gray-750 text-[#0B5694] dark:text-blue-400 shadow-sm' : 'text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300'"
                                    class="px-2.5 py-1 rounded-lg text-[9px] font-extrabold uppercase tracking-wider transition-all duration-200">
                                All
                            </button>
                        </div>
                    </x-slot:headerAction>
                    <div x-show="loading" class="h-[350px] flex items-center justify-center bg-gray-50/50 dark:bg-gray-950/50 rounded-3xl">
                        <x-skeleton class="h-64 w-64 rounded-full" />
                    </div>
                    <div x-show="!loading" x-transition.opacity.duration.500ms class="h-[350px]">
                        <canvas id="schoolChart"></canvas>
                    </div>
                </x-card-container>
            </div>

            {{-- Department Distribution --}}
            <x-card-container 
                title="Department Allocation"
                subtitle="Workload distribution across all division units"
                padding="p-10"
            >
                {{-- Skeletons --}}
                <div x-show="loading" class="{{ count($departments) > 3 ? 'flex overflow-x-auto pb-4 gap-6 custom-scrollbar' : 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6' }}">
                    @for($i = 0; $i < 6; $i++)
                        <div class="{{ count($departments) > 3 ? 'min-w-[280px] sm:min-w-[320px] shrink-0' : '' }} p-5 rounded-[24px] bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 flex items-center justify-between gap-4">
                            <div class="space-y-2.5 flex-1 min-w-0">
                                <x-skeleton class="h-4 w-28 rounded-lg" />
                                <x-skeleton class="h-3 w-20 rounded-lg" />
                            </div>
                            <x-skeleton class="h-8 w-8 rounded-xl shrink-0" />
                        </div>
                    @endfor
                </div>

                <div x-show="!loading" x-transition.opacity.duration.500ms class="{{ count($departments) > 3 ? 'flex overflow-x-auto pb-4 gap-6 custom-scrollbar' : 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6' }}">
                    @foreach($departments as $dept)
                    <div class="{{ count($departments) > 3 ? 'min-w-[280px] sm:min-w-[320px] shrink-0' : '' }} group flex items-center justify-between p-5 rounded-[24px] bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 transition-all hover:bg-gray-50 dark:hover:bg-gray-800/50 hover:shadow-sm">
                        <div class="min-w-0">
                            <p class="text-[13px] font-extrabold text-gray-800 dark:text-gray-100 uppercase truncate tracking-normal group-hover:text-[#0B5694] dark:group-hover:text-blue-400 transition-colors">
                                {{ $dept->name }}
                            </p>
                            <p class="text-[10px] text-gray-400 dark:text-slate-500 font-bold uppercase tracking-wider mt-1">
                                Assigned Students
                            </p>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-normal leading-none group-hover:text-[#0B5694] dark:group-hover:text-blue-400 transition-colors">
                                {{ $dept->intern_details_count }}
                            </p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </x-card-container>
        </div>

        {{-- Reports Content Tab --}}
        <div x-show="activeTab === 'reports'" x-cloak class="space-y-8 animate-in fade-in duration-300">
            <div class="w-full bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-[25px] sm:rounded-[35px] p-4 sm:p-6 shadow-sm mb-6">
                <form action="{{ route('head-supervisor.analytics') }}" method="GET" class="grid grid-cols-2 lg:grid-cols-12 gap-3 sm:gap-4 items-end">
                    {{-- Search Name --}}
                    <div class="col-span-2 lg:col-span-3 relative group">
                        <label class="block text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-2">Search Intern</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 group-focus-within:text-[#0B5694] transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                            </span>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name..." 
                                class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl pl-12 pr-6 py-3.5 text-sm font-medium focus:ring-2 focus:ring-[#0B5694] outline-none transition-all tracking-tight">
                        </div>
                    </div>

                    {{-- Discretionary Filter Type --}}
                    <div class="lg:col-span-3">
                        <label class="block text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-2">Discretionary Filter</label>
                        <select name="filter_type" 
                                class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl px-4 py-3.5 text-sm font-medium focus:ring-2 focus:ring-[#0B5694] outline-none transition-all tracking-tight text-gray-700 dark:text-gray-300">
                            <option value="all" {{ request('filter_type') == 'all' ? 'selected' : '' }}>All Time</option>
                            <option value="annual" {{ request('filter_type') == 'annual' ? 'selected' : '' }}>Annually</option>
                            <option value="monthly" {{ request('filter_type') == 'monthly' ? 'selected' : '' }}>Specified Month</option>
                        </select>
                    </div>

                    {{-- Month Selection --}}
                    <div class="lg:col-span-2">
                        <label class="block text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-2">Month</label>
                        <select name="month" 
                                class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl px-4 py-3.5 text-sm font-medium focus:ring-2 focus:ring-[#0B5694] outline-none transition-all tracking-tight text-gray-700 dark:text-gray-300">
                            <option value="">Select Month</option>
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Year Selection --}}
                    <div class="lg:col-span-2">
                        <label class="block text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-2">Year</label>
                        <select name="year" 
                                class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl px-4 py-3.5 text-sm font-medium focus:ring-2 focus:ring-[#0B5694] outline-none transition-all tracking-tight text-gray-700 dark:text-gray-300">
                            @foreach(range(now()->year, now()->year - 4) as $y)
                                <option value="{{ $y }}" {{ request('year', now()->year) == $y ? 'selected' : '' }}>
                                    {{ $y }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter Action Button --}}
                    <div class="col-span-2 sm:col-span-1 lg:col-span-2">
                        <button type="submit" class="w-full bg-[#0B5694] dark:bg-blue-600 hover:bg-[#084577] dark:hover:bg-blue-700 text-white font-bold rounded-xl py-3.5 text-sm transition-all shadow-md active:scale-95 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z"/></svg>
                        </button>
                    </div>
                </form>
            </div>

            {{-- Reports Table Container --}}
            <x-table-container 
                title="Activity Logs" 
                subtitle="Review student journals and total rendered hours"
            >
                <div class="border border-gray-100 dark:border-gray-800 rounded-3xl overflow-hidden shadow-inner bg-white dark:bg-gray-900/50">
                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left">
                            <thead class="bg-gray-50 dark:bg-gray-800/50 text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider border-b border-gray-100 dark:border-gray-800">
                                <tr>
                                    <th class="px-8 py-5">Intern Information</th>
                                    <th class="px-8 py-5">Placement</th>
                                    <th class="px-8 py-5">Latest Update</th>
                                    <th class="px-8 py-5 text-center">Rendering</th>
                                    <th class="px-8 py-5">Fulfillment</th>
                                    <th class="px-8 py-5 text-right">Registry</th>
                                </tr>
                            </thead>
                            {{-- Skeleton Rows --}}
                            <tbody x-show="loading" class="divide-y divide-gray-50 dark:divide-gray-800">
                                @for($i = 0; $i < min(count($reports) ?: 5, 5); $i++)
                                    <tr>
                                        <td class="px-8 py-6">
                                            <div class="flex items-center gap-4">
                                                <x-skeleton class="w-10 h-10 rounded-full shrink-0" />
                                                <div class="space-y-2">
                                                    <x-skeleton class="h-4 w-32 rounded-lg" />
                                                    <x-skeleton class="h-3 w-24 rounded-lg" />
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-8 py-6"><x-skeleton class="h-6 w-24 rounded-lg" /></td>
                                        <td class="px-8 py-6">
                                            <div class="space-y-2">
                                                <x-skeleton class="h-3 w-20 rounded-lg" />
                                                <x-skeleton class="h-2 w-16 rounded-lg" />
                                            </div>
                                        </td>
                                        <td class="px-8 py-6 text-center"><x-skeleton class="h-4 w-20 mx-auto rounded-lg" /></td>
                                        <td class="px-8 py-6"><x-skeleton class="h-5 w-16 rounded-full" /></td>
                                        <td class="px-8 py-6 text-right"><x-skeleton class="h-10 w-10 ml-auto rounded-xl" /></td>
                                    </tr>
                                @endfor
                            </tbody>

                            <tbody x-show="!loading" x-transition.opacity.duration.500ms class="text-sm divide-y divide-gray-50 dark:divide-gray-800">
                                @forelse($reports as $intern)
                                    @php 
                                        $latestJournal = $intern->journals->first();
                                        $isCompleted = optional($intern->certificate)->status === 'published';
                                    @endphp
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors group">
                                        <td class="px-8 py-6 whitespace-nowrap">
                                            <div class="flex items-center gap-4">
                                                <x-avatar :user="$intern" size="10" border="1" circular="true" textSize="xs" />
                                                <div>
                                                    <p class="text-sm font-black text-gray-800 dark:text-gray-100 uppercase tracking-tight group-hover:text-[#0B5694] transition-colors">{{ $intern->first_name }} {{ $intern->last_name }}</p>
                                                    <p class="text-[11px] text-gray-400 dark:text-gray-500 font-bold uppercase tracking-wider mt-0.5 leading-none">{{ $intern->user->email ?? 'No email' }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-8 py-6 whitespace-nowrap">
                                            <span class="font-bold text-gray-600 dark:text-gray-400 uppercase tracking-wider text-[11px] bg-gray-50 dark:bg-gray-950 border border-gray-100 dark:border-gray-800 px-3 py-1.5 rounded-lg">
                                                {{ $intern->office->name ?? 'Unassigned' }}
                                            </span>
                                        </td>
                                        <td class="px-8 py-6 whitespace-nowrap">
                                            @if($latestJournal)
                                                <p class="text-xs font-bold text-gray-700 dark:text-gray-300">{{ \Carbon\Carbon::parse($latestJournal->entry_date)->format('M d, Y') }}</p>
                                                <p class="text-[10px] text-gray-400 uppercase tracking-wider mt-0.5 leading-none">{{ \Carbon\Carbon::parse($latestJournal->created_at)->diffForHumans() }}</p>
                                            @else
                                                <span class="text-xs text-gray-400 italic">No Activity</span>
                                            @endif
                                        </td>
                                        <td class="px-8 py-6 text-center whitespace-nowrap">
                                            <span class="font-bold text-gray-800 dark:text-gray-200 text-xs tracking-wider uppercase">{{ (int)$intern->hours_rendered }} / 480 HRS</span>
                                        </td>
                                        <td class="px-8 py-6 whitespace-nowrap">
                                            @if($isCompleted)
                                                <span class="bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 text-[9px] font-bold px-4 py-1.5 rounded-full uppercase tracking-wider border border-emerald-100 dark:border-emerald-900/30">completed</span>
                                            @else
                                                <span class="bg-gray-50 dark:bg-gray-950 text-gray-400 dark:text-gray-500 text-[9px] font-bold px-4 py-1.5 rounded-full uppercase tracking-wider border border-gray-100 dark:border-gray-800">pending</span>
                                            @endif
                                        </td>
                                        <td class="px-8 py-6 text-right whitespace-nowrap">
                                            <a href="{{ route('head-supervisor.interns.show', $intern->user) }}" class="p-2.5 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 text-gray-400 hover:text-[#0B5694] dark:hover:text-blue-400 transition-all shadow-sm rounded-xl transform hover:scale-110 active:scale-95 inline-flex items-center justify-center">
                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-20 text-center">
                                            <x-empty-state title="No internship reports found" compact="true" />
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($reports->hasPages())
                    <div class="mt-8 flex justify-end">
                        {{ $reports->appends(request()->query())->links('vendor.pagination.tailwind-custom') }}
                    </div>
                @endif
            </x-table-container>
        </div>

    </div>
</x-page-layout>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush
@endsection
