@extends('layouts.app')

@section('title', 'System Configuration - SmarTern Admin')

@section('content')
<x-page-layout 
    portalType="Admin" 
    userName="{{ Auth::user()->email }}" 
    userRole="System Administrator"
>
    <div 
        x-data="systemConfigManager()" 
        x-init="init()"
        class="flex flex-col flex-1 min-h-0 w-full"
    >
        {{-- ========================================================================= --}}
        {{-- 1. PAGE HEADER                                                            --}}
        {{-- ========================================================================= --}}
        <header class="mb-5 sm:mb-6 shrink-0 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                {{-- Breadcrumb --}}
                <nav class="flex items-center gap-1.5 text-xs font-medium text-slate-400 dark:text-gray-400 mb-1.5" aria-label="Breadcrumb">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-[#0B5694] dark:hover:text-blue-400 transition-colors flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                        <span>Dashboard</span>
                    </a>
                    <svg class="w-3 h-3 text-slate-300 dark:text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                    <span class="text-slate-600 dark:text-gray-300 font-semibold">System configuration</span>
                </nav>

                {{-- Title & Real-Content Subtitle --}}
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-tight">
                    System configuration
                </h1>
                <p class="text-xs sm:text-sm font-medium text-slate-500 dark:text-gray-400 mt-0.5">
                    Backups and data protection
                </p>
            </div>

            {{-- Header Action Buttons --}}
            <div class="flex items-center gap-2.5 sm:gap-3">
                {{-- Secondary Action: Upload Snapshot (Slide-over Trigger) --}}
                <button 
                    type="button" 
                    @click="openUploadSlideOver()"
                    class="h-10 sm:h-11 px-4 sm:px-5 rounded-xl bg-white dark:bg-gray-800 hover:bg-slate-50 dark:hover:bg-gray-700/80 text-[#0B5694] dark:text-blue-400 border border-slate-200/80 dark:border-gray-700 font-semibold text-xs sm:text-sm flex items-center justify-center gap-2 transition-all shadow-sm active:scale-95 cursor-pointer"
                    aria-haspopup="dialog"
                >
                    {{-- Upload Icon --}}
                    <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="17 8 12 3 7 8"/>
                        <line x1="12" y1="3" x2="12" y2="15"/>
                    </svg>
                    <span>Upload snapshot</span>
                </button>

                {{-- Primary Action: Back Up Now (Different Database Icon, Not Upload Icon) --}}
                <form 
                    action="{{ route('admin.backup.trigger') }}" 
                    method="POST" 
                    @submit="isTriggeringBackup = true"
                    class="inline-block"
                >
                    @csrf
                    <button 
                        type="submit" 
                        :disabled="isTriggeringBackup || isBackupRunning"
                        :aria-busy="isTriggeringBackup || isBackupRunning"
                        class="h-10 sm:h-11 px-4 sm:px-5 rounded-xl bg-[#0B5694] hover:bg-[#084577] dark:bg-blue-600 dark:hover:bg-blue-700 text-white font-semibold text-xs sm:text-sm flex items-center justify-center gap-2 transition-all shadow-md shadow-blue-900/10 active:scale-95 disabled:opacity-60 disabled:cursor-not-allowed cursor-pointer"
                    >
                        {{-- Database Icon (Differentiated from Upload) --}}
                        <template x-if="!isTriggeringBackup && !isBackupRunning">
                            <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <ellipse cx="12" cy="5" rx="9" ry="3"/>
                                <path d="M3 5V19A9 3 0 0 0 21 19V5"/>
                                <path d="M3 12A9 3 0 0 0 21 12"/>
                            </svg>
                        </template>

                        {{-- Spinner for in-progress --}}
                        <template x-if="isTriggeringBackup || isBackupRunning">
                            <svg class="w-4 h-4 shrink-0 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </template>

                        <span x-text="(isTriggeringBackup || isBackupRunning) ? 'Backing up...' : 'Back up now'"></span>
                    </button>
                </form>
            </div>
        </header>

        {{-- Flash Alerts --}}
        @if(session('success'))
            <div class="mb-5 p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200/80 dark:border-emerald-800/40 flex items-center gap-3 text-emerald-800 dark:text-emerald-300 text-xs sm:text-sm font-medium animate-in fade-in slide-in-from-top-2" role="status">
                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-5 p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200/80 dark:border-rose-800/40 flex items-start gap-3 text-rose-800 dark:text-rose-300 text-xs sm:text-sm font-medium animate-in fade-in slide-in-from-top-2" role="alert">
                <svg class="w-5 h-5 text-rose-600 dark:text-rose-400 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <div>
                    <span class="font-bold block">Action failed</span>
                    <span class="mt-0.5 block">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-5 p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200/80 dark:border-rose-800/40 flex items-start gap-3 text-rose-800 dark:text-rose-300 text-xs sm:text-sm font-medium animate-in fade-in slide-in-from-top-2" role="alert">
                <svg class="w-5 h-5 text-rose-600 dark:text-rose-400 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <div>
                    <span class="font-bold block">Please resolve the following issues:</span>
                    <ul class="list-disc list-inside mt-1 space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        {{-- SKELETON LOADING VIEW --}}
        <div x-show="loading" x-transition:leave="transition ease-in duration-300 pointer-events-none" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="flex flex-col flex-1">
            {{-- Banner Skeleton --}}
            <div class="bg-white dark:bg-gray-900 rounded-[22px] border border-slate-200/80 dark:border-gray-800 p-5 sm:p-6 mb-6">
                <div class="h-6 w-1/3 bg-slate-200 dark:bg-gray-800 rounded-lg animate-pulse mb-3"></div>
                <div class="h-4 w-1/2 bg-slate-100 dark:bg-gray-800/60 rounded animate-pulse"></div>
            </div>

            {{-- Table Skeleton --}}
            <div class="bg-white dark:bg-gray-900 rounded-[22px] border border-slate-200/80 dark:border-gray-800 p-5 sm:p-6">
                <div class="flex justify-between items-center pb-5 border-b border-slate-100 dark:border-gray-800 mb-4">
                    <div class="h-5 w-28 bg-slate-200 dark:bg-gray-700 rounded animate-pulse"></div>
                    <div class="h-9 w-64 bg-slate-200 dark:bg-gray-700 rounded-xl animate-pulse"></div>
                </div>
                <div class="space-y-3">
                    @for($i = 0; $i < 3; $i++)
                        <div class="h-16 bg-slate-50 dark:bg-gray-800/40 rounded-2xl animate-pulse border border-slate-100 dark:border-gray-800"></div>
                    @endfor
                </div>
            </div>
        </div>

        {{-- ACTUAL CONTENT VIEW --}}
        <div x-show="!loading" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="flex flex-col flex-1">

            {{-- ===================================================================== --}}
            {{-- 2. BACKUP HEALTH BANNER (ONE WIDE SOFT PANEL - NO STAT TILES)         --}}
            {{-- ===================================================================== --}}
            @php
                $healthLevel = $health['level'] ?? 'info';
                $healthStatus = $health['status'] ?? 'healthy';

                // Visual styling map based on health state
                $bannerClasses = match($healthLevel) {
                    'success' => 'bg-emerald-50/50 dark:bg-emerald-950/20 border-emerald-200/70 dark:border-emerald-900/40',
                    'warning' => 'bg-amber-50/60 dark:bg-amber-950/20 border-amber-200/70 dark:border-amber-900/40',
                    'critical', 'failed' => 'bg-rose-50/60 dark:bg-rose-950/20 border-rose-200/70 dark:border-rose-900/40',
                    default => 'bg-slate-50/70 dark:bg-gray-900 border-slate-200/80 dark:border-gray-800',
                };

                $iconContainerClasses = match($healthLevel) {
                    'success' => 'bg-emerald-100/80 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400',
                    'warning' => 'bg-amber-100/80 dark:bg-amber-900/40 text-amber-700 dark:text-amber-400',
                    'critical', 'failed' => 'bg-rose-100/80 dark:bg-rose-900/40 text-rose-700 dark:text-rose-400',
                    default => 'bg-blue-100/80 dark:bg-blue-900/40 text-[#0B5694] dark:text-blue-400',
                };

                $badgeClasses = match($healthLevel) {
                    'success' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
                    'warning' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-300 border-amber-200 dark:border-amber-800',
                    'critical', 'failed' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/60 dark:text-rose-300 border-rose-200 dark:border-rose-800',
                    default => 'bg-blue-100 text-[#0B5694] dark:bg-blue-900/60 dark:text-blue-300 border-blue-200 dark:border-blue-800',
                };

                $ariaRole = in_array($healthLevel, ['critical', 'failed']) ? 'alert' : 'status';
            @endphp

            <section 
                role="{{ $ariaRole }}"
                aria-label="Backup system health overview"
                class="rounded-[22px] border p-5 sm:p-6 mb-6 shadow-[0_1px_3px_rgba(0,0,0,0.02)] transition-colors {{ $bannerClasses }}"
            >
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5 lg:gap-8">
                    
                    {{-- ZONE 1: Status (Left) --}}
                    <div class="flex items-start gap-4 flex-1 min-w-0">
                        {{-- Distinct semantic status icon --}}
                        <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl flex items-center justify-center shrink-0 {{ $iconContainerClasses }}">
                            @if($healthLevel === 'success')
                                {{-- Shield Check Icon --}}
                                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/>
                                    <path d="m9 12 2 2 4-4"/>
                                </svg>
                            @elseif($healthLevel === 'warning')
                                {{-- Clock Alert Icon --}}
                                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"/>
                                    <polyline points="12 6 12 12 16 14"/>
                                </svg>
                            @elseif($healthStatus === 'running')
                                {{-- Spinner --}}
                                <svg class="w-6 h-6 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            @else
                                {{-- Alert Octagon Icon --}}
                                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"/>
                                    <line x1="12" y1="8" x2="12" y2="12"/>
                                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                                </svg>
                            @endif
                        </div>

                        {{-- Plain Language Headline & Detail --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <span class="text-[10px] font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-full border {{ $badgeClasses }}">
                                    @if($healthLevel === 'success') Healthy
                                    @elseif($healthStatus === 'stale') Stale schedule
                                    @elseif($healthStatus === 'critical') Critical delay
                                    @elseif($healthStatus === 'failed') Failed run
                                    @elseif($healthStatus === 'running') Running
                                    @else Setup required
                                    @endif
                                </span>
                            </div>

                            <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white leading-tight">
                                {{ $health['headline'] }}
                            </h2>

                            <p class="text-xs sm:text-sm text-slate-600 dark:text-gray-300 font-medium mt-1 leading-relaxed">
                                {{ $health['detail'] }}
                            </p>

                            {{-- Contextual Call-To-Action if failed or empty --}}
                            @if($healthStatus === 'failed')
                                <div class="mt-3 flex items-center gap-2">
                                    <form action="{{ route('admin.backup.trigger') }}" method="POST" @submit="isTriggeringBackup = true">
                                        @csrf
                                        <button 
                                            type="submit" 
                                            :disabled="isTriggeringBackup"
                                            class="h-8 px-3.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-semibold text-xs inline-flex items-center gap-1.5 transition-all shadow-sm active:scale-95 cursor-pointer"
                                        >
                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 2v6h-6"/><path d="M3 12a9 9 0 0 1 15-6.7L21 8"/><path d="M3 22v-6h6"/><path d="M21 12a9 9 0 0 1-15 6.7L3 16"/></svg>
                                            <span>Retry backup</span>
                                        </button>
                                    </form>
                                </div>
                            @elseif($healthStatus === 'empty')
                                <div class="mt-3 flex items-center gap-2">
                                    <form action="{{ route('admin.backup.trigger') }}" method="POST" @submit="isTriggeringBackup = true">
                                        @csrf
                                        <button 
                                            type="submit" 
                                            :disabled="isTriggeringBackup"
                                            class="h-8 px-3.5 rounded-lg bg-[#0B5694] hover:bg-[#084577] text-white font-semibold text-xs inline-flex items-center gap-1.5 transition-all shadow-sm active:scale-95 cursor-pointer"
                                        >
                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14a9 3 0 0 0 18 0V5"/></svg>
                                            <span>Create first backup</span>
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- ZONE 2: Schedule & Last Run (Right / Stacked on Tablet) --}}
                    <div class="flex flex-row sm:flex-row lg:flex-col items-start lg:items-end justify-between sm:justify-start lg:justify-center gap-4 sm:gap-6 pt-4 lg:pt-0 border-t border-slate-200/60 dark:border-gray-800/80 lg:border-t-0 lg:border-l lg:pl-8 shrink-0">
                        {{-- Next Scheduled Run (Derived from config and computed) --}}
                        <div class="text-left lg:text-right">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-gray-500 block mb-0.5">
                                Automated schedule
                            </span>
                            <div class="flex items-center lg:justify-end gap-1.5 text-xs sm:text-sm font-semibold text-slate-800 dark:text-gray-200">
                                <svg class="w-3.5 h-3.5 text-[#0B5694] dark:text-blue-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                <span>{{ $health['next_run_display'] ?? 'Daily at 7:00 PM' }}</span>
                            </div>
                        </div>

                        {{-- Last Run Timestamp --}}
                        <div class="text-right">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-gray-500 block mb-0.5">
                                Last recorded snapshot
                            </span>
                            <div 
                                class="text-xs sm:text-sm font-semibold text-slate-700 dark:text-gray-300"
                                @if($health['last_run'])
                                    title="{{ $health['last_run']->format('M d, Y h:i:s A') }}"
                                @endif
                            >
                                @if($health['last_run'])
                                    <span>{{ $health['last_run_diff'] }}</span>
                                    <span class="text-[10px] text-slate-400 dark:text-gray-500 block font-normal">{{ $health['last_run']->format('M d, Y') }}</span>
                                @else
                                    <span class="text-slate-400 dark:text-gray-500 italic font-normal">None recorded</span>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>
            </section>

            {{-- ===================================================================== --}}
            {{-- 3. BACKUPS PANEL                                                      --}}
            {{-- ===================================================================== --}}
            <section 
                aria-label="Backup snapshots directory"
                class="bg-white dark:bg-gray-900 rounded-[22px] border border-slate-200/80 dark:border-gray-800 p-5 sm:p-6 lg:p-7 shadow-[0_1px_3px_rgba(0,0,0,0.02)] flex flex-col flex-1"
            >
                {{-- Panel Header with Results Count, Search, & Filter Pills --}}
                <div class="flex flex-col gap-4 pb-5 border-b border-slate-100 dark:border-gray-800">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        {{-- Panel Title & Result Count --}}
                        <div class="flex items-center gap-2">
                            <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white">
                                Backups
                            </h2>
                            <span class="text-xs font-semibold text-slate-400 dark:text-gray-500">
                                ({{ number_format($backups->total()) }})
                            </span>
                        </div>

                        {{-- Search Input (by Date or Checksum Prefix) --}}
                        <form 
                            action="{{ route('admin.configuration') }}" 
                            method="GET"
                            class="relative w-full sm:w-72"
                        >
                            @if(request('filter'))
                                <input type="hidden" name="filter" value="{{ request('filter') }}">
                            @endif

                            <div class="relative flex items-center">
                                <span class="absolute left-3 text-slate-400 dark:text-gray-500 pointer-events-none">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                </span>
                                <input 
                                    type="text" 
                                    name="search" 
                                    value="{{ request('search') }}"
                                    placeholder="Search date or checksum prefix..." 
                                    class="w-full pl-9 pr-8 py-2 rounded-xl bg-slate-50 dark:bg-gray-800/80 border border-slate-200/70 dark:border-gray-700/80 text-xs font-medium text-slate-800 dark:text-gray-200 placeholder-slate-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-[#0B5694]/20 focus:border-[#0B5694] transition-all"
                                >
                                @if(request('search'))
                                    <a 
                                        href="{{ route('admin.configuration', array_filter(request()->except(['page', 'search']))) }}" 
                                        class="absolute right-2.5 p-1 rounded-md text-slate-400 hover:text-slate-600 dark:hover:text-gray-200 transition-colors"
                                        title="Clear search"
                                    >
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>

                    {{-- Filter Pills: All | Auto | Manual | Failed (Scrolls Horizontally on Mobile) --}}
                    <div class="overflow-x-auto pb-1 -mb-1 custom-scrollbar">
                        <div class="flex items-center gap-2 min-w-max">
                            {{-- All Pill --}}
                            @php
                                $allUrl = route('admin.configuration', array_filter(array_merge(request()->except(['page', 'filter', 'type', 'status']))));
                                $isAllActive = empty(request('filter')) || request('filter') === 'all';
                            @endphp
                            <a 
                                href="{{ $allUrl }}"
                                class="min-h-[40px] px-3.5 py-1.5 rounded-full text-xs font-semibold flex items-center gap-1.5 transition-all {{ $isAllActive ? 'bg-[#0B5694] text-white shadow-sm' : 'bg-slate-100 dark:bg-gray-800 text-slate-600 dark:text-gray-400 hover:bg-slate-200/80 dark:hover:bg-gray-700' }}"
                            >
                                <span>All</span>
                                <span class="text-[11px] px-1.5 py-0.2 rounded-full {{ $isAllActive ? 'bg-white/20 text-white' : 'bg-slate-200/80 dark:bg-gray-700 text-slate-600 dark:text-gray-300' }}">
                                    {{ $counts['all'] }}
                                </span>
                            </a>

                            {{-- Auto Pill --}}
                            @php
                                $autoUrl = route('admin.configuration', array_filter(array_merge(request()->except(['page', 'type', 'status']), ['filter' => 'auto'])));
                                $isAutoActive = request('filter') === 'auto';
                            @endphp
                            <a 
                                href="{{ $autoUrl }}"
                                class="min-h-[40px] px-3.5 py-1.5 rounded-full text-xs font-semibold flex items-center gap-1.5 transition-all {{ $isAutoActive ? 'bg-[#0B5694] text-white shadow-sm' : 'bg-slate-100 dark:bg-gray-800 text-slate-600 dark:text-gray-400 hover:bg-slate-200/80 dark:hover:bg-gray-700' }}"
                            >
                                <span>Auto</span>
                                <span class="text-[11px] px-1.5 py-0.2 rounded-full {{ $isAutoActive ? 'bg-white/20 text-white' : 'bg-slate-200/80 dark:bg-gray-700 text-slate-600 dark:text-gray-300' }}">
                                    {{ $counts['auto'] }}
                                </span>
                            </a>

                            {{-- Manual Pill --}}
                            @php
                                $manualUrl = route('admin.configuration', array_filter(array_merge(request()->except(['page', 'type', 'status']), ['filter' => 'manual'])));
                                $isManualActive = request('filter') === 'manual';
                            @endphp
                            <a 
                                href="{{ $manualUrl }}"
                                class="min-h-[40px] px-3.5 py-1.5 rounded-full text-xs font-semibold flex items-center gap-1.5 transition-all {{ $isManualActive ? 'bg-[#0B5694] text-white shadow-sm' : 'bg-slate-100 dark:bg-gray-800 text-slate-600 dark:text-gray-400 hover:bg-slate-200/80 dark:hover:bg-gray-700' }}"
                            >
                                <span>Manual</span>
                                <span class="text-[11px] px-1.5 py-0.2 rounded-full {{ $isManualActive ? 'bg-white/20 text-white' : 'bg-slate-200/80 dark:bg-gray-700 text-slate-600 dark:text-gray-300' }}">
                                    {{ $counts['manual'] }}
                                </span>
                            </a>

                            {{-- Failed Pill --}}
                            @php
                                $failedUrl = route('admin.configuration', array_filter(array_merge(request()->except(['page', 'type', 'status']), ['filter' => 'failed'])));
                                $isFailedActive = request('filter') === 'failed';
                            @endphp
                            <a 
                                href="{{ $failedUrl }}"
                                class="min-h-[40px] px-3.5 py-1.5 rounded-full text-xs font-semibold flex items-center gap-1.5 transition-all {{ $isFailedActive ? 'bg-rose-600 text-white shadow-sm' : 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400 hover:bg-rose-100 dark:hover:bg-rose-900/60 border border-rose-200/60 dark:border-rose-900/40' }}"
                            >
                                <span>Failed</span>
                                <span class="text-[11px] px-1.5 py-0.2 rounded-full {{ $isFailedActive ? 'bg-white/20 text-white' : 'bg-rose-200/60 dark:bg-rose-900/80 text-rose-800 dark:text-rose-300' }}">
                                    {{ $counts['failed'] }}
                                </span>
                            </a>

                            @if(request('filter') || request('search'))
                                <a 
                                    href="{{ route('admin.configuration') }}"
                                    class="text-xs font-medium text-slate-400 hover:text-[#0B5694] dark:hover:text-blue-400 ml-2 transition-colors"
                                >
                                    Reset filters
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Running Backup State (Session or in-progress DB row) --}}
                <template x-if="isBackupRunning">
                    <div class="mt-4 p-4 rounded-2xl bg-blue-50/70 dark:bg-blue-950/20 border border-blue-200/80 dark:border-blue-900/40 flex items-center justify-between gap-4 animate-pulse">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-[#0B5694] dark:text-blue-400 animate-spin shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <div>
                                <span class="font-bold text-xs sm:text-sm text-slate-900 dark:text-white block">Backup in progress...</span>
                                <span class="text-[11px] text-slate-500 dark:text-gray-400">Exporting database tables and packaging storage files. Actions will be available once finalized.</span>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-blue-100 text-[#0B5694] dark:bg-blue-900/60 dark:text-blue-300">
                            Running
                        </span>
                    </div>
                </template>

                {{-- ================================================================= --}}
                {{-- DESKTOP ROW STRIPS TABLE (>= 640px)                                --}}
                {{-- ================================================================= --}}
                <div class="hidden sm:block overflow-x-auto mt-4 custom-scrollbar">
                    <table class="w-full text-left border-separate border-spacing-y-2">
                        <thead>
                            <tr class="text-slate-400 dark:text-gray-500 text-[10px] font-bold uppercase tracking-wider">
                                <th class="px-4 py-2">Date & time</th>
                                <th class="px-4 py-2">Type</th>
                                <th class="px-4 py-2">Size</th>
                                <th class="px-4 py-2">Integrity (SHA-256)</th>
                                <th class="px-4 py-2">Status</th>
                                <th class="px-4 py-2 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($backups as $backup)
                                @php
                                    $isSuccess = $backup->status === 'success';
                                    $isFailed = $backup->status === 'failed';
                                    $isInProgress = $backup->status === 'in_progress';
                                    $isAuto = $backup->backup_type === 'auto';
                                    $isOnlySuccess = $health['total_successful'] === 1 && $isSuccess;

                                    // Truncate hash to first 6 and last 5 characters
                                    $rawHash = $backup->sha256_hash;
                                    $truncatedHash = $rawHash ? (substr($rawHash, 0, 6) . '…' . substr($rawHash, -5)) : 'N/A';
                                @endphp
                                <tr class="bg-slate-50/70 hover:bg-slate-100/70 dark:bg-gray-800/40 dark:hover:bg-gray-800/80 rounded-2xl transition-colors h-[68px]">
                                    
                                    {{-- 1. Date & Time --}}
                                    <td class="px-4 py-3 rounded-l-2xl">
                                        <div class="flex flex-col min-w-0">
                                            <span class="font-bold text-slate-800 dark:text-gray-100 text-xs sm:text-sm">
                                                {{ $backup->created_at->format('M d, Y') }}
                                            </span>
                                            <span class="text-[11px] text-slate-400 dark:text-gray-500 font-medium">
                                                {{ $backup->created_at->format('g:i A') }} · {{ $backup->created_at->diffForHumans() }}
                                            </span>
                                        </div>
                                    </td>

                                    {{-- 2. Type (Neutral Gray for Auto, Blue for Manual - No Purple!) --}}
                                    <td class="px-4 py-3">
                                        @if($isAuto)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 dark:bg-gray-700/80 text-slate-700 dark:text-gray-300 border border-slate-200/80 dark:border-gray-600/80">
                                                Auto
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-blue-50 dark:bg-blue-950/40 text-[#0B5694] dark:text-blue-400 border border-blue-100 dark:border-blue-900/40">
                                                Manual
                                            </span>
                                        @endif
                                    </td>

                                    {{-- 3. Size --}}
                                    <td class="px-4 py-3">
                                        <span class="text-xs sm:text-sm font-semibold text-slate-700 dark:text-gray-300 tabular-nums">
                                            {{ $backup->file_size ?? 'N/A' }}
                                        </span>
                                    </td>

                                    {{-- 4. Integrity (Truncated SHA-256 + Copy Button) --}}
                                    <td class="px-4 py-3">
                                        @if($rawHash)
                                            <div 
                                                x-data="{ copied: false }" 
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-white dark:bg-gray-900 border border-slate-200/70 dark:border-gray-700/70"
                                            >
                                                <code 
                                                    class="text-[11px] font-mono text-slate-600 dark:text-gray-300 select-all" 
                                                    title="SHA-256: {{ $rawHash }}"
                                                >
                                                    {{ $truncatedHash }}
                                                </code>

                                                <button 
                                                    type="button" 
                                                    @click="navigator.clipboard.writeText('{{ $rawHash }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                                    class="p-1 rounded text-slate-400 hover:text-[#0B5694] dark:hover:text-blue-400 transition-colors cursor-pointer"
                                                    aria-label="Copy SHA-256 hash"
                                                    :title="copied ? 'Copied to clipboard!' : 'Copy full hash'"
                                                >
                                                    <template x-if="!copied">
                                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
                                                    </template>
                                                    <template x-if="copied">
                                                        <svg class="w-3.5 h-3.5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                                                    </template>
                                                </button>
                                            </div>
                                        @else
                                            <span class="text-xs text-slate-400 italic">None</span>
                                        @endif
                                    </td>

                                    {{-- 5. Status --}}
                                    <td class="px-4 py-3">
                                        @if($isSuccess)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800/40">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                Success
                                            </span>
                                        @elseif($isFailed)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400 border border-rose-200/60 dark:border-rose-800/40" title="Backup execution encountered an error">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                Failed
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-sky-50 dark:bg-sky-950/40 text-sky-700 dark:text-sky-400 border border-sky-200/60 dark:border-sky-800/40">
                                                <span class="w-1.5 h-1.5 rounded-full bg-sky-500 animate-pulse"></span>
                                                Running
                                            </span>
                                        @endif
                                    </td>

                                    {{-- 6. Actions (Labeled Restore, Download Icon, Kebab Menu with Delete) --}}
                                    <td class="px-4 py-3 rounded-r-2xl text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            {{-- Labeled Restore Button --}}
                                            @if($isSuccess)
                                                <button 
                                                    type="button" 
                                                    @click="openRestoreModal({
                                                        id: '{{ $backup->getRouteKey() }}',
                                                        date: '{{ $backup->created_at->format('M d, Y') }}',
                                                        time: '{{ $backup->created_at->format('g:i A') }}',
                                                        size: '{{ $backup->file_size ?? 'N/A' }}',
                                                        hash: '{{ $truncatedHash }}',
                                                        type: '{{ ucfirst($backup->backup_type) }}'
                                                    })"
                                                    class="h-8 px-3 rounded-xl bg-white dark:bg-gray-800 hover:bg-slate-100 dark:hover:bg-gray-700 text-slate-700 dark:text-gray-200 border border-slate-200/80 dark:border-gray-700 font-semibold text-xs transition-all shadow-sm active:scale-95 cursor-pointer"
                                                >
                                                    Restore
                                                </button>

                                                {{-- Download Button --}}
                                                <a 
                                                    href="{{ route('admin.backup.download', $backup) }}" 
                                                    class="w-8 h-8 rounded-xl bg-white dark:bg-gray-800 hover:bg-slate-100 dark:hover:bg-gray-700 text-slate-600 dark:text-gray-300 border border-slate-200/80 dark:border-gray-700 inline-flex items-center justify-center transition-all shadow-sm active:scale-95 cursor-pointer"
                                                    title="Download snapshot archive (.zip)"
                                                    aria-label="Download backup snapshot from {{ $backup->created_at->format('M d, Y') }}"
                                                >
                                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                                </a>
                                            @else
                                                <button 
                                                    type="button" 
                                                    disabled
                                                    class="h-8 px-3 rounded-xl bg-slate-100 dark:bg-gray-800 text-slate-400 dark:text-gray-600 font-semibold text-xs cursor-not-allowed"
                                                    title="Restore is unavailable for failed or incomplete snapshots"
                                                >
                                                    Restore
                                                </button>
                                            @endif

                                            {{-- Kebab Menu for Delete and extra actions --}}
                                            <div 
                                                x-data="{ open: false }" 
                                                @keydown.escape.stop="open = false" 
                                                @click.outside="open = false"
                                                class="relative"
                                            >
                                                <button 
                                                    type="button" 
                                                    @click="open = !open" 
                                                    class="w-8 h-8 rounded-xl bg-white dark:bg-gray-800 hover:bg-slate-100 dark:hover:bg-gray-700 text-slate-500 dark:text-gray-400 border border-slate-200/80 dark:border-gray-700 inline-flex items-center justify-center transition-all shadow-sm active:scale-95 cursor-pointer"
                                                    aria-haspopup="true"
                                                    :aria-expanded="open"
                                                    aria-label="More actions for backup from {{ $backup->created_at->format('M d, Y') }}"
                                                >
                                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="1"/><circle cx="12" cy="5" r="1"/><circle cx="12" cy="19" r="1"/></svg>
                                                </button>

                                                <div 
                                                    x-show="open" 
                                                    x-transition:enter="transition ease-out duration-100" 
                                                    x-transition:enter-start="transform opacity-0 scale-95" 
                                                    x-transition:enter-end="transform opacity-100 scale-100" 
                                                    x-transition:leave="transition ease-in duration-75" 
                                                    x-transition:leave-start="transform opacity-100 scale-100" 
                                                    x-transition:leave-end="transform opacity-0 scale-95" 
                                                    class="absolute right-0 mt-1 w-44 rounded-xl bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 shadow-xl py-1 z-20"
                                                    x-cloak
                                                >
                                                    <button 
                                                        type="button" 
                                                        @click="open = false; openDeleteModal({
                                                            id: '{{ $backup->getRouteKey() }}',
                                                            date: '{{ $backup->created_at->format('M d, Y') }}',
                                                            time: '{{ $backup->created_at->format('g:i A') }}',
                                                            size: '{{ $backup->file_size ?? 'N/A' }}',
                                                            isOnlySuccess: {{ $isOnlySuccess ? 'true' : 'false' }}
                                                        })"
                                                        class="w-full text-left px-3.5 py-2 text-xs font-medium text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 dark:text-rose-400 flex items-center gap-2 transition-colors cursor-pointer"
                                                    >
                                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                                        <span>Delete backup</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center">
                                        <div class="max-w-sm mx-auto flex flex-col items-center">
                                            <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-gray-800 text-slate-400 dark:text-gray-500 flex items-center justify-center mb-3">
                                                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14a9 3 0 0 0 18 0V5"/></svg>
                                            </div>
                                            <h3 class="font-bold text-sm text-slate-800 dark:text-gray-200">No backups match your filters</h3>
                                            <p class="text-xs text-slate-400 dark:text-gray-500 mt-1 mb-4">Try clearing active filters or search terms.</p>
                                            <a 
                                                href="{{ route('admin.configuration') }}"
                                                class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-gray-800 hover:bg-slate-200 dark:hover:bg-gray-700 text-xs font-semibold text-slate-700 dark:text-gray-300 transition-colors"
                                            >
                                                Clear filters
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- ================================================================= --}}
                {{-- MOBILE STACKED CARDS (< 640px)                                    --}}
                {{-- ================================================================= --}}
                <div class="sm:hidden space-y-3 mt-4">
                    @forelse($backups as $backup)
                        @php
                            $isSuccess = $backup->status === 'success';
                            $isFailed = $backup->status === 'failed';
                            $isAuto = $backup->backup_type === 'auto';
                            $isOnlySuccess = $health['total_successful'] === 1 && $isSuccess;

                            $rawHash = $backup->sha256_hash;
                            $truncatedHash = $rawHash ? (substr($rawHash, 0, 6) . '…' . substr($rawHash, -5)) : 'N/A';
                        @endphp
                        <div class="bg-slate-50/80 dark:bg-gray-800/50 p-4 rounded-2xl border border-slate-200/80 dark:border-gray-700/80 space-y-3">
                            {{-- Card Header: Date & Status Pill --}}
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <span class="font-bold text-slate-900 dark:text-white text-sm block">
                                        {{ $backup->created_at->format('M d, Y') }}
                                    </span>
                                    <span class="text-[11px] text-slate-400 dark:text-gray-500 font-medium">
                                        {{ $backup->created_at->format('g:i A') }} · {{ $backup->created_at->diffForHumans() }}
                                    </span>
                                </div>

                                {{-- Status Pill --}}
                                @if($isSuccess)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800/40">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Success
                                    </span>
                                @elseif($isFailed)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400 border border-rose-200/60 dark:border-rose-800/40">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        Failed
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-sky-50 dark:bg-sky-950/40 text-sky-700 dark:text-sky-400 border border-sky-200/60 dark:border-sky-800/40">
                                        <span class="w-1.5 h-1.5 rounded-full bg-sky-500 animate-pulse"></span>
                                        Running
                                    </span>
                                @endif
                            </div>

                            {{-- Card Details: Type, Size, Integrity --}}
                            <div class="grid grid-cols-2 gap-2 text-xs pt-1 border-t border-slate-200/60 dark:border-gray-700/60">
                                <div>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-gray-500 block">Type</span>
                                    <span class="font-semibold text-slate-700 dark:text-gray-300">
                                        {{ ucfirst($backup->backup_type) }}
                                    </span>
                                </div>

                                <div>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-gray-500 block">Size</span>
                                    <span class="font-semibold text-slate-700 dark:text-gray-300 tabular-nums">
                                        {{ $backup->file_size ?? 'N/A' }}
                                    </span>
                                </div>
                            </div>

                            {{-- Integrity Checksum with Copy --}}
                            @if($rawHash)
                                <div 
                                    x-data="{ copied: false }" 
                                    class="p-2 rounded-xl bg-white dark:bg-gray-900 border border-slate-200/60 dark:border-gray-700/60 flex items-center justify-between gap-2"
                                >
                                    <div class="min-w-0">
                                        <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400 block">SHA-256</span>
                                        <code class="text-[10px] font-mono text-slate-600 dark:text-gray-300 truncate block">
                                            {{ $truncatedHash }}
                                        </code>
                                    </div>
                                    <button 
                                        type="button" 
                                        @click="navigator.clipboard.writeText('{{ $rawHash }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                        class="h-7 px-2.5 rounded-lg bg-slate-100 dark:bg-gray-800 text-slate-600 dark:text-gray-300 text-[10px] font-semibold inline-flex items-center gap-1 transition-colors cursor-pointer shrink-0"
                                        aria-label="Copy hash"
                                    >
                                        <template x-if="!copied">
                                            <span>Copy</span>
                                        </template>
                                        <template x-if="copied">
                                            <span class="text-emerald-600 font-bold">Copied!</span>
                                        </template>
                                    </button>
                                </div>
                            @endif

                            {{-- Mobile Card Actions --}}
                            <div class="flex items-center gap-2 pt-2 border-t border-slate-200/60 dark:border-gray-700/60">
                                @if($isSuccess)
                                    <button 
                                        type="button" 
                                        @click="openRestoreModal({
                                            id: '{{ $backup->getRouteKey() }}',
                                            date: '{{ $backup->created_at->format('M d, Y') }}',
                                            time: '{{ $backup->created_at->format('g:i A') }}',
                                            size: '{{ $backup->file_size ?? 'N/A' }}',
                                            hash: '{{ $truncatedHash }}',
                                            type: '{{ ucfirst($backup->backup_type) }}'
                                        })"
                                        class="flex-1 min-h-[44px] rounded-xl bg-white dark:bg-gray-800 hover:bg-slate-100 dark:hover:bg-gray-700 text-slate-800 dark:text-gray-200 border border-slate-200/80 dark:border-gray-700 font-semibold text-xs flex items-center justify-center gap-1.5 transition-all shadow-sm active:scale-95 cursor-pointer"
                                    >
                                        Restore
                                    </button>

                                    <a 
                                        href="{{ route('admin.backup.download', $backup) }}" 
                                        class="min-w-[44px] min-h-[44px] rounded-xl bg-white dark:bg-gray-800 hover:bg-slate-100 dark:hover:bg-gray-700 text-slate-700 dark:text-gray-300 border border-slate-200/80 dark:border-gray-700 flex items-center justify-center transition-all shadow-sm active:scale-95"
                                        aria-label="Download backup archive"
                                    >
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                    </a>
                                @endif

                                <button 
                                    type="button" 
                                    @click="openDeleteModal({
                                        id: '{{ $backup->getRouteKey() }}',
                                        date: '{{ $backup->created_at->format('M d, Y') }}',
                                        time: '{{ $backup->created_at->format('g:i A') }}',
                                        size: '{{ $backup->file_size ?? 'N/A' }}',
                                        isOnlySuccess: {{ $isOnlySuccess ? 'true' : 'false' }}
                                    })"
                                    class="min-w-[44px] min-h-[44px] rounded-xl bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-100 text-rose-600 dark:text-rose-400 border border-rose-200/80 dark:border-rose-900/40 flex items-center justify-center transition-all active:scale-95 cursor-pointer"
                                    aria-label="Delete backup"
                                >
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center bg-slate-50 dark:bg-gray-800/40 rounded-2xl border border-slate-100 dark:border-gray-800">
                            <h3 class="font-bold text-sm text-slate-800 dark:text-gray-200">No backups match your filters</h3>
                            <a href="{{ route('admin.configuration') }}" class="mt-3 inline-block px-4 py-2 rounded-xl bg-slate-100 dark:bg-gray-700 text-xs font-semibold text-slate-700 dark:text-gray-200">Clear filters</a>
                        </div>
                    @endforelse
                </div>

                {{-- Pagination Footer --}}
                <div class="mt-6 pt-5 border-t border-slate-100 dark:border-gray-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <p class="text-xs font-medium text-slate-400 dark:text-gray-500">
                        @if($backups->total() > 0)
                            Showing {{ $backups->firstItem() }}–{{ $backups->lastItem() }} of {{ number_format($backups->total()) }} backups
                        @else
                            Showing 0 backups
                        @endif
                    </p>

                    <div>
                        {{ $backups->links('vendor.pagination.tailwind-custom') }}
                    </div>
                </div>
            </section>
        </div>

        {{-- ========================================================================= --}}
        {{-- 4. UPLOAD SNAPSHOT SLIDE-OVER (SECTION F)                                 --}}
        {{-- ========================================================================= --}}
        <div 
            x-show="uploadSlideOverOpen" 
            x-cloak
            class="fixed inset-0 z-[130] flex justify-end"
            role="dialog"
            aria-modal="true"
            aria-labelledby="upload-slideover-title"
            @keydown.escape.window="handleCancelUpload()"
        >
            {{-- Backdrop --}}
            <div 
                x-show="uploadSlideOverOpen"
                x-transition:enter="transition-opacity ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"
                @click="handleCancelUpload()"
            ></div>

            {{-- Slide-over Panel --}}
            <div 
                x-show="uploadSlideOverOpen" 
                x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="translate-x-full"
                class="relative w-full sm:w-[460px] md:w-[480px] bg-white dark:bg-gray-900 h-full shadow-2xl flex flex-col z-10 border-l border-slate-200 dark:border-gray-800"
            >
                {{-- Slide-Over Header --}}
                <div class="p-5 sm:p-6 border-b border-slate-100 dark:border-gray-800 flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-blue-50 dark:bg-blue-900/30 text-[#0B5694] dark:text-blue-400 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        </div>
                        <div>
                            <h2 id="upload-slideover-title" class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-white leading-tight">
                                Upload snapshot
                            </h2>
                            <p class="text-xs text-slate-500 dark:text-gray-400 font-medium mt-0.5">
                                Import a previously exported SmarTern backup
                            </p>
                        </div>
                    </div>
                    <button 
                        type="button" 
                        @click="handleCancelUpload()"
                        class="p-2 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-gray-200 hover:bg-slate-100 dark:hover:bg-gray-800 transition-colors cursor-pointer"
                        aria-label="Close upload slide-over"
                    >
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>

                {{-- Slide-over Body: Scrollable Form --}}
                <form 
                    action="{{ route('admin.backup.upload') }}" 
                    method="POST" 
                    enctype="multipart/form-data"
                    @submit="isUploading = true"
                    class="flex-1 flex flex-col justify-between overflow-y-auto p-5 sm:p-6 space-y-5 custom-scrollbar"
                >
                    @csrf

                    <div class="space-y-5">
                        {{-- Drop Zone --}}
                        <div 
                            @dragover.prevent="isDragging = true"
                            @dragleave.prevent="isDragging = false"
                            @drop.prevent="handleDrop($event)"
                            :class="isDragging ? 'border-[#0B5694] bg-blue-50/50 dark:bg-blue-950/20' : 'border-slate-300 dark:border-gray-700 bg-slate-50/60 dark:bg-gray-800/40'"
                            class="border-2 border-dashed rounded-2xl p-6 sm:p-8 text-center transition-all"
                        >
                            <input 
                                type="file" 
                                id="backupFileInput" 
                                name="backup_zip" 
                                accept=".zip,application/zip,application/x-zip-compressed" 
                                required
                                @change="handleFileSelect($event)"
                                class="sr-only"
                            >

                            <div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-900/30 text-[#0B5694] dark:text-blue-400 flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                            </div>

                            <p class="text-xs sm:text-sm font-semibold text-slate-800 dark:text-gray-200">
                                Drag and drop your .zip file here, or
                            </p>

                            <button 
                                type="button" 
                                @click="document.getElementById('backupFileInput').click()"
                                class="mt-2 inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-white dark:bg-gray-800 text-[#0B5694] dark:text-blue-400 border border-slate-200 dark:border-gray-700 text-xs font-semibold hover:bg-slate-50 dark:hover:bg-gray-700 transition-colors cursor-pointer"
                            >
                                Choose file
                            </button>

                            <p class="mt-4 text-[11px] font-medium text-slate-400 dark:text-gray-500">
                                Accepted format: <span class="font-bold">.zip</span> · Maximum size: <span class="font-bold">100 MB</span>
                            </p>
                        </div>

                        {{-- File Selection Card --}}
                        <template x-if="selectedFileName">
                            <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-gray-800/70 border border-slate-200/80 dark:border-gray-700 flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                    </div>
                                    <div class="min-w-0">
                                        <span class="text-xs font-bold text-slate-800 dark:text-gray-200 truncate block" x-text="selectedFileName"></span>
                                        <span class="text-[10px] text-slate-400 dark:text-gray-500" x-text="selectedFileSize"></span>
                                    </div>
                                </div>
                                <button 
                                    type="button" 
                                    @click="clearSelectedFile()"
                                    class="p-1 rounded-lg text-slate-400 hover:text-rose-500 transition-colors"
                                    title="Remove file"
                                >
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                </button>
                            </div>
                        </template>

                        {{-- Snapshot Requirements Callout --}}
                        <div class="p-4 rounded-xl bg-slate-50 dark:bg-gray-800/40 border border-slate-200/60 dark:border-gray-700/60 text-xs text-slate-600 dark:text-gray-400 space-y-1.5 leading-relaxed">
                            <span class="font-bold text-slate-700 dark:text-gray-300 block">Snapshot requirements:</span>
                            <ul class="list-disc list-inside space-y-1 text-[11px]">
                                <li>Archive must contain an internal <code class="font-mono text-slate-700 dark:text-gray-300 font-semibold">database/</code> export.</li>
                                <li>Integrity checksum will be verified automatically upon saving.</li>
                                <li>The uploaded snapshot will become immediately available for restoration.</li>
                            </ul>
                        </div>
                    </div>

                    {{-- Slide-over Actions --}}
                    <div class="pt-5 border-t border-slate-100 dark:border-gray-800 flex items-center justify-end gap-3 shrink-0">
                        <button 
                            type="button" 
                            @click="handleCancelUpload()"
                            class="px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-gray-800 hover:bg-slate-200 dark:hover:bg-gray-700 text-xs font-semibold text-slate-600 dark:text-gray-300 transition-colors cursor-pointer"
                        >
                            Cancel
                        </button>

                        <button 
                            type="submit" 
                            :disabled="!selectedFileName || isUploading"
                            class="px-5 py-2.5 rounded-xl bg-[#0B5694] hover:bg-[#084577] dark:bg-blue-600 dark:hover:bg-blue-700 text-white font-semibold text-xs inline-flex items-center gap-2 transition-all shadow-md shadow-blue-900/10 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                        >
                            <template x-if="isUploading">
                                <svg class="w-3.5 h-3.5 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </template>
                            <span x-text="isUploading ? 'Uploading...' : 'Confirm upload'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- 5. GUARDED RESTORE MODAL (SECTION G)                                      --}}
        {{-- ========================================================================= --}}
        <div 
            x-show="restoreModalOpen" 
            x-cloak
            class="fixed inset-0 z-[140] flex items-end sm:items-center justify-center p-0 sm:p-4"
            role="alertdialog"
            aria-modal="true"
            aria-labelledby="restore-modal-title"
            aria-describedby="restore-modal-description"
            @keydown.escape.window="closeRestoreModal()"
        >
            {{-- Backdrop --}}
            <div 
                x-show="restoreModalOpen"
                x-transition:enter="transition-opacity ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"
                @click="closeRestoreModal()"
            ></div>

            {{-- Modal Content (Bottom Sheet on Mobile, Centered Modal on Desktop) --}}
            <div 
                x-show="restoreModalOpen"
                x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="translate-y-full sm:translate-y-0 sm:scale-95 sm:opacity-0"
                x-transition:enter-end="translate-y-0 sm:scale-100 sm:opacity-100"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="translate-y-0 sm:scale-100 sm:opacity-100"
                x-transition:leave-end="translate-y-full sm:translate-y-0 sm:scale-95 sm:opacity-0"
                class="relative w-full sm:max-w-lg bg-white dark:bg-gray-900 rounded-t-[28px] sm:rounded-[24px] p-6 sm:p-7 shadow-2xl border border-slate-200/80 dark:border-gray-800 z-10 space-y-5"
            >
                {{-- Header Icon & Title --}}
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    </div>
                    <div>
                        <h3 id="restore-modal-title" class="text-lg font-extrabold text-slate-900 dark:text-white leading-tight">
                            Restore system snapshot
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-gray-400 font-medium mt-1">
                            Overwrite existing application data with snapshot state
                        </p>
                    </div>
                </div>

                {{-- Selected Backup Metadata Strip --}}
                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-gray-800/60 border border-slate-200/70 dark:border-gray-700/70 text-xs space-y-1">
                    <div class="flex justify-between">
                        <span class="text-slate-400 dark:text-gray-500 font-medium">Snapshot date:</span>
                        <span class="font-bold text-slate-800 dark:text-gray-200" x-text="restoreTarget.date + ' at ' + restoreTarget.time"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400 dark:text-gray-500 font-medium">Type & Size:</span>
                        <span class="font-semibold text-slate-700 dark:text-gray-300" x-text="restoreTarget.type + ' · ' + restoreTarget.size"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400 dark:text-gray-500 font-medium">Integrity SHA-256:</span>
                        <code class="font-mono text-[11px] text-slate-600 dark:text-gray-300" x-text="restoreTarget.hash"></code>
                    </div>
                </div>

                {{-- Honest Plain-Language Explanation of Real Restore Behavior --}}
                <div id="restore-modal-description" class="p-4 rounded-xl bg-amber-50/80 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-900/40 text-xs text-amber-900 dark:text-amber-300 space-y-1.5 leading-relaxed">
                    <span class="font-bold block">Important impact on current system:</span>
                    <ul class="list-disc list-inside space-y-1 text-[11px]">
                        <li>All current database tables and public storage files will be replaced with this snapshot.</li>
                        <li>Any data entered after this snapshot was created will be permanently lost.</li>
                        <li>All active sessions will be terminated and you will need to log in again.</li>
                    </ul>
                </div>

                {{-- Type "RESTORE" Guarded Input --}}
                <div class="space-y-1.5">
                    <label for="restoreConfirmInput" class="block text-xs font-semibold text-slate-700 dark:text-gray-300">
                        Type <span class="font-mono font-bold text-rose-600 dark:text-rose-400">RESTORE</span> to confirm:
                    </label>
                    <input 
                        type="text" 
                        id="restoreConfirmInput" 
                        x-model="restoreInputText"
                        placeholder="RESTORE" 
                        autocomplete="off"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 text-xs font-mono font-bold text-slate-900 dark:text-white uppercase tracking-wider focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 transition-all"
                    >
                </div>

                {{-- Action Buttons (Cancel is default focused) --}}
                <div class="pt-2 flex items-center justify-end gap-3">
                    <button 
                        type="button" 
                        @click="closeRestoreModal()"
                        x-ref="cancelRestoreButton"
                        class="px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-gray-800 hover:bg-slate-200 dark:hover:bg-gray-700 text-xs font-semibold text-slate-700 dark:text-gray-300 transition-colors cursor-pointer"
                    >
                        Cancel
                    </button>

                    <button 
                        type="button" 
                        @click="submitRestore()"
                        :disabled="restoreInputText !== 'RESTORE' || isRestoring"
                        class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-semibold text-xs inline-flex items-center gap-2 transition-all shadow-md shadow-rose-900/10 active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer"
                    >
                        <template x-if="isRestoring">
                            <svg class="w-3.5 h-3.5 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </template>
                        <span x-text="isRestoring ? 'Restoring system...' : 'Restore system'"></span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Hidden Dynamic Restore Form --}}
        <form 
            id="globalRestoreForm" 
            :action="'/admin/configuration/backup/' + restoreTarget.id + '/restore'" 
            method="POST" 
            class="hidden"
        >
            @csrf
        </form>

        {{-- ========================================================================= --}}
        {{-- 6. GUARDED DELETE MODAL (SECTION G)                                       --}}
        {{-- ========================================================================= --}}
        <div 
            x-show="deleteModalOpen" 
            x-cloak
            class="fixed inset-0 z-[140] flex items-end sm:items-center justify-center p-0 sm:p-4"
            role="alertdialog"
            aria-modal="true"
            aria-labelledby="delete-modal-title"
            aria-describedby="delete-modal-description"
            @keydown.escape.window="closeDeleteModal()"
        >
            {{-- Backdrop --}}
            <div 
                x-show="deleteModalOpen"
                x-transition:enter="transition-opacity ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"
                @click="closeDeleteModal()"
            ></div>

            {{-- Modal Content --}}
            <div 
                x-show="deleteModalOpen"
                x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="translate-y-full sm:translate-y-0 sm:scale-95 sm:opacity-0"
                x-transition:enter-end="translate-y-0 sm:scale-100 sm:opacity-100"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="translate-y-0 sm:scale-100 sm:opacity-100"
                x-transition:leave-end="translate-y-full sm:translate-y-0 sm:scale-95 sm:opacity-0"
                class="relative w-full sm:max-w-md bg-white dark:bg-gray-900 rounded-t-[28px] sm:rounded-[24px] p-6 sm:p-7 shadow-2xl border border-slate-200/80 dark:border-gray-800 z-10 space-y-4"
            >
                {{-- Header Icon & Title --}}
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                    </div>
                    <div>
                        <h3 id="delete-modal-title" class="text-lg font-extrabold text-slate-900 dark:text-white leading-tight">
                            Delete backup snapshot
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-gray-400 font-medium mt-1">
                            Remove archive file and database registry record
                        </p>
                    </div>
                </div>

                {{-- CRITICAL ALERT: Only Successful Backup Warning --}}
                <template x-if="deleteTarget.isOnlySuccess">
                    <div class="p-4 rounded-xl bg-rose-100/80 dark:bg-rose-950/60 border border-rose-300 dark:border-rose-800 text-xs text-rose-900 dark:text-rose-200 space-y-1 leading-relaxed">
                        <span class="font-extrabold block uppercase tracking-wide flex items-center gap-1.5 text-rose-700 dark:text-rose-400">
                            <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            Critical Warning: Only Successful Backup
                        </span>
                        <p class="mt-1">
                            This is the <span class="font-bold underline">ONLY successful backup snapshot</span> in your system. Deleting it will leave your system with zero recovery points!
                        </p>
                    </div>
                </template>

                {{-- Standard Description --}}
                <div id="delete-modal-description" class="text-xs text-slate-600 dark:text-gray-300 leading-relaxed">
                    <p>
                        Are you sure you want to permanently delete the backup from 
                        <span class="font-bold text-slate-800 dark:text-white" x-text="deleteTarget.date + ' (' + deleteTarget.size + ')'"></span>?
                        The archived archive file will be deleted from storage. This action is irreversible.
                    </p>
                </div>

                {{-- Actions (Cancel is default focused) --}}
                <div class="pt-3 flex items-center justify-end gap-3">
                    <button 
                        type="button" 
                        @click="closeDeleteModal()"
                        x-ref="cancelDeleteButton"
                        class="px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-gray-800 hover:bg-slate-200 dark:hover:bg-gray-700 text-xs font-semibold text-slate-700 dark:text-gray-300 transition-colors cursor-pointer"
                    >
                        Cancel
                    </button>

                    <button 
                        type="button" 
                        @click="submitDelete()"
                        class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-semibold text-xs inline-flex items-center gap-2 transition-all shadow-md shadow-rose-900/10 active:scale-95 cursor-pointer"
                    >
                        Delete permanently
                    </button>
                </div>
            </div>
        </div>

        {{-- Hidden Dynamic Delete Form --}}
        <form 
            id="globalDeleteForm" 
            :action="'/admin/configuration/backup/' + deleteTarget.id" 
            method="POST" 
            class="hidden"
        >
            @csrf
            @method('DELETE')
        </form>

        {{-- ========================================================================= --}}
        {{-- 7. RESTORE IN-PROGRESS BLOCKING OVERLAY                                   --}}
        {{-- ========================================================================= --}}
        <div 
            x-show="isRestoring" 
            x-cloak
            class="fixed inset-0 z-[150] flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-md"
        >
            <div class="bg-white dark:bg-gray-900 rounded-[24px] p-8 text-center max-w-sm w-full shadow-2xl border border-slate-200 dark:border-gray-800 space-y-4">
                <div class="w-16 h-16 rounded-2xl bg-amber-50 dark:bg-amber-950/40 text-amber-500 mx-auto flex items-center justify-center animate-spin">
                    <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Restoring system</h3>
                <p class="text-xs text-slate-500 dark:text-gray-400 leading-relaxed">
                    Unpacking database and storage assets. Please do not refresh, close, or navigate away from this page.
                </p>
            </div>
        </div>

    </div>
</x-page-layout>
@endsection

@push('scripts')
<script>
function systemConfigManager() {
    return {
        loading: false,
        isTriggeringBackup: false,
        isBackupRunning: {{ ($health['status'] ?? '') === 'running' ? 'true' : 'false' }},
        uploadSlideOverOpen: false,
        isDragging: false,
        selectedFileName: '',
        selectedFileSize: '',
        isUploading: false,
        restoreModalOpen: false,
        restoreInputText: '',
        isRestoring: false,
        restoreTarget: {
            id: '',
            date: '',
            time: '',
            size: '',
            hash: '',
            type: ''
        },
        deleteModalOpen: false,
        deleteTarget: {
            id: '',
            date: '',
            time: '',
            size: '',
            isOnlySuccess: false
        },

        init() {
            // Keep page scroll synced with slide-over / modals
            this.$watch('uploadSlideOverOpen', value => {
                document.body.classList.toggle('overflow-hidden', value || this.restoreModalOpen || this.deleteModalOpen);
            });
            this.$watch('restoreModalOpen', value => {
                document.body.classList.toggle('overflow-hidden', value || this.uploadSlideOverOpen || this.deleteModalOpen);
                if (value) {
                    this.$nextTick(() => {
                        this.$refs.cancelRestoreButton && this.$refs.cancelRestoreButton.focus();
                    });
                }
            });
            this.$watch('deleteModalOpen', value => {
                document.body.classList.toggle('overflow-hidden', value || this.uploadSlideOverOpen || this.restoreModalOpen);
                if (value) {
                    this.$nextTick(() => {
                        this.$refs.cancelDeleteButton && this.$refs.cancelDeleteButton.focus();
                    });
                }
            });
        },

        openUploadSlideOver() {
            this.uploadSlideOverOpen = true;
        },

        handleCancelUpload() {
            if (this.isUploading) return;
            if (this.selectedFileName) {
                if (!confirm('Discard selected file and close?')) return;
            }
            this.uploadSlideOverOpen = false;
            this.clearSelectedFile();
        },

        handleFileSelect(event) {
            const file = event.target.files[0];
            if (file) {
                this.setFileInfo(file);
            }
        },

        handleDrop(event) {
            this.isDragging = false;
            const file = event.dataTransfer.files[0];
            if (file) {
                const input = document.getElementById('backupFileInput');
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                input.files = dataTransfer.files;
                this.setFileInfo(file);
            }
        },

        setFileInfo(file) {
            this.selectedFileName = file.name;
            const bytes = file.size;
            if (bytes === 0) {
                this.selectedFileSize = '0 Bytes';
                return;
            }
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            this.selectedFileSize = parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        },

        clearSelectedFile() {
            this.selectedFileName = '';
            this.selectedFileSize = '';
            const input = document.getElementById('backupFileInput');
            if (input) input.value = '';
        },

        openRestoreModal(target) {
            this.restoreTarget = target;
            this.restoreInputText = '';
            this.restoreModalOpen = true;
        },

        closeRestoreModal() {
            if (this.isRestoring) return;
            this.restoreModalOpen = false;
            this.restoreInputText = '';
        },

        submitRestore() {
            if (this.restoreInputText !== 'RESTORE' || this.isRestoring) return;
            this.isRestoring = true;
            this.restoreModalOpen = false;
            const form = document.getElementById('globalRestoreForm');
            if (form) form.submit();
        },

        openDeleteModal(target) {
            this.deleteTarget = target;
            this.deleteModalOpen = true;
        },

        closeDeleteModal() {
            this.deleteModalOpen = false;
        },

        submitDelete() {
            this.deleteModalOpen = false;
            const form = document.getElementById('globalDeleteForm');
            if (form) form.submit();
        }
    };
}
</script>
@endpush
