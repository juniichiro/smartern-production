@extends('layouts.app')

@section('title', 'Security Monitor')

@section('content')
<x-page-layout 
    portalType="Admin" 
    userName="{{ Auth::user()->email }}" 
    userRole="Admin"
>
    @php
        $crit = (float) ($crit ?? config('security.thresholds.critical', 0.70));
        $warn = (float) ($warn ?? config('security.thresholds.warning', 0.50));
        $totalCount = $totalCount ?? ($normalCount + $warningCount + $criticalCount);
        $currentStatus = request('status');
        $currentType = request('type');
        $currentDate = request('date');
        $currentSort = request('sort', 'severity_desc');
        $currentSearch = request('search');
    @endphp

    <div x-data="Object.assign(securityMonitorManager(@js($recentUserLogs)), {
        showDetail: false,
        detailLog: null,
        detailUser: null,
        detailDisplayName: '',
        detailUserRouteKey: '',
        canEvaluate: false,
        lastFocusedEl: null,

        openReview(log, user, displayName, userRouteKey, canEval = false) {
            this.lastFocusedEl = document.activeElement;
            this.detailLog = log;
            this.detailUser = user;
            this.detailDisplayName = displayName;
            this.detailUserRouteKey = userRouteKey;
            this.canEvaluate = canEval;
            this.selectedAction = 'safe';
            this.evaluatingUser = user;
            if (this.evaluatingUser) {
                this.evaluatingUser.route_key = userRouteKey;
            }
            this.evaluatingLog = log;
            this.evaluatingDisplayName = displayName;
            this.showDetail = true;
            document.body.style.overflow = 'hidden';
        },

        closeReview() {
            this.showDetail = false;
            document.body.style.overflow = '';
            if (this.lastFocusedEl) {
                this.$nextTick(() => {
                    this.lastFocusedEl.focus();
                });
            }
        }
    })" 
    @keydown.window.escape="if(showDetail) closeReview()"
    class="flex flex-col flex-1 max-w-7xl mx-auto w-full">

        {{-- PAGE HEADER --}}
        <header class="mb-6 lg:mb-8 flex flex-col gap-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    {{-- Breadcrumb --}}
                    <nav class="flex items-center gap-1.5 text-xs font-medium text-slate-400 dark:text-gray-400 mb-1.5" aria-label="Breadcrumb">
                        <a href="{{ route('admin.dashboard') }}" class="hover:text-[#0B5694] dark:hover:text-blue-400 transition-colors flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                            <span>Dashboard</span>
                        </a>
                        <svg class="w-3 h-3 text-slate-300 dark:text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        <span class="text-slate-600 dark:text-gray-300 font-semibold">Security Monitor</span>
                    </nav>

                    {{-- Title & Subtitle --}}
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-tight">
                        Security Monitor
                    </h1>
                    <p class="text-xs sm:text-sm font-medium text-slate-500 dark:text-gray-400 mt-0.5">
                        Isolation Forest anomaly detection engine
                    </p>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center gap-3 self-start sm:self-center">
                    <a href="{{ route('admin.security.guide') }}" 
                       aria-label="Download Isolation Forest Algorithm Guide" 
                       class="h-10 sm:h-11 px-4 sm:px-5 rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-slate-700 dark:text-gray-200 hover:bg-slate-50 dark:hover:bg-gray-700/60 hover:border-slate-300 font-semibold text-xs sm:text-sm flex items-center gap-2 transition-all shadow-sm active:scale-95 cursor-pointer">
                        <svg class="w-4 h-4 text-slate-500 dark:text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                        <span>Algorithm Guide</span>
                    </a>

                    <a href="{{ route('admin.security.export', request()->query()) }}" 
                       class="h-10 sm:h-11 px-4 sm:px-5 rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-slate-700 dark:text-gray-200 hover:bg-slate-50 dark:hover:bg-gray-700/60 hover:border-slate-300 font-semibold text-xs sm:text-sm flex items-center gap-2 transition-all shadow-sm active:scale-95 cursor-pointer">
                        <svg class="w-4 h-4 text-slate-500 dark:text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        <span>Export Log</span>
                    </a>
                </div>
            </div>
        </header>

        {{-- SKELETON LOADING VIEW --}}
        <div x-show="loading" x-transition:leave="transition ease-in duration-300 pointer-events-none" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="flex flex-col flex-1">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                @for($i = 0; $i < 4; $i++)
                    <div class="bg-white dark:bg-gray-900 rounded-[22px] border border-slate-200/80 dark:border-gray-800 p-5 space-y-3">
                        <div class="h-4 w-20 bg-slate-200 dark:bg-gray-700 rounded animate-shimmer"></div>
                        <div class="h-8 w-16 bg-slate-200 dark:bg-gray-700 rounded animate-shimmer"></div>
                        <div class="h-3 w-28 bg-slate-200 dark:bg-gray-700 rounded animate-shimmer"></div>
                    </div>
                @endfor
            </div>
            <div class="bg-white dark:bg-gray-900 rounded-[22px] border border-slate-200/80 dark:border-gray-800 p-6 space-y-4">
                <div class="h-10 bg-slate-100 dark:bg-gray-800 rounded-xl animate-shimmer"></div>
                <div class="h-64 bg-slate-50 dark:bg-gray-800/40 rounded-xl animate-shimmer"></div>
            </div>
        </div>

        {{-- ACTUAL CONTENT VIEW --}}
        <div x-show="!loading" x-cloak class="flex flex-col flex-1">
            
            {{-- SEVERITY TILES & DISTRIBUTION BAR --}}
            <section aria-label="Severity Overview" class="bg-white dark:bg-gray-900 rounded-[22px] border border-slate-200/80 dark:border-gray-800 p-5 sm:p-6 mb-6 shadow-[0_1px_3px_rgba(0,0,0,0.02)]">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-sm sm:text-base font-bold text-slate-800 dark:text-gray-100 tracking-tight">Anomaly Distribution</h2>
                        <p class="text-xs text-slate-400 dark:text-gray-500">Live score classification based on trained Isolation Forest baseline</p>
                    </div>
                </div>

                {{-- 4-Tile Row: Critical leftmost -> Warnings -> Normal -> Total --}}
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-5">
                    {{-- 1. Critical Tile --}}
                    @php
                        $critUrl = route('admin.security', array_filter(array_merge(request()->except('page'), ['status' => 'critical'])));
                        $isCritActive = request('status') === 'critical';
                    @endphp
                    <a href="{{ $critUrl }}" 
                       class="group rounded-2xl p-4 border transition-all flex flex-col justify-between min-h-[115px] sm:min-h-[125px] {{ $isCritActive ? 'bg-rose-50/70 border-rose-300 dark:bg-rose-950/30 dark:border-rose-800 ring-2 ring-rose-500/20 shadow-sm' : 'bg-slate-50/70 dark:bg-gray-800/40 border-slate-150 dark:border-gray-800 hover:border-rose-200 dark:hover:border-rose-900/60' }} focus:outline-none focus:ring-2 focus:ring-rose-500/30">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-rose-100 dark:bg-rose-900/40 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                </div>
                                <span class="text-xs font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400 text-[11px]">Critical</span>
                            </div>
                            @if($isCritActive)
                                <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                            @endif
                        </div>
                        <div class="my-1">
                            <span class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ number_format($criticalCount) }}</span>
                        </div>
                        <div class="flex items-center gap-1.5 text-[11px] text-slate-500 dark:text-gray-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                            <span>Score > {{ number_format($crit, 2) }}</span>
                        </div>
                    </a>

                    {{-- 2. Warnings Tile --}}
                    @php
                        $warnUrl = route('admin.security', array_filter(array_merge(request()->except('page'), ['status' => 'warning'])));
                        $isWarnActive = request('status') === 'warning';
                    @endphp
                    <a href="{{ $warnUrl }}" 
                       class="group rounded-2xl p-4 border transition-all flex flex-col justify-between min-h-[115px] sm:min-h-[125px] {{ $isWarnActive ? 'bg-amber-50/70 border-amber-300 dark:bg-amber-950/30 dark:border-amber-800 ring-2 ring-amber-500/20 shadow-sm' : 'bg-slate-50/70 dark:bg-gray-800/40 border-slate-150 dark:border-gray-800 hover:border-amber-200 dark:hover:border-amber-900/60' }} focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                                </div>
                                <span class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400 text-[11px]">Warnings</span>
                            </div>
                            @if($isWarnActive)
                                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            @endif
                        </div>
                        <div class="my-1">
                            <span class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ number_format($warningCount) }}</span>
                        </div>
                        <div class="flex items-center gap-1.5 text-[11px] text-slate-500 dark:text-gray-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                            <span>Score {{ number_format($warn + 0.01, 2) }} – {{ number_format($crit, 2) }}</span>
                        </div>
                    </a>

                    {{-- 3. Normal Tile --}}
                    @php
                        $normUrl = route('admin.security', array_filter(array_merge(request()->except('page'), ['status' => 'normal'])));
                        $isNormActive = request('status') === 'normal';
                    @endphp
                    <a href="{{ $normUrl }}" 
                       class="group rounded-2xl p-4 border transition-all flex flex-col justify-between min-h-[115px] sm:min-h-[125px] {{ $isNormActive ? 'bg-emerald-50/70 border-emerald-300 dark:bg-emerald-950/30 dark:border-emerald-800 ring-2 ring-emerald-500/20 shadow-sm' : 'bg-slate-50/70 dark:bg-gray-800/40 border-slate-150 dark:border-gray-800 hover:border-emerald-200 dark:hover:border-emerald-900/60' }} focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                                </div>
                                <span class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 text-[11px]">Normal</span>
                            </div>
                            @if($isNormActive)
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            @endif
                        </div>
                        <div class="my-1">
                            <span class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ number_format($normalCount) }}</span>
                        </div>
                        <div class="flex items-center gap-1.5 text-[11px] text-slate-500 dark:text-gray-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            <span>Score ≤ {{ number_format($warn, 2) }}</span>
                        </div>
                    </a>

                    {{-- 4. Total Tile (Clears status filter) --}}
                    @php
                        $totalUrl = route('admin.security', array_filter(request()->except(['page', 'status'])));
                        $isTotalActive = empty(request('status'));
                    @endphp
                    <a href="{{ $totalUrl }}" 
                       class="group rounded-2xl p-4 border transition-all flex flex-col justify-between min-h-[115px] sm:min-h-[125px] {{ $isTotalActive ? 'bg-slate-100/90 border-slate-300 dark:bg-gray-800 dark:border-gray-700 ring-2 ring-slate-400/20 shadow-sm' : 'bg-slate-50/70 dark:bg-gray-800/40 border-slate-150 dark:border-gray-800 hover:border-slate-300 dark:hover:border-gray-700' }} focus:outline-none focus:ring-2 focus:ring-[#0B5694]/30">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-slate-200 dark:bg-gray-700 text-slate-700 dark:text-gray-300 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                                </div>
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-gray-400 text-[11px]">Total Events</span>
                            </div>
                            @if($isTotalActive)
                                <span class="w-2 h-2 rounded-full bg-slate-500"></span>
                            @endif
                        </div>
                        <div class="my-1">
                            <span class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ number_format($totalCount) }}</span>
                        </div>
                        <div class="flex items-center gap-1.5 text-[11px] text-slate-500 dark:text-gray-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span>
                            <span>All recorded activity</span>
                        </div>
                    </a>
                </div>

                {{-- Slim Stacked Distribution Bar --}}
                @php
                    $safeTotal = max(1, $totalCount);
                    $critPct = round(($criticalCount / $safeTotal) * 100, 1);
                    $warnPct = round(($warningCount / $safeTotal) * 100, 1);
                    $normPct = round(($normalCount / $safeTotal) * 100, 1);
                @endphp
                <div class="pt-2">
                    <div class="w-full h-2.5 bg-slate-100 dark:bg-gray-800 rounded-full overflow-hidden flex shadow-inner" 
                         role="progressbar" 
                         aria-valuenow="{{ $totalCount }}" 
                         aria-valuemin="0" 
                         aria-valuemax="{{ $safeTotal }}" 
                         aria-label="Anomaly distribution ratio">
                        @if($totalCount > 0)
                            <div class="h-full bg-rose-500 transition-all duration-500" style="width: {{ $critPct }}%" title="Critical: {{ $criticalCount }} ({{ $critPct }}%)"></div>
                            <div class="h-full bg-amber-500 transition-all duration-500" style="width: {{ $warnPct }}%" title="Warning: {{ $warningCount }} ({{ $warnPct }}%)"></div>
                            <div class="h-full bg-emerald-500 transition-all duration-500" style="width: {{ $normPct }}%" title="Normal: {{ $normalCount }} ({{ $normPct }}%)"></div>
                        @else
                            <div class="h-full bg-slate-200 dark:bg-gray-700 w-full" title="No events recorded"></div>
                        @endif
                    </div>

                    {{-- Distribution Legend --}}
                    <div class="mt-2.5 flex flex-wrap items-center justify-between gap-3 text-[11px] text-slate-500 dark:text-gray-400">
                        <div class="flex flex-wrap items-center gap-4">
                            <span class="inline-flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                <strong class="text-slate-700 dark:text-gray-300">Critical (> {{ number_format($crit, 2) }}):</strong> {{ number_format($criticalCount) }} ({{ $critPct }}%)
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                <strong class="text-slate-700 dark:text-gray-300">Warning ({{ number_format($warn + 0.01, 2) }}–{{ number_format($crit, 2) }}):</strong> {{ number_format($warningCount) }} ({{ $warnPct }}%)
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                <strong class="text-slate-700 dark:text-gray-300">Normal (≤ {{ number_format($warn, 2) }}):</strong> {{ number_format($normalCount) }} ({{ $normPct }}%)
                            </span>
                        </div>
                        <span class="text-slate-400 dark:text-gray-500 font-medium">Contiguous threshold scale</span>
                    </div>
                </div>
            </section>

            {{-- REGISTRY PANEL --}}
            <section class="bg-white dark:bg-gray-900 rounded-[22px] border border-slate-200/80 dark:border-gray-800 p-5 sm:p-6 shadow-[0_1px_3px_rgba(0,0,0,0.02)] flex flex-col flex-1 mb-8">
                
                {{-- Panel Top Header --}}
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-5 border-b border-slate-100 dark:border-gray-800">
                    <div>
                        <div class="flex items-center gap-3">
                            <h2 class="text-lg sm:text-xl font-bold text-slate-800 dark:text-white tracking-tight">Security Alerts Registry</h2>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 dark:bg-gray-800 text-slate-600 dark:text-gray-300 border border-slate-200/70 dark:border-gray-700">
                                {{ $securityLogs->total() }} {{ Str::plural('event', $securityLogs->total()) }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-400 dark:text-gray-500 mt-0.5">Real-time Isolation Forest anomaly detection logs and event triage</p>
                    </div>

                    {{-- Search Input Form --}}
                    <form action="{{ route('admin.security') }}" method="GET" class="relative w-full md:w-80">
                        @if($currentStatus) <input type="hidden" name="status" value="{{ $currentStatus }}"> @endif
                        @if($currentType) <input type="hidden" name="type" value="{{ $currentType }}"> @endif
                        @if($currentDate) <input type="hidden" name="date" value="{{ $currentDate }}"> @endif
                        @if($currentSort && $currentSort !== 'severity_desc') <input type="hidden" name="sort" value="{{ $currentSort }}"> @endif

                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-gray-500">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        </div>
                        <input type="text" 
                               name="search" 
                               value="{{ $currentSearch }}" 
                               placeholder="Search user, email, IP, or event..." 
                               class="w-full bg-slate-50 dark:bg-gray-800/80 border border-slate-200 dark:border-gray-700/80 rounded-xl pl-9 pr-8 py-2 text-xs sm:text-sm text-slate-800 dark:text-gray-200 placeholder-slate-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-[#0B5694]/20 focus:border-[#0B5694] transition-all font-medium">
                        @if($currentSearch)
                            <a href="{{ route('admin.security', array_filter(request()->except(['page', 'search']))) }}" 
                               class="absolute inset-y-0 right-0 pr-3 flex items-center text-xs font-bold text-slate-400 hover:text-rose-500 transition-colors" 
                               title="Clear search">✕</a>
                        @endif
                    </form>
                </div>

                {{-- Secondary Filter Controls Bar (Status Pills + Type + Date + Sort) --}}
                <div class="py-4 flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-slate-100 dark:border-gray-800/60">
                    {{-- Status Filter Pills --}}
                    <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar shrink-0">
                        <span class="text-xs font-semibold text-slate-400 dark:text-gray-500 uppercase tracking-wider mr-1 text-[11px]">Severity:</span>
                        
                        {{-- All --}}
                        <a href="{{ route('admin.security', array_filter(array_merge(request()->except(['page', 'status'])))) }}"
                           class="px-3 py-1.5 rounded-full text-xs font-semibold transition-all whitespace-nowrap {{ empty($currentStatus) ? 'bg-[#0B5694] text-white shadow-sm' : 'bg-slate-100 dark:bg-gray-800 text-slate-600 dark:text-gray-400 hover:bg-slate-200/70' }}">
                            All ({{ $totalCount }})
                        </a>

                        {{-- Critical --}}
                        <a href="{{ route('admin.security', array_filter(array_merge(request()->except('page'), ['status' => 'critical']))) }}"
                           class="px-3 py-1.5 rounded-full text-xs font-semibold transition-all whitespace-nowrap {{ $currentStatus === 'critical' ? 'bg-rose-600 text-white shadow-sm' : 'bg-rose-50 dark:bg-rose-950/30 text-rose-600 dark:text-rose-400 hover:bg-rose-100 border border-rose-150 dark:border-rose-900/50' }}">
                            Critical ({{ $criticalCount }})
                        </a>

                        {{-- Warning --}}
                        <a href="{{ route('admin.security', array_filter(array_merge(request()->except('page'), ['status' => 'warning']))) }}"
                           class="px-3 py-1.5 rounded-full text-xs font-semibold transition-all whitespace-nowrap {{ $currentStatus === 'warning' ? 'bg-amber-600 text-white shadow-sm' : 'bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-400 hover:bg-amber-100 border border-amber-150 dark:border-amber-900/50' }}">
                            Warning ({{ $warningCount }})
                        </a>

                        {{-- Normal --}}
                        <a href="{{ route('admin.security', array_filter(array_merge(request()->except('page'), ['status' => 'normal']))) }}"
                           class="px-3 py-1.5 rounded-full text-xs font-semibold transition-all whitespace-nowrap {{ $currentStatus === 'normal' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-100 border border-emerald-150 dark:border-emerald-900/50' }}">
                            Normal ({{ $normalCount }})
                        </a>
                    </div>

                    {{-- Dropdown Selects: Type, Date, Sort --}}
                    <div class="flex flex-wrap items-center gap-2.5">
                        {{-- Activity Type Select --}}
                        <div class="relative">
                            <select onchange="window.location.href = this.value" 
                                    class="bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl px-3 py-1.5 text-xs font-semibold text-slate-700 dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-[#0B5694]/20 cursor-pointer">
                                <option value="{{ route('admin.security', array_filter(request()->except(['page', 'type']))) }}" {{ empty($currentType) ? 'selected' : '' }}>All Activity Types</option>
                                <option value="{{ route('admin.security', array_filter(array_merge(request()->except('page'), ['type' => 'auth']))) }}" {{ $currentType === 'auth' ? 'selected' : '' }}>Authentication</option>
                                <option value="{{ route('admin.security', array_filter(array_merge(request()->except('page'), ['type' => 'onboarding']))) }}" {{ $currentType === 'onboarding' ? 'selected' : '' }}>Onboarding</option>
                                <option value="{{ route('admin.security', array_filter(array_merge(request()->except('page'), ['type' => 'tasks']))) }}" {{ $currentType === 'tasks' ? 'selected' : '' }}>Tasks</option>
                                <option value="{{ route('admin.security', array_filter(array_merge(request()->except('page'), ['type' => 'admin']))) }}" {{ $currentType === 'admin' ? 'selected' : '' }}>Administrative</option>
                            </select>
                        </div>

                        {{-- Date Range Select --}}
                        <div class="relative">
                            <select onchange="window.location.href = this.value" 
                                    class="bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl px-3 py-1.5 text-xs font-semibold text-slate-700 dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-[#0B5694]/20 cursor-pointer">
                                <option value="{{ route('admin.security', array_filter(request()->except(['page', 'date']))) }}" {{ empty($currentDate) ? 'selected' : '' }}>All Time</option>
                                <option value="{{ route('admin.security', array_filter(array_merge(request()->except('page'), ['date' => 'today']))) }}" {{ $currentDate === 'today' ? 'selected' : '' }}>Today</option>
                                <option value="{{ route('admin.security', array_filter(array_merge(request()->except('page'), ['date' => '7d']))) }}" {{ $currentDate === '7d' ? 'selected' : '' }}>Last 7 Days</option>
                                <option value="{{ route('admin.security', array_filter(array_merge(request()->except('page'), ['date' => '30d']))) }}" {{ $currentDate === '30d' ? 'selected' : '' }}>Last 30 Days</option>
                            </select>
                        </div>

                        {{-- Sort Select --}}
                        <div class="relative">
                            <select onchange="window.location.href = this.value" 
                                    class="bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl px-3 py-1.5 text-xs font-semibold text-slate-700 dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-[#0B5694]/20 cursor-pointer">
                                <option value="{{ route('admin.security', array_filter(array_merge(request()->except('page'), ['sort' => 'severity_desc']))) }}" {{ $currentSort === 'severity_desc' ? 'selected' : '' }}>Severity First</option>
                                <option value="{{ route('admin.security', array_filter(array_merge(request()->except('page'), ['sort' => 'newest']))) }}" {{ $currentSort === 'newest' ? 'selected' : '' }}>Newest First</option>
                                <option value="{{ route('admin.security', array_filter(array_merge(request()->except('page'), ['sort' => 'highest_score']))) }}" {{ $currentSort === 'highest_score' ? 'selected' : '' }}>Highest Score</option>
                                <option value="{{ route('admin.security', array_filter(array_merge(request()->except('page'), ['sort' => 'oldest']))) }}" {{ $currentSort === 'oldest' ? 'selected' : '' }}>Oldest First</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- DESKTOP SEVERITY-FIRST TABLE --}}
                <div class="hidden md:block overflow-x-auto mt-2">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50/80 dark:bg-gray-800/60 text-slate-400 dark:text-gray-500 text-[10px] font-bold uppercase tracking-wider">
                                <th class="px-5 py-3 rounded-l-xl">Event</th>
                                <th class="px-5 py-3">User</th>
                                <th class="px-5 py-3">Source & Time</th>
                                <th class="px-5 py-3">Anomaly Score</th>
                                <th class="px-5 py-3">Device</th>
                                <th class="px-5 py-3">Status</th>
                                <th class="px-5 py-3 text-right rounded-r-xl">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-gray-800/80">
                            @forelse($securityLogs as $log)
                            @php
                                $score = (float) $log->anomaly_score;
                                $isCritical = $score > $crit;
                                $isWarning = !$isCritical && $score > $warn;
                                $isNormal = !$isCritical && !$isWarning;

                                $rowAccentClass = match(true) {
                                    $isCritical => 'border-l-4 border-l-rose-500 bg-rose-50/20 dark:bg-rose-950/10',
                                    $isWarning => 'border-l-4 border-l-amber-500 bg-amber-50/20 dark:bg-amber-950/10',
                                    default => 'border-l-4 border-l-transparent hover:bg-slate-50/80 dark:hover:bg-gray-800/50',
                                };

                                $displayName = '';
                                if ($log->internDetails) {
                                    $displayName = $log->internDetails->first_name . ' ' . $log->internDetails->last_name;
                                } elseif ($log->supervisorDetails) {
                                    $displayName = $log->supervisorDetails->first_name . ' ' . $log->supervisorDetails->last_name;
                                } else {
                                    $displayName = ($log->user && $log->user->role_id === \App\Enums\UserRole::ADMIN) ? 'SmarTern Admin' : ($log->user->email ?? 'System');
                                }

                                $canEvaluate = false;
                                if ($log->user && $score > $crit && $log->user->locked_until && now()->lessThan($log->user->locked_until)) {
                                    $targetRoleVal = $log->user->role_id instanceof \App\Enums\UserRole ? $log->user->role_id->value : (int)$log->user->role_id;
                                    if ($targetRoleVal === \App\Enums\UserRole::SUPER_ADMIN->value) {
                                        $canEvaluate = false;
                                    } elseif ($targetRoleVal === \App\Enums\UserRole::ADMIN->value) {
                                        $canEvaluate = Auth::user()->role_id === \App\Enums\UserRole::SUPER_ADMIN;
                                    } else {
                                        $canEvaluate = true;
                                    }
                                }

                                $cleanDevice = str_replace('|', ' • ', $log->device ?? 'System');
                            @endphp
                            <tr class="transition-colors h-[70px] {{ $rowAccentClass }}">
                                {{-- 1. Event --}}
                                <td class="px-5 py-3">
                                    <div class="min-w-0 max-w-xs">
                                        <div class="text-slate-800 dark:text-gray-100 font-semibold text-xs sm:text-sm truncate" title="{{ $log->resource }}">
                                            {{ $log->resource ?: $log->action }}
                                        </div>
                                        <div class="mt-0.5">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 dark:bg-gray-800 text-slate-600 dark:text-gray-400">
                                                {{ $log->action }}
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                {{-- 2. User --}}
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        <x-avatar :user="$log->internDetails ?? $log->supervisorDetails ?? $log->user" size="8" border="1" circular="true" textSize="xs" />
                                        <div class="min-w-0">
                                            <div class="text-slate-800 dark:text-gray-200 font-bold text-xs truncate">
                                                {{ $displayName }}
                                            </div>
                                            <div class="text-slate-400 dark:text-gray-500 text-[11px] truncate">
                                                {{ $log->user->email ?? 'System' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- 3. Source & Time --}}
                                <td class="px-5 py-3 whitespace-nowrap">
                                    <div class="text-xs font-semibold text-slate-700 dark:text-gray-300">
                                        {{ $log->ip_address ?? 'Unknown IP' }}
                                    </div>
                                    <div class="text-[11px] text-slate-400 dark:text-gray-500" title="{{ $log->timestamp ? $log->timestamp->format('Y-m-d H:i:s') : 'N/A' }}">
                                        {{ $log->timestamp ? $log->timestamp->diffForHumans() : 'N/A' }}
                                    </div>
                                </td>

                                {{-- 4. Anomaly Score with Tick Marks --}}
                                <td class="px-5 py-3 whitespace-nowrap">
                                    <div class="flex items-center gap-2.5">
                                        <div class="relative w-24 sm:w-28 h-2 bg-slate-200/80 dark:bg-gray-800 rounded-full overflow-hidden shrink-0 border border-slate-200 dark:border-gray-700">
                                            {{-- Tick mark for Warning at 50% --}}
                                            <div class="absolute top-0 bottom-0 left-[50%] w-[1.5px] bg-slate-400 dark:bg-gray-500 z-10" title="Warning threshold: {{ number_format($warn, 2) }}"></div>
                                            {{-- Tick mark for Critical at 70% --}}
                                            <div class="absolute top-0 bottom-0 left-[70%] w-[1.5px] bg-slate-500 dark:bg-gray-400 z-10" title="Critical threshold: {{ number_format($crit, 2) }}"></div>
                                            {{-- Progress fill --}}
                                            <div class="h-full rounded-full transition-all duration-500 {{ $isCritical ? 'bg-rose-500' : ($isWarning ? 'bg-amber-500' : 'bg-emerald-500') }}" 
                                                 style="width: {{ min(100, max(0, $score * 100)) }}%"></div>
                                        </div>
                                        <span class="text-xs font-bold tabular-nums {{ $isCritical ? 'text-rose-600 dark:text-rose-400' : ($isWarning ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400') }}">
                                            {{ number_format($score, 2) }}
                                        </span>
                                    </div>
                                </td>

                                {{-- 5. Device --}}
                                <td class="px-5 py-3">
                                    <span class="text-xs text-slate-500 dark:text-gray-400 block max-w-[140px] truncate" title="{{ $log->user_agent ?? $cleanDevice }}">
                                        {{ $cleanDevice }}
                                    </span>
                                </td>

                                {{-- 6. Status --}}
                                <td class="px-5 py-3 whitespace-nowrap">
                                    @if($isCritical)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-rose-50 text-rose-700 border border-rose-200/60 dark:bg-rose-900/30 dark:text-rose-300 dark:border-rose-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            Critical
                                        </span>
                                    @elseif($isWarning)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200/60 dark:bg-amber-900/30 dark:text-amber-300 dark:border-amber-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            Warning
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60 dark:bg-emerald-900/30 dark:text-emerald-300 dark:border-emerald-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Normal
                                        </span>
                                    @endif
                                </td>

                                {{-- 7. Action --}}
                                <td class="px-5 py-3 text-right whitespace-nowrap">
                                    @if($isCritical || $isWarning)
                                        <button type="button" 
                                                @click="openReview(@js($log), @js($log->user), '{{ addslashes($displayName) }}', '{{ optional($log->user)->getRouteKey() }}', {{ $canEvaluate ? 'true' : 'false' }})" 
                                                class="px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all cursor-pointer {{ $isCritical ? 'bg-rose-50 text-rose-700 border-rose-200 hover:bg-rose-100 dark:bg-rose-950/30 dark:text-rose-300 dark:border-rose-800' : 'bg-amber-50 text-amber-700 border-amber-200 hover:bg-amber-100 dark:bg-amber-950/30 dark:text-amber-300 dark:border-amber-800' }}">
                                            Review
                                        </button>
                                    @else
                                        <button type="button" 
                                                @click="openReview(@js($log), @js($log->user), '{{ addslashes($displayName) }}', '{{ optional($log->user)->getRouteKey() }}', false)" 
                                                class="p-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-gray-300 rounded-lg transition-colors cursor-pointer" 
                                                title="View details" 
                                                aria-label="View details for {{ $displayName }}">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="py-16 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-gray-800 flex items-center justify-center text-slate-400 mb-3">
                                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                                        </div>
                                        <h3 class="text-sm font-bold text-slate-800 dark:text-gray-200">No security events found</h3>
                                        <p class="text-xs text-slate-400 dark:text-gray-500 mt-1">No activity records match your current filter criteria.</p>
                                        <a href="{{ route('admin.security') }}" class="mt-3 text-xs font-semibold text-[#0B5694] dark:text-blue-400 hover:underline">Clear all filters</a>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- MOBILE CARDS VIEW --}}
                <div class="md:hidden space-y-3 mt-4">
                    @forelse($securityLogs as $log)
                    @php
                        $score = (float) $log->anomaly_score;
                        $isCritical = $score > $crit;
                        $isWarning = !$isCritical && $score > $warn;

                        $displayNameMobile = '';
                        if ($log->internDetails) {
                            $displayNameMobile = $log->internDetails->first_name . ' ' . $log->internDetails->last_name;
                        } elseif ($log->supervisorDetails) {
                            $displayNameMobile = $log->supervisorDetails->first_name . ' ' . $log->supervisorDetails->last_name;
                        } else {
                            $displayNameMobile = ($log->user && $log->user->role_id === \App\Enums\UserRole::ADMIN) ? 'SmarTern Admin' : ($log->user->email ?? 'System');
                        }

                        $canEvaluateMobile = false;
                        if ($log->user && $score > $crit && $log->user->locked_until && now()->lessThan($log->user->locked_until)) {
                            $targetRoleVal = $log->user->role_id instanceof \App\Enums\UserRole ? $log->user->role_id->value : (int)$log->user->role_id;
                            if ($targetRoleVal === \App\Enums\UserRole::SUPER_ADMIN->value) {
                                $canEvaluateMobile = false;
                            } elseif ($targetRoleVal === \App\Enums\UserRole::ADMIN->value) {
                                $canEvaluateMobile = Auth::user()->role_id === \App\Enums\UserRole::SUPER_ADMIN;
                            } else {
                                $canEvaluateMobile = true;
                            }
                        }

                        $cleanDeviceMobile = str_replace('|', ' • ', $log->device ?? 'System');
                    @endphp
                    <div class="bg-slate-50/70 dark:bg-gray-800/50 p-4 rounded-2xl border border-slate-100 dark:border-gray-800 flex flex-col gap-3 relative overflow-hidden">
                        {{-- Left border severity accent --}}
                        <div class="absolute top-0 bottom-0 left-0 w-1.5 {{ $isCritical ? 'bg-rose-500' : ($isWarning ? 'bg-amber-500' : 'bg-emerald-500') }}"></div>

                        <div class="flex items-start justify-between gap-3 pl-1">
                            <div class="min-w-0">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-200/80 dark:bg-gray-700 text-slate-700 dark:text-gray-300">
                                    {{ $log->action }}
                                </span>
                                <div class="text-slate-800 dark:text-gray-100 font-bold text-sm leading-snug mt-1 truncate" title="{{ $log->resource }}">
                                    {{ $log->resource ?: $log->action }}
                                </div>
                            </div>
                            @if($isCritical)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-100 text-rose-700 border border-rose-200 shrink-0">Critical</span>
                            @elseif($isWarning)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-700 border border-amber-200 shrink-0">Warning</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-700 border border-emerald-200 shrink-0">Normal</span>
                            @endif
                        </div>

                        <div class="flex items-center gap-3 pl-1">
                            <x-avatar :user="$log->internDetails ?? $log->supervisorDetails ?? $log->user" size="8" border="1" circular="true" textSize="xs" />
                            <div class="min-w-0">
                                <div class="text-xs font-bold text-slate-800 dark:text-gray-200 truncate">{{ $displayNameMobile }}</div>
                                <div class="text-[11px] text-slate-400 dark:text-gray-500 truncate">{{ $log->user->email ?? 'System' }}</div>
                            </div>
                        </div>

                        <div class="text-xs text-slate-500 dark:text-gray-400 space-y-1 pt-1 border-t border-slate-200/60 dark:border-gray-700/60 pl-1">
                            <div class="flex justify-between">
                                <span>Source:</span>
                                <span class="font-medium text-slate-700 dark:text-gray-300">{{ $log->ip_address ?? 'Unknown' }} • {{ $log->timestamp ? $log->timestamp->diffForHumans() : 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Device:</span>
                                <span class="font-medium text-slate-700 dark:text-gray-300 truncate max-w-[180px]">{{ $cleanDeviceMobile }}</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-1 pl-1">
                            <span class="text-xs font-semibold text-slate-500">Anomaly Score:</span>
                            <div class="flex items-center gap-2">
                                <div class="relative w-20 h-2 bg-slate-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                    <div class="absolute top-0 bottom-0 left-[50%] w-[1px] bg-slate-400 z-10"></div>
                                    <div class="absolute top-0 bottom-0 left-[70%] w-[1px] bg-slate-500 z-10"></div>
                                    <div class="h-full {{ $isCritical ? 'bg-rose-500' : ($isWarning ? 'bg-amber-500' : 'bg-emerald-500') }}" style="width: {{ min(100, max(0, $score * 100)) }}%"></div>
                                </div>
                                <span class="text-xs font-bold {{ $isCritical ? 'text-rose-600' : ($isWarning ? 'text-amber-600' : 'text-emerald-600') }}">{{ number_format($score, 2) }}</span>
                            </div>
                        </div>

                        <div class="pt-2 border-t border-slate-200/60 dark:border-gray-700/60">
                            <button type="button" 
                                    @click="openReview(@js($log), @js($log->user), '{{ addslashes($displayNameMobile) }}', '{{ optional($log->user)->getRouteKey() }}', {{ $canEvaluateMobile ? 'true' : 'false' }})" 
                                    class="w-full min-h-[44px] rounded-xl text-xs font-semibold flex items-center justify-center gap-1.5 transition active:scale-95 shadow-sm cursor-pointer {{ $isCritical ? 'bg-rose-600 text-white hover:bg-rose-700' : ($isWarning ? 'bg-amber-600 text-white hover:bg-amber-700' : 'bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 text-slate-700 dark:text-gray-200') }}">
                                <span>{{ $isCritical || $isWarning ? 'Review Incident' : 'View Details' }}</span>
                            </button>
                        </div>
                    </div>
                    @empty
                        <div class="py-12 text-center">
                            <x-empty-state title="No security events recorded" compact="true" />
                        </div>
                    @endforelse
                </div>

                {{-- PAGINATION --}}
                <div class="mt-6 pt-5 border-t border-slate-100 dark:border-gray-800 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-xs text-slate-500 dark:text-gray-400 font-medium">
                        Showing <span class="font-bold text-slate-700 dark:text-gray-200">{{ $securityLogs->firstItem() ?? 0 }}</span> to <span class="font-bold text-slate-700 dark:text-gray-200">{{ $securityLogs->lastItem() ?? 0 }}</span> of <span class="font-bold text-slate-700 dark:text-gray-200">{{ $securityLogs->total() }}</span> events
                    </div>

                    <div class="flex items-center gap-1.5">
                        @if ($securityLogs->onFirstPage())
                            <span class="w-9 h-9 rounded-lg bg-slate-100 dark:bg-gray-800 text-slate-300 dark:text-gray-600 flex items-center justify-center cursor-not-allowed">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                            </span>
                        @else
                            <a href="{{ $securityLogs->previousPageUrl() }}" class="w-9 h-9 rounded-lg bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 text-slate-700 dark:text-gray-200 hover:bg-slate-50 dark:hover:bg-gray-700 flex items-center justify-center transition shadow-sm" title="Previous page">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                            </a>
                        @endif

                        <div class="flex items-center gap-1">
                            @foreach ($securityLogs->getUrlRange(max(1, $securityLogs->currentPage() - 2), min($securityLogs->lastPage(), $securityLogs->currentPage() + 2)) as $page => $url)
                                <a href="{{ $url }}" class="w-9 h-9 flex items-center justify-center rounded-lg text-xs font-semibold transition-all {{ $page == $securityLogs->currentPage() ? 'bg-[#0B5694] text-white shadow-sm' : 'bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 text-slate-600 dark:text-gray-300 hover:bg-slate-50 dark:hover:bg-gray-700' }}">{{ $page }}</a>
                            @endforeach
                        </div>

                        @if ($securityLogs->hasMorePages())
                            <a href="{{ $securityLogs->nextPageUrl() }}" class="w-9 h-9 rounded-lg bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 text-slate-700 dark:text-gray-200 hover:bg-slate-50 dark:hover:bg-gray-700 flex items-center justify-center transition shadow-sm" title="Next page">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                            </a>
                        @else
                            <span class="w-9 h-9 rounded-lg bg-slate-100 dark:bg-gray-800 text-slate-300 dark:text-gray-600 flex items-center justify-center cursor-not-allowed">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                            </span>
                        @endif
                    </div>
                </div>
            </section>

            {{-- RECENT ADMIN EVALUATIONS LOG TRAIL --}}
            <section class="bg-white dark:bg-gray-900 rounded-[22px] border border-slate-200/80 dark:border-gray-800 p-5 sm:p-6 shadow-[0_1px_3px_rgba(0,0,0,0.02)] flex flex-col mb-8">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-gray-800 mb-2">
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-slate-800 dark:text-white tracking-tight">Recent Admin Decisions</h2>
                        <p class="text-xs text-slate-400 dark:text-gray-500 mt-0.5">Audit log trail of security mitigations (mark safe, credential resets, suspensions)</p>
                    </div>
                </div>

                {{-- Desktop Table --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50/80 dark:bg-gray-800/60 text-slate-400 dark:text-gray-500 text-[10px] font-bold uppercase tracking-wider">
                                <th class="px-5 py-3 rounded-l-xl">Timestamp</th>
                                <th class="px-5 py-3">Administrator</th>
                                <th class="px-5 py-3">Mitigation Action</th>
                                <th class="px-5 py-3">Target Account</th>
                                <th class="px-5 py-3">Incident Reference</th>
                                <th class="px-5 py-3 text-right rounded-r-xl">Source IP</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-gray-800/80">
                            @forelse($unifiedLogs as $log)
                            @php
                                $targetUser = $log->target_user_id ? $targetUsers->get($log->target_user_id) : null;
                                $targetLog = $log->target_log_id ? $targetLogs->get($log->target_log_id) : null;
                                
                                if (str_contains($log->action, 'Marked Safe')) {
                                    $badgeColor = 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/30 dark:text-emerald-300';
                                    $actionLabel = 'Marked Safe';
                                } elseif (str_contains($log->action, 'Compromised')) {
                                    $badgeColor = 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/30 dark:text-amber-300';
                                    $actionLabel = 'Compromised (Reset)';
                                } else {
                                    $badgeColor = 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/30 dark:text-rose-300';
                                    $actionLabel = 'Suspended';
                                }
                            @endphp
                            <tr class="hover:bg-slate-50/70 dark:hover:bg-gray-800/50 transition-colors h-[64px]">
                                <td class="px-5 py-3 text-xs text-slate-500 dark:text-gray-400 whitespace-nowrap">
                                    <div class="font-semibold text-slate-700 dark:text-gray-200">{{ $log->timestamp->format('Y-m-d H:i') }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $log->timestamp->diffForHumans() }}</div>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="text-xs font-bold text-slate-800 dark:text-gray-100">{{ $log->evaluator_email }}</div>
                                    <span class="inline-block text-[10px] font-semibold text-indigo-600 dark:text-indigo-400">{{ $log->evaluator_role }}</span>
                                </td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-semibold border {{ $badgeColor }}">
                                        {{ $actionLabel }}
                                    </span>
                                </td>
                                <td class="px-5 py-3">
                                    @if($targetUser)
                                        <div class="text-xs font-bold text-slate-800 dark:text-gray-200">
                                            @if($targetUser->internDetails)
                                                {{ $targetUser->internDetails->first_name }} {{ $targetUser->internDetails->last_name }}
                                            @elseif($targetUser->supervisorDetails)
                                                {{ $targetUser->supervisorDetails->first_name }} {{ $targetUser->supervisorDetails->last_name }}
                                            @else
                                                System Account
                                            @endif
                                        </div>
                                        <div class="text-[10px] text-slate-400">{{ $targetUser->email }}</div>
                                    @else
                                        <span class="text-xs text-slate-400 italic">User #{{ $log->target_user_id }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-xs">
                                    @if($targetLog)
                                        <div class="font-semibold text-slate-700 dark:text-gray-300">{{ $targetLog->action }}</div>
                                        <div class="text-[10px] text-slate-400">Score: {{ number_format($targetLog->anomaly_score, 2) }}</div>
                                    @else
                                        <span class="text-slate-400 italic">Log #{{ $log->target_log_id }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-right text-xs text-slate-500 dark:text-gray-400 whitespace-nowrap">
                                    {{ $log->ip_address ?? 'N/A' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-xs text-slate-400">
                                    No admin evaluations recorded yet.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Mobile Cards --}}
                <div class="md:hidden space-y-3 mt-3">
                    @forelse($unifiedLogs as $log)
                    @php
                        $targetUser = $log->target_user_id ? $targetUsers->get($log->target_user_id) : null;
                        $targetLog = $log->target_log_id ? $targetLogs->get($log->target_log_id) : null;
                    @endphp
                    <div class="bg-slate-50/70 dark:bg-gray-800/40 p-4 rounded-xl border border-slate-100 dark:border-gray-800 space-y-2 text-xs">
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-slate-800 dark:text-gray-200">{{ $log->evaluator_email }}</span>
                            <span class="text-[10px] text-slate-400">{{ $log->timestamp->diffForHumans() }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500">Action:</span>
                            <span class="font-semibold text-indigo-600">{{ $log->action }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500">Target:</span>
                            <span class="font-medium text-slate-700 dark:text-gray-300">{{ $targetUser->email ?? 'User #' . $log->target_user_id }}</span>
                        </div>
                    </div>
                    @empty
                        <div class="py-8 text-center text-xs text-slate-400">No admin evaluations recorded yet.</div>
                    @endforelse
                </div>
            </section>
        </div>

        {{-- DETAIL SLIDE-OVER (Desktop) / BOTTOM SHEET (Mobile) --}}
        <div x-show="showDetail" 
             x-cloak 
             class="fixed inset-0 z-[120]" 
             role="dialog" 
             aria-modal="true" 
             aria-labelledby="detail-panel-title">
            
            {{-- Backdrop --}}
            <div x-show="showDetail" 
                 x-transition:enter="transition-opacity ease-linear duration-300" 
                 x-transition:enter-start="opacity-0" 
                 x-transition:enter-end="opacity-100" 
                 x-transition:leave="transition-opacity ease-linear duration-200" 
                 x-transition:leave-start="opacity-100" 
                 x-transition:leave-end="opacity-0" 
                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" 
                 @click="closeReview()"></div>

            {{-- Panel Container --}}
            <div class="fixed inset-0 overflow-hidden pointer-events-none flex justify-end items-end sm:items-stretch">
                <div x-show="showDetail" 
                     x-transition:enter="transform transition ease-in-out duration-300" 
                     x-transition:enter-start="translate-y-full sm:translate-y-0 sm:translate-x-full" 
                     x-transition:enter-end="translate-y-0 sm:translate-x-0" 
                     x-transition:leave="transform transition ease-in-out duration-200" 
                     x-transition:leave-start="translate-y-0 sm:translate-x-0" 
                     x-transition:leave-end="translate-y-full sm:translate-y-0 sm:translate-x-full" 
                     class="pointer-events-auto w-full max-w-lg bg-white dark:bg-gray-900 border-t sm:border-t-0 sm:border-l border-slate-200 dark:border-gray-800 rounded-t-3xl sm:rounded-none shadow-2xl flex flex-col max-h-[90vh] sm:max-h-full">
                    
                    {{-- Header --}}
                    <div class="p-6 border-b border-slate-100 dark:border-gray-800 flex items-center justify-between">
                        <div>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider mb-1"
                                  :class="detailLog?.anomaly_score > {{ $crit }} ? 'bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-950/40 dark:text-rose-300' : (detailLog?.anomaly_score > {{ $warn }} ? 'bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300' : 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300')"
                                  x-text="detailLog?.anomaly_score > {{ $crit }} ? 'Critical Alert' : (detailLog?.anomaly_score > {{ $warn }} ? 'Warning Alert' : 'Normal Event')">
                            </span>
                            <h3 id="detail-panel-title" class="text-lg font-bold text-slate-900 dark:text-white" x-text="detailLog?.action || 'Incident Breakdown'"></h3>
                        </div>
                        <button type="button" 
                                @click="closeReview()" 
                                class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-gray-200 rounded-xl hover:bg-slate-100 dark:hover:bg-gray-800 transition cursor-pointer" 
                                aria-label="Close panel">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18M6 6l12 12"/></svg>
                        </button>
                    </div>

                    {{-- Scrollable Content --}}
                    <div class="p-6 overflow-y-auto space-y-6 flex-1 custom-scrollbar">
                        {{-- Score Card with Tick Marks --}}
                        <div class="bg-slate-50 dark:bg-gray-800/60 p-5 rounded-2xl border border-slate-100 dark:border-gray-800">
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <span class="text-xs font-semibold text-slate-400 dark:text-gray-500 uppercase tracking-wider text-[10px]">Anomaly Score</span>
                                    <div class="text-2xl font-black text-slate-900 dark:text-white tabular-nums" x-text="parseFloat(detailLog?.anomaly_score || 0).toFixed(3)"></div>
                                </div>
                                <span class="text-xs font-bold px-3 py-1 rounded-full uppercase"
                                      :class="detailLog?.anomaly_score > {{ $crit }} ? 'bg-rose-100 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300' : (detailLog?.anomaly_score > {{ $warn }} ? 'bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300')"
                                      x-text="detailLog?.anomaly_score > {{ $crit }} ? 'High Deviation' : (detailLog?.anomaly_score > {{ $warn }} ? 'Moderate Deviation' : 'Within Baseline')">
                                </span>
                            </div>

                            {{-- Large Progress Bar with Ticks --}}
                            <div class="relative w-full h-3 bg-slate-200 dark:bg-gray-700 rounded-full overflow-hidden border border-slate-200 dark:border-gray-600 mt-2">
                                <div class="absolute top-0 bottom-0 left-[50%] w-[2px] bg-slate-400 dark:bg-gray-500 z-10" title="Warning Threshold: {{ number_format($warn, 2) }}"></div>
                                <div class="absolute top-0 bottom-0 left-[70%] w-[2px] bg-slate-500 dark:bg-gray-400 z-10" title="Critical Threshold: {{ number_format($crit, 2) }}"></div>
                                <div class="h-full rounded-full transition-all duration-500" 
                                     :class="detailLog?.anomaly_score > {{ $crit }} ? 'bg-rose-500' : (detailLog?.anomaly_score > {{ $warn }} ? 'bg-amber-500' : 'bg-emerald-500')"
                                     :style="`width: ${Math.min(100, Math.max(0, (detailLog?.anomaly_score || 0) * 100))}%`"></div>
                            </div>
                            <div class="flex justify-between text-[10px] font-semibold text-slate-400 dark:text-gray-500 mt-2">
                                <span>0.00 (Normal)</span>
                                <span class="text-amber-600 dark:text-amber-400">0.50 (Warning)</span>
                                <span class="text-rose-600 dark:text-rose-400">0.70 (Critical)</span>
                                <span>1.00</span>
                            </div>
                        </div>

                        {{-- Event Description & Details --}}
                        <div class="space-y-3">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-gray-500">Incident Context</h4>
                            <div class="bg-white dark:bg-gray-800/40 p-4 rounded-xl border border-slate-100 dark:border-gray-800 space-y-2 text-xs">
                                <div class="flex justify-between">
                                    <span class="text-slate-400">Description:</span>
                                    <span class="font-semibold text-slate-800 dark:text-gray-200 text-right max-w-xs" x-text="detailLog?.resource || 'N/A'"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-400">IP Address:</span>
                                    <span class="font-semibold text-slate-800 dark:text-gray-200" x-text="detailLog?.ip_address || 'Unknown'"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-400">Device Signature:</span>
                                    <span class="font-semibold text-slate-800 dark:text-gray-200" x-text="detailLog?.device ? detailLog.device.replace('|', ' • ') : 'System'"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-400">User Agent:</span>
                                    <span class="font-medium text-slate-600 dark:text-gray-400 text-right truncate max-w-xs" :title="detailLog?.user_agent" x-text="detailLog?.user_agent || 'Direct System Call'"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-400">Logged Time:</span>
                                    <span class="font-semibold text-slate-800 dark:text-gray-200" x-text="detailLog?.timestamp ? new Date(detailLog.timestamp).toLocaleString() : 'N/A'"></span>
                                </div>
                            </div>
                        </div>

                        {{-- User Context Card --}}
                        <div class="space-y-3">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-gray-500">Account Profile</h4>
                            <div class="bg-white dark:bg-gray-800/40 p-4 rounded-xl border border-slate-100 dark:border-gray-800 space-y-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-slate-200 dark:bg-gray-700 flex items-center justify-center font-bold text-xs text-slate-700 dark:text-gray-200" x-text="detailDisplayName ? detailDisplayName.substring(0,2).toUpperCase() : 'US'"></div>
                                    <div class="min-w-0">
                                        <div class="text-sm font-bold text-slate-900 dark:text-white truncate" x-text="detailDisplayName || 'System Account'"></div>
                                        <div class="text-xs text-slate-400 truncate" x-text="detailUser?.email || 'No email attached'"></div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-100 dark:border-gray-800 text-[11px]">
                                    <div class="bg-slate-50 dark:bg-gray-800 p-2.5 rounded-lg">
                                        <span class="text-slate-400 block">Total Anomalies</span>
                                        <span class="font-bold text-slate-800 dark:text-gray-100 text-sm" x-text="userMetrics[detailUser?.id]?.count || 0"></span>
                                    </div>
                                    <div class="bg-slate-50 dark:bg-gray-800 p-2.5 rounded-lg">
                                        <span class="text-slate-400 block">24h Persistence</span>
                                        <span class="font-bold text-slate-800 dark:text-gray-100 text-sm" x-text="userMetrics[detailUser?.id]?.persistence || 0"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Action Remediation Flow (Existing Backend Endpoints) --}}
                        <div class="space-y-3 pt-2">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-gray-500">Mitigation & Remediation</h4>
                            
                            <template x-if="canEvaluate">
                                <form :action="getSubmitUrl()" method="POST" class="space-y-3">
                                    @csrf
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-gray-300">Administrative Assessment</label>
                                    <select x-model="selectedAction" class="w-full bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-slate-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-[#0B5694]/20 cursor-pointer">
                                        <option value="safe">Mark Safe (Restore access & update baseline)</option>
                                        <option value="compromised">Mark Compromised (Force password & 2FA reset)</option>
                                        <option value="suspended">Suspend Account (Indefinite security lock)</option>
                                    </select>

                                    <p class="text-[11px] text-slate-500 dark:text-gray-400 leading-relaxed" x-show="selectedAction === 'safe'">
                                        * Clears temporary lockout and establishes this client signature into the user's verified baseline.
                                    </p>
                                    <p class="text-[11px] text-amber-600 dark:text-amber-400 leading-relaxed" x-show="selectedAction === 'compromised'">
                                        * Logs out the user globally, invalidates 2FA secrets, and requires credential renewal.
                                    </p>
                                    <p class="text-[11px] text-rose-600 dark:text-rose-400 leading-relaxed" x-show="selectedAction === 'suspended'">
                                        * Indefinitely restricts system access for this account until manual reactivation.
                                    </p>

                                    <button type="submit" 
                                            class="w-full min-h-[44px] mt-2 rounded-xl text-xs font-bold uppercase tracking-wider text-white shadow-sm transition active:scale-95 cursor-pointer"
                                            :class="selectedAction === 'safe' ? 'bg-emerald-600 hover:bg-emerald-700' : (selectedAction === 'compromised' ? 'bg-amber-600 hover:bg-amber-700' : 'bg-rose-600 hover:bg-rose-700')">
                                        Submit Assessment
                                    </button>
                                </form>
                            </template>

                            <template x-if="!canEvaluate">
                                <div class="bg-slate-50 dark:bg-gray-800/60 p-4 rounded-xl border border-slate-100 dark:border-gray-800 text-xs text-slate-500 dark:text-gray-400 flex items-center gap-3">
                                    <svg class="w-5 h-5 text-emerald-500 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                                    <span>No security intervention required for this record. Account is operating normally or evaluated.</span>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="p-4 border-t border-slate-100 dark:border-gray-800 bg-slate-50/50 dark:bg-gray-800/30">
                        <button type="button" 
                                @click="closeReview()" 
                                class="w-full min-h-[44px] bg-slate-100 dark:bg-gray-800 text-slate-700 dark:text-gray-300 font-semibold rounded-xl text-xs hover:bg-slate-200 transition cursor-pointer">
                            Done
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-page-layout>
@endsection
