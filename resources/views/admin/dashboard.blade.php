@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<x-page-layout portalType="Admin" userName="{{ Auth::user()->email }}" userRole="Admin">
    <div x-data="adminDashboardManager()" class="flex flex-col flex-1">
        {{-- 1. PAGE HEADER --}}
        <x-page-header title="Dashboard" subtitle="System Metrics & Administrative Oversight">
            <x-slot:actions>
                <div class="flex items-center gap-2 bg-white dark:bg-gray-900 border border-gray-150 dark:border-gray-800 px-3.5 py-1.5 rounded-full shadow-sm">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <span class="text-[11px] font-medium text-gray-500 dark:text-gray-400" x-data="{ lastUpdated: 'Just now' }" x-init="
                        const updateTime = () => {
                            const now = new Date();
                            lastUpdated = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                        };
                        updateTime();
                    ">
                        Last updated <span x-text="lastUpdated" class="font-semibold text-gray-700 dark:text-gray-300"></span>
                    </span>
                    <button type="button" 
                            @click="window.location.reload()" 
                            class="p-1 -mr-1 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-[#0B5694]/20 cursor-pointer min-w-[32px] min-h-[32px] flex items-center justify-center" 
                            title="Refresh Dashboard" 
                            aria-label="Refresh Dashboard">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                            <path d="M3 3v5h5"/>
                            <path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/>
                            <path d="M16 21h5v-5"/>
                        </svg>
                    </button>
                </div>
            </x-slot:actions>
        </x-page-header>

        {{-- SKELETON LOADING VIEW --}}
        @php
            $newUserCount = $newUsers->isEmpty() ? 1 : min($newUsers->count(), 5);
            $anomalyCount = $highRiskAnomalies->isEmpty() ? 1 : min($highRiskAnomalies->count(), 5);
        @endphp

        <div x-show="loading" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="flex flex-col flex-1">
            {{-- KPI Skeleton --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5 mb-6 lg:mb-8">
                @for($i = 0; $i < 4; $i++)
                    <div class="bg-white dark:bg-gray-900 rounded-3xl p-5 border border-gray-100 dark:border-gray-800 space-y-3">
                        <div class="flex justify-between items-center">
                            <x-skeleton type="text" class="w-1/2" />
                            <div class="w-8 h-8 rounded-xl bg-gray-200 dark:bg-gray-800 animate-shimmer"></div>
                        </div>
                        <div class="h-8 w-1/3 bg-gray-200 dark:bg-gray-800 rounded animate-shimmer"></div>
                        <x-skeleton type="text" class="w-2/3" />
                    </div>
                @endfor
            </div>

            {{-- Status Strip Skeleton --}}
            <div class="bg-white dark:bg-gray-900 rounded-3xl p-5 border border-gray-100 dark:border-gray-800 mb-6 lg:mb-8 space-y-4">
                <div class="flex justify-between items-center">
                    <x-skeleton type="text" class="w-1/4" />
                    <div class="h-5 w-24 bg-gray-200 dark:bg-gray-800 rounded-full animate-shimmer"></div>
                </div>
                <div class="flex flex-wrap gap-3">
                    @for($i = 0; $i < 5; $i++)
                        <div class="h-9 w-36 bg-gray-200 dark:bg-gray-800 rounded-2xl animate-shimmer"></div>
                    @endfor
                </div>
            </div>

            {{-- Main Section Skeleton (1/3 + 2/3) --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 mb-6 lg:mb-8">
                <div class="lg:col-span-5 xl:col-span-4 bg-[#0B5694]/10 dark:bg-gray-900 rounded-[32px] sm:rounded-[40px] p-6 sm:p-8 space-y-6">
                    <x-skeleton type="text" class="w-1/3" />
                    <div class="h-16 w-1/2 bg-gray-200 dark:bg-gray-800 rounded animate-shimmer"></div>
                    <x-skeleton type="text" class="w-2/3" />
                    <div class="h-11 w-full bg-gray-200 dark:bg-gray-800 rounded-2xl animate-shimmer"></div>
                </div>
                <x-card-container class="lg:col-span-7 xl:col-span-8" padding="p-6">
                    <x-skeleton type="text" class="w-1/4 mb-6" />
                    <x-skeleton type="list" :rows="min($anomalyCount, 4)" />
                </x-card-container>
            </div>

            {{-- Recent Users Skeleton --}}
            <x-card-container class="w-full" padding="p-6">
                <x-skeleton type="text" class="w-1/4 mb-6" />
                <x-skeleton type="list" :rows="min($newUserCount, 4)" />
            </x-card-container>
        </div>

        {{-- ACTUAL CONTENT VIEW --}}
        <div x-show="!loading" x-cloak x-transition:enter="transition ease-out duration-500 delay-200" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="flex flex-col flex-1">
            
            {{-- 2. KPI ROW (MOVED TO TOP) --}}
            @php
                $kpiCards = [
                    [
                        'title' => 'Total Users',
                        'value' => number_format($totalUsers),
                        'caption' => 'Provisioned accounts',
                        'link' => route('admin.account'),
                        'valueColor' => 'text-gray-900 dark:text-white',
                        'icon' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
                        'aria' => 'View account management'
                    ],
                    [
                        'title' => 'Certificates',
                        'value' => number_format($totalCertificates),
                        'caption' => 'Total generated',
                        'link' => null,
                        'valueColor' => 'text-emerald-500 dark:text-emerald-400',
                        'icon' => '<circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/>',
                        'aria' => null
                    ],
                    [
                        'title' => 'Active Offices',
                        'value' => number_format($totalDepartments),
                        'caption' => 'Operational units',
                        'link' => route('admin.offices'),
                        'valueColor' => 'text-gray-900 dark:text-white',
                        'icon' => '<rect x="4" y="2" width="16" height="20" rx="2" ry="2"/><path d="M9 22v-4h6v4"/><path d="M8 6h.01"/><path d="M16 6h.01"/><path d="M12 6h.01"/><path d="M12 10h.01"/><path d="M12 14h.01"/><path d="M16 10h.01"/><path d="M16 14h.01"/><path d="M8 10h.01"/><path d="M8 14h.01"/>',
                        'aria' => 'View office geofencing'
                    ],
                    [
                        'title' => 'Backup Snapshots',
                        'value' => $totalBackups,
                        'caption' => 'Stored locally',
                        'link' => route('admin.configuration'),
                        'valueColor' => 'text-gray-900 dark:text-white',
                        'icon' => '<ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/>',
                        'aria' => 'View system configuration and backups'
                    ],
                ];
            @endphp

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5 mb-6 lg:mb-8">
                @foreach($kpiCards as $card)
                    @php
                        $tag = $card['link'] ? 'a' : 'div';
                    @endphp
                    <{{ $tag }}
                        @if($card['link']) 
                            href="{{ $card['link'] }}" 
                            aria-label="{{ $card['aria'] }}"
                        @endif
                        class="bg-white dark:bg-gray-900 rounded-3xl p-4 sm:p-5 border border-gray-150 dark:border-gray-800 shadow-sm flex flex-col justify-between min-w-0 transition-all duration-200 {{ $card['link'] ? 'hover:border-blue-200 dark:hover:border-blue-800 hover:shadow-md active:scale-[0.99] group cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#0B5694]/20' : '' }}"
                    >
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="text-[11px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider truncate">{{ $card['title'] }}</span>
                            <div class="w-8 h-8 rounded-xl bg-gray-50 dark:bg-gray-800 flex items-center justify-center text-gray-400 dark:text-gray-500 shrink-0 {{ $card['link'] ? 'group-hover:text-[#0B5694] dark:group-hover:text-blue-400 transition-colors' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    {!! $card['icon'] !!}
                                </svg>
                            </div>
                        </div>
                        <div class="flex items-baseline justify-between gap-2 min-w-0">
                            <div>
                                <h3 class="text-2xl sm:text-3xl font-extrabold tracking-tight {{ $card['valueColor'] }} leading-none">{{ $card['value'] }}</h3>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400 font-medium mt-1 truncate">{{ $card['caption'] }}</p>
                            </div>
                            @if($card['link'])
                                <div class="text-gray-300 dark:text-gray-600 group-hover:text-[#0B5694] dark:group-hover:text-blue-400 group-hover:translate-x-0.5 transition-all shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
                                    </svg>
                                </div>
                            @endif
                        </div>
                    </{{ $tag }}>
                @endforeach
            </div>

            {{-- 3. SYSTEM STATUS STRIP --}}
            @php
                $allOperational = collect($services)->every(fn($s) => ($s['color'] ?? '') === 'green');
                $hasErrors = collect($services)->contains(fn($s) => ($s['color'] ?? '') === 'rose');
                
                $overallLabel = $allOperational ? 'Operational Status' : ($hasErrors ? 'System Degraded' : 'Operational Status (Warnings)');
                $overallBadgeClass = $allOperational
                    ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800'
                    : ($hasErrors
                        ? 'bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border-rose-200 dark:border-rose-800'
                        : 'bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border-amber-200 dark:border-amber-800');
                $overallDotClass = $allOperational ? 'bg-emerald-500' : ($hasErrors ? 'bg-rose-500' : 'bg-amber-500');
            @endphp

            <div class="bg-white dark:bg-gray-900 rounded-3xl p-4 sm:p-5 border border-gray-150 dark:border-gray-800 shadow-sm mb-6 lg:mb-8">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3.5 pb-3 border-b border-gray-100 dark:border-gray-800/80">
                    <div class="flex items-center gap-2.5">
                        <h4 class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">System Services</h4>
                        <span class="text-gray-300 dark:text-gray-700">•</span>
                        <span class="text-[11px] text-gray-500 dark:text-gray-400 font-medium">Core Infrastructure</span>
                    </div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border {{ $overallBadgeClass }} self-start sm:self-auto">
                        <span class="w-2 h-2 rounded-full {{ $overallDotClass }} {{ !$allOperational ? 'animate-ping' : '' }}"></span>
                        <span>{{ $overallLabel }}</span>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2 sm:gap-2.5">
                    @foreach($services as $service)
                        @php
                            $dotColor = match($service['color']) {
                                'green' => 'bg-emerald-500',
                                'rose' => 'bg-rose-500',
                                'orange' => 'bg-amber-500',
                                default => 'bg-gray-400'
                            };
                            $pillStyle = match($service['color']) {
                                'rose' => 'bg-rose-50 dark:bg-rose-950/30 border-rose-200 dark:border-rose-900/60 text-rose-900 dark:text-rose-200 ring-1 ring-rose-500/20',
                                'orange' => 'bg-amber-50 dark:bg-amber-950/30 border-amber-200 dark:border-amber-900/60 text-amber-900 dark:text-amber-200 ring-1 ring-amber-500/20',
                                default => 'bg-gray-50 dark:bg-gray-800/50 border-gray-150 dark:border-gray-800 text-gray-700 dark:text-gray-300'
                            };
                            $statusColor = match($service['color']) {
                                'rose' => 'text-rose-600 dark:text-rose-400',
                                'orange' => 'text-amber-600 dark:text-amber-400',
                                default => 'text-emerald-600 dark:text-emerald-400 font-bold'
                            };
                        @endphp
                        <div class="flex items-center gap-2.5 px-3 py-2 rounded-2xl border text-xs {{ $pillStyle }} transition-all">
                            <span class="w-2 h-2 rounded-full {{ $dotColor }} shrink-0 {{ $service['color'] !== 'green' ? 'animate-pulse' : '' }}"></span>
                            <span class="font-semibold text-gray-800 dark:text-gray-200 truncate">{{ $service['name'] }}</span>
                            <span class="text-[10px] uppercase font-bold tracking-wider {{ $statusColor }}">{{ $service['status'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- 4. MAIN TWO-COLUMN SECTION (1/3 + 2/3) --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 mb-6 lg:mb-8 items-start">
                
                {{-- LEFT (1/3): SECURITY ALERTS CARD --}}
                <div class="lg:col-span-5 xl:col-span-4 bg-[#0B5694] dark:bg-blue-600 text-white rounded-[32px] sm:rounded-[40px] p-6 sm:p-8 flex flex-col justify-between relative overflow-hidden shadow-sm min-w-0 h-full">
                    {{-- Shield Watermark Icon --}}
                    <div class="absolute -bottom-6 -right-6 w-36 h-36 opacity-10 pointer-events-none">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        </svg>
                    </div>

                    <div class="relative z-10">
                        <div class="flex items-center justify-between gap-2 mb-6">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-white/70">Security Alerts</p>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-white/15 text-white backdrop-blur-sm">
                                <span class="w-1.5 h-1.5 rounded-full {{ $flaggedLogins > 0 ? 'bg-rose-400 animate-pulse' : 'bg-emerald-400' }}"></span>
                                {{ $flaggedLogins > 0 ? 'Action Needed' : 'Monitoring' }}
                            </span>
                        </div>

                        <div class="mb-6 sm:mb-8">
                            <h3 class="text-5xl sm:text-6xl font-extrabold tracking-tight text-white leading-none">{{ $flaggedLogins }}</h3>
                            <p class="text-xs sm:text-sm font-bold uppercase tracking-wider text-white/80 mt-2">Flagged Security Anomalies</p>
                            <p class="text-xs text-blue-100/70 mt-1 font-medium">Events requiring administrative audit and review.</p>
                        </div>
                    </div>

                    <div class="relative z-10 flex flex-col sm:flex-row lg:flex-col gap-2.5 pt-4 border-t border-white/15">
                        <a href="{{ route('admin.security') }}" 
                           class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-white text-[#0B5694] dark:text-blue-700 text-xs font-bold uppercase tracking-wider shadow-md hover:bg-blue-50 transition-all active:scale-95 cursor-pointer min-h-[44px]">
                            Review alerts
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
                            </svg>
                        </a>
                        <a href="{{ route('admin.security') }}" 
                           class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl border border-white/40 hover:bg-white/10 text-white text-xs font-bold uppercase tracking-wider transition-all active:scale-95 cursor-pointer min-h-[44px]">
                            Run Diagnostics
                        </a>
                    </div>
                </div>

                {{-- RIGHT (2/3): HIGH-RISK SECURITY LOGS --}}
                <x-card-container 
                    class="lg:col-span-7 xl:col-span-8 min-w-0"
                    title="High-Risk Security Logs"
                    :headerActionLink="route('admin.security')"
                    headerActionText="View All"
                    padding="p-0"
                >
                    <div class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($highRiskAnomalies as $anomaly)
                            <div class="p-3 sm:px-5 sm:py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-gray-50/70 dark:hover:bg-gray-800/40 transition-colors">
                                {{-- Event Icon + Event Type --}}
                                <div class="flex items-center gap-3 min-w-0 flex-1">
                                    <div class="w-9 h-9 rounded-xl bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0 border border-rose-100 dark:border-rose-900/40">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/>
                                            <line x1="12" y1="9" x2="12" y2="13"/>
                                            <line x1="12" y1="17" x2="12.01" y2="17"/>
                                        </svg>
                                    </div>
                                    <div class="min-w-0 flex-1 text-left">
                                        <p class="text-[13px] sm:text-[14px] font-bold text-gray-800 dark:text-gray-100 uppercase tracking-tight truncate">
                                            {{ $anomaly->action }}
                                        </p>
                                        <p class="text-[11px] text-gray-400 dark:text-gray-500 font-medium truncate sm:hidden" title="{{ $anomaly->timestamp->toDayDateTimeString() }}">
                                            {{ $anomaly->timestamp->diffForHumans() }}
                                        </p>
                                    </div>
                                </div>

                                {{-- Severity Badge + Relative Time + Inspect Action --}}
                                <div class="flex items-center justify-between sm:justify-end gap-3 sm:gap-4 shrink-0">
                                    <div class="flex items-center gap-1.5" title="Anomaly Score: {{ number_format($anomaly->anomaly_score, 2) }}">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400 border border-rose-200/80 dark:border-rose-900/50">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            Risk: {{ number_format($anomaly->anomaly_score, 2) }}
                                        </span>
                                    </div>

                                    <span class="hidden sm:inline-block text-[11px] text-gray-400 dark:text-gray-500 font-medium whitespace-nowrap" 
                                          title="{{ $anomaly->timestamp->toDayDateTimeString() }}">
                                        {{ $anomaly->timestamp->diffForHumans() }}
                                    </span>

                                    <a href="{{ route('admin.security') }}" 
                                       class="px-3.5 py-1.5 min-h-[36px] sm:min-h-[32px] inline-flex items-center justify-center rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-200 text-[10px] font-bold uppercase tracking-wider transition-all active:scale-95 cursor-pointer shrink-0"
                                       aria-label="Inspect anomaly: {{ $anomaly->action }}">
                                        Inspect
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="p-8">
                                <x-empty-state 
                                    title="No high-risk anomalies detected" 
                                    compact="true"
                                />
                            </div>
                        @endforelse
                    </div>
                </x-card-container>
            </div>

            {{-- 5. RECENT USER ACCOUNTS (BELOW, SIZED TO CONTENT, NO DEAD SPACE) --}}
            <div class="mb-6 lg:mb-8">
                <x-card-container 
                    class="w-full"
                    title="Recent User Accounts"
                    :headerActionLink="route('admin.account')"
                    headerActionText="Manage Accounts"
                    padding="p-0"
                >
                    <div class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($newUsers as $user)
                            <div class="p-3.5 sm:px-6 sm:py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-gray-50/60 dark:hover:bg-gray-800/30 transition-colors">
                                <div class="flex items-center gap-3.5 min-w-0">
                                    <x-avatar :user="$user" size="10" border="1" circular="true" textSize="xs" />
                                    <div class="min-w-0 flex-1 text-left">
                                        <p class="text-[14px] sm:text-[15px] font-bold text-gray-800 dark:text-gray-200 truncate" title="{{ $user->email }}">
                                            {{ $user->email }}
                                        </p>
                                        <p class="text-[11px] text-gray-400 dark:text-gray-500 font-medium sm:hidden mt-0.5" title="{{ $user->created_at->toDayDateTimeString() }}">
                                            Joined {{ $user->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between sm:justify-end gap-3 sm:gap-6 shrink-0">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200/60 dark:border-gray-700/60">
                                        {{ $user->role->name }}
                                    </span>
                                    <span class="hidden sm:inline-block text-[11px] text-gray-400 dark:text-gray-500 font-medium whitespace-nowrap" title="{{ $user->created_at->toDayDateTimeString() }}">
                                        Joined {{ $user->created_at->diffForHumans() }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="p-8">
                                <x-empty-state 
                                    title="No recent user accounts" 
                                    compact="true"
                                />
                            </div>
                        @endforelse
                    </div>
                </x-card-container>
            </div>

        </div>
    </div>
</x-page-layout>
@endsection
