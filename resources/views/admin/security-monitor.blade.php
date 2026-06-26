@extends('layouts.app')

@section('title', 'Security Monitor')

@section('content')
<x-page-layout 
    portalType="Admin" 
    userName="{{ Auth::user()->email }}" 
    userRole="Admin"
>
    <div x-data="securityMonitorManager(@js($recentUserLogs))" class="flex flex-col flex-1">
        {{-- Header --}}
        <x-page-header 
            title="Security Monitor" 
            subtitle="Isolation Forest Anomaly Detection Engine" 
        >
            <x-slot name="actions">
                <a href="{{ route('admin.security.guide') }}" class="flex items-center gap-3 bg-rose-50 dark:bg-rose-900/20 px-6 py-3 border border-rose-100 dark:border-rose-800 rounded-xl text-[10px] font-bold text-rose-600 dark:text-rose-400 hover:bg-rose-100 transition uppercase tracking-wider active:scale-95 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                    Algorithm Guide
                </a>
                <a href="{{ route('admin.security.export') }}" class="flex items-center gap-3 bg-white dark:bg-gray-900 px-6 py-3 border border-gray-200 dark:border-gray-800 rounded-xl text-[10px] font-bold text-gray-700 dark:text-gray-400 shadow-sm hover:bg-gray-50 transition uppercase tracking-wider active:scale-95">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                    Export Log
                </a>
            </x-slot>
        </x-page-header>

        {{-- SKELETON LOADING VIEW --}}
        @php
            $logSkeletonCount = $securityLogs->isEmpty() ? 3 : min($securityLogs->count(), 5);
        @endphp

        <div x-show="loading" x-transition:leave="transition ease-in duration-300 pointer-events-none" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="flex flex-col flex-1">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8 mb-6 lg:mb-10">
                <x-skeleton type="metric" />
                <x-skeleton type="metric" />
                <x-skeleton type="metric" />
            </div>
            
            <x-table-container 
                title="Security Alerts Registry" 
                subtitle="Real-time system activity analysis"
            >
                <x-slot name="headerAction">
                    <div class="h-[44px] w-full sm:w-64 bg-gray-100 dark:bg-gray-800 rounded-2xl animate-shimmer"></div>
                </x-slot>

                <div class="hidden md:block">
                    <div class="h-12 bg-gray-50/50 dark:bg-gray-800/30 rounded-t-3xl animate-shimmer mb-2 border border-gray-100 dark:border-gray-800"></div>
                    <div class="space-y-4">
                        @for($i = 0; $i < $logSkeletonCount; $i++)
                            <div class="flex items-center justify-between p-6 bg-white dark:bg-gray-900 rounded-3xl border border-gray-100 dark:border-gray-800/50 animate-shimmer">
                                <div class="w-1/5 h-4 bg-gray-100 dark:bg-gray-800 rounded"></div>
                                <div class="w-1/4 flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-800"></div>
                                    <div class="w-1/2 h-4 bg-gray-100 dark:bg-gray-800 rounded"></div>
                                </div>
                                <div class="w-1/4 h-4 bg-gray-100 dark:bg-gray-800 rounded"></div>
                                <div class="w-1/6 h-4 bg-gray-100 dark:bg-gray-800 rounded"></div>
                                <div class="w-1/6 h-4 bg-gray-100 dark:bg-gray-800 rounded"></div>
                                <div class="w-1/12 h-6 bg-gray-100 dark:bg-gray-800 rounded-full"></div>
                            </div>
                        @endfor
                    </div>
                </div>

                <div class="md:hidden space-y-4">
                    @for($i = 0; $i < min($logSkeletonCount, 3); $i++)
                        <div class="bg-gray-50 dark:bg-gray-800/50 p-6 rounded-3xl border border-gray-100 dark:border-gray-700 animate-shimmer space-y-4">
                            <div class="flex justify-between items-center">
                                <div class="w-1/2 h-5 bg-gray-200 dark:bg-gray-700 rounded"></div>
                                <div class="w-12 h-6 bg-gray-200 dark:bg-gray-700 rounded-full"></div>
                            </div>
                            <div class="h-20 bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800"></div>
                        </div>
                    @endfor
                </div>
            </x-table-container>
        </div>

        {{-- ACTUAL CONTENT VIEW --}}
        <div x-show="!loading" x-cloak x-transition:enter="transition ease-out duration-500 delay-200" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="flex flex-col flex-1">
            {{-- Summary Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8 mb-6 lg:mb-10">
        <x-stat-card 
            title="Normal Activity" 
            :value="number_format($normalCount ?? 0)" 
            subtitle="Score < 0.5"
            color="white"
            icon='<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>'
        >
            <x-slot name="value">
                <span class="text-emerald-500">{{ number_format($normalCount ?? 0) }}</span>
            </x-slot>
        </x-stat-card>

        <x-stat-card 
            title="Warnings" 
            :value="number_format($warningCount ?? 0)" 
            subtitle="Score 0.51 - 0.7"
            color="white"
            icon='<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>'
        >
            <x-slot name="value">
                <span class="text-orange-500">{{ number_format($warningCount ?? 0) }}</span>
            </x-slot>
        </x-stat-card>

        <x-stat-card 
            title="Critical Alerts" 
            :value="number_format($criticalCount ?? 0)" 
            subtitle="Score > 0.7"
            color="white"
            icon='<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>'
        >
            <x-slot name="value">
                <span class="text-rose-500">{{ number_format($criticalCount ?? 0) }}</span>
            </x-slot>
        </x-stat-card>
    </div>

    {{-- Alerts Registry --}}
    <x-table-container 
        title="Security Alerts Registry" 
        subtitle="Real-time system activity analysis"
    >
        <x-slot name="headerAction">
            {{-- Custom Styled Filter --}}
            <div x-data="{ 
                open: false, 
                selected: '{{ match(request('type')) { 'auth' => 'Authentication', 'onboarding' => 'Onboarding', 'tasks' => 'Task Management', 'admin' => 'Administrative', default => 'All Activity' } }}',
                filter(type) {
                    const url = new URL(window.location.href);
                    if(type) url.searchParams.set('type', type);
                    else url.searchParams.delete('type');
                    url.searchParams.delete('page');
                    window.location.href = url.toString();
                }
            }" class="relative w-full sm:w-64">
                <button @click="open = !open" 
                        class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl px-6 min-h-[44px] text-[11px] font-bold text-gray-700 dark:text-gray-200 flex items-center justify-between hover:border-[#0B5694] transition-all uppercase tracking-wider shadow-sm cursor-pointer">
                    <span x-text="selected"></span>
                    <svg class="transition-transform duration-300" :class="open ? 'rotate-180' : ''" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </button>

                <div x-show="open" 
                     @click.away="open = false"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="absolute right-0 mt-3 w-full bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl shadow-xl z-50 overflow-hidden p-2 max-h-64 overflow-y-auto custom-scrollbar">
                    
                    @php
                        $types = [
                            '' => 'All Activity',
                            'auth' => 'Authentication',
                            'onboarding' => 'Onboarding',
                            'tasks' => 'Task Management',
                            'admin' => 'Administrative'
                        ];
                    @endphp
                    @foreach($types as $val => $label)
                        <button @click="filter('{{ $val }}'); open = false" 
                                class="w-full text-left px-5 py-3 text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-[#0B5694] dark:hover:text-blue-400 rounded-xl transition-colors cursor-pointer">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>
        </x-slot>

        {{-- Desktop Table --}}
        <div class="hidden md:block border border-gray-100 dark:border-gray-800 rounded-3xl overflow-hidden shadow-inner">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 dark:bg-gray-800/50 text-[10px] lg:text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider border-b border-gray-100 dark:border-gray-800">
                        <tr>
                            <th class="px-6 lg:px-8 py-5">Activity Type</th>
                            <th class="px-6 lg:px-8 py-5">User Account</th>
                            <th class="px-6 lg:px-8 py-5">Incident Details</th>
                            <th class="px-6 lg:px-8 py-5">Anomaly Analysis</th>
                            <th class="px-6 lg:px-8 py-5">Device</th>
                            <th class="px-6 lg:px-8 py-5 text-center">Status</th>
                            <th class="px-6 lg:px-8 py-5 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                        @forelse($securityLogs as $log)
                        @php
                            $score = $log->anomaly_score;
                            $color = $score > 0.7 ? 'rose' : ($score > 0.5 ? 'orange' : 'emerald');
                            
                            $displayName = '';
                            if($log->internDetails) {
                                $displayName = $log->internDetails->first_name . ' ' . $log->internDetails->last_name;
                            } elseif($log->supervisorDetails) {
                                $displayName = $log->supervisorDetails->first_name . ' ' . $log->supervisorDetails->last_name;
                            } else {
                                $displayName = ($log->user && $log->user->role_id === \App\Enums\UserRole::ADMIN) ? 'SmarTern Admin' : ($log->user->email ?? 'System');
                            }
                        @endphp
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors group">
                            <td class="px-6 lg:px-8 py-6">
                                <span class="font-black text-gray-800 dark:text-gray-200 text-xs lg:text-sm uppercase tracking-tight">{{ $log->action }}</span>
                            </td>
                            <td class="px-6 lg:px-8 py-6">
                                <div class="flex items-center gap-3">
                                    <x-avatar :user="$log->internDetails ?? $log->supervisorDetails ?? $log->user" size="8" border="1" circular="true" textSize="xs" />
                                    <span class="text-xs lg:text-sm font-bold text-gray-500 dark:text-gray-400">
                                        @if($log->internDetails)
                                            {{ $log->internDetails->first_name }} {{ $log->internDetails->last_name }}
                                        @elseif($log->supervisorDetails)
                                            {{ $log->supervisorDetails->first_name }} {{ $log->supervisorDetails->last_name }}
                                        @else
                                            {{ ($log->user && $log->user->role_id === \App\Enums\UserRole::ADMIN) ? 'SmarTern Admin' : ($log->user->email ?? 'System') }}
                                        @endif
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 lg:px-8 py-6">
                                <p class="text-xs lg:text-sm text-gray-400 dark:text-gray-500 font-medium italic leading-relaxed line-clamp-1">{{ $log->resource }}</p>
                                <span class="text-[9px] lg:text-[10px] text-gray-300 dark:text-gray-600 font-bold uppercase tracking-wider mt-1 block">IP: {{ $log->ip_address ?? 'Unknown' }} • {{ $log->timestamp ? $log->timestamp->format('m/d, H:i') : 'N/A' }}</span>
                            </td>
                            <td class="px-6 lg:px-8 py-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-24 lg:w-32 h-2 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden shadow-inner shrink-0 border border-gray-50 dark:border-gray-700">
                                        <div class="bg-{{ $color }}-500 h-full shadow-lg transition-all duration-1000" style="width: {{ $score * 100 }}%"></div>
                                    </div>
                                    <span class="text-[11px] lg:text-xs font-bold text-gray-700 dark:text-gray-300 tracking-tighter">{{ number_format($score, 2) }}</span>
                                </div>
                            </td>
                            <td class="px-6 lg:px-8 py-6">
                                <span class="text-xs lg:text-sm text-gray-400 dark:text-gray-500 font-medium leading-relaxed line-clamp-1 max-w-[140px] lg:max-w-[200px]" title="{{ $log->user_agent ?? 'Direct System Access' }}">
                                    {{ str_replace('|', ' • ', $log->device ?? 'System') }}
                                </span>
                            </td>
                            <td class="px-6 lg:px-8 py-6 text-center">
                                <span class="px-3 lg:px-4 py-1.5 rounded-full text-[9px] lg:text-[10px] font-bold uppercase tracking-wider border {{ $score > 0.7 ? 'bg-rose-50 text-rose-500 border-rose-100' : ($score > 0.5 ? 'bg-orange-50 text-orange-500 border-orange-100' : 'bg-emerald-50 text-emerald-500 border-emerald-100') }} dark:bg-opacity-10">
                                    {{ $score > 0.7 ? 'Critical' : ($score > 0.5 ? 'Warning' : 'Normal') }}
                                </span>
                            </td>
                            <td class="px-8 py-6 text-right">
                                @php
                                    $canEvaluate = false;
                                    if ($log->user && $log->anomaly_score > 0.7 && $log->user->locked_until && now()->lessThan($log->user->locked_until)) {
                                        $targetRoleVal = $log->user->role_id instanceof \App\Enums\UserRole ? $log->user->role_id->value : (int)$log->user->role_id;
                                        if ($targetRoleVal === \App\Enums\UserRole::SUPER_ADMIN->value) {
                                            $canEvaluate = false;
                                        } elseif ($targetRoleVal === \App\Enums\UserRole::ADMIN->value) {
                                            $canEvaluate = Auth::user()->role_id === \App\Enums\UserRole::SUPER_ADMIN;
                                        } else {
                                            $canEvaluate = true;
                                        }
                                    }
                                @endphp
                                @if($canEvaluate)
                                    <div class="flex flex-col items-end gap-1">
                                        @if($log->user->locked_until && now()->lessThan($log->user->locked_until))
                                            <span class="inline-block text-[9px] font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-900/20 px-2 py-0.5 rounded-md border border-rose-100 dark:border-rose-800/50 mb-1">
                                                @if(\Carbon\Carbon::parse($log->user->locked_until)->year > 9000)
                                                    Suspended
                                                @else
                                                    Locked (Temp)
                                                @endif
                                            </span>
                                        @endif
                                        <button @click="openEvaluation(@js($log->user), @js($log), '{{ addslashes($displayName) }}')" type="button" class="inline-flex items-center justify-center gap-1.5 px-5 py-2.5 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 rounded-lg text-[10px] font-bold uppercase tracking-wider hover:bg-indigo-100 dark:hover:bg-indigo-900/40 border border-indigo-100 dark:border-indigo-800 transition cursor-pointer">
                                            Evaluate
                                        </button>
                                    </div>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-20 text-center">
                                <x-empty-state title="No security events recorded" compact="true" />
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Mobile Cards --}}
        <div class="md:hidden space-y-6">
            @forelse($securityLogs as $log)
            @php
                $score = $log->anomaly_score;
                $color = $score > 0.7 ? 'rose' : ($score > 0.5 ? 'orange' : 'emerald');
            @endphp
            <div class="bg-gray-50 dark:bg-gray-800/50 p-6 rounded-3xl border border-gray-100 dark:border-gray-700 relative overflow-hidden">
                <div class="absolute top-0 left-0 w-2 h-full bg-{{ $color }}-500"></div>
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h5 class="text-gray-800 dark:text-gray-100 font-black uppercase tracking-tight">{{ $log->action }}</h5>
                        <p class="text-[10px] text-gray-400 dark:text-gray-500 font-bold uppercase tracking-wider mt-1">
                            {{ ($log->user && $log->user->role_id === \App\Enums\UserRole::ADMIN) ? 'SmarTern Admin' : ($log->user->email ?? 'System') }}
                        </p>
                    </div>
                    <span class="bg-{{ $color }}-500 text-white text-[9px] font-bold px-3 py-1 rounded-full uppercase tracking-wider shadow-sm">
                        {{ number_format($score, 2) }}
                    </span>
                </div>
                <div class="bg-white/50 dark:bg-gray-900/50 p-4 rounded-2xl border border-gray-100 dark:border-gray-700">
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium italic leading-relaxed">{{ $log->resource }}</p>
                    <p class="text-[9px] text-gray-400 dark:text-gray-500 font-bold uppercase tracking-wider mt-2">
                        IP: {{ $log->ip_address ?? 'Unknown' }} • {{ $log->timestamp ? $log->timestamp->format('m/d, H:i') : 'N/A' }}
                    </p>
                    <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-800 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-1 sm:gap-0 text-[9px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                        <p class="line-clamp-1 overflow-hidden" title="{{ $log->user_agent }}">Device: {{ str_replace('|', ' • ', $log->device ?? 'System') }}</p>
                        <p class="line-clamp-1 sm:ml-4 overflow-hidden" title="{{ $log->user_agent }}">UA: {{ $log->user_agent ?? 'System' }}</p>
                    </div>
                    @php
                        $canEvaluateMobile = false;
                        if ($log->user && $log->anomaly_score > 0.7 && $log->user->locked_until && now()->lessThan($log->user->locked_until)) {
                            $targetRoleVal = $log->user->role_id instanceof \App\Enums\UserRole ? $log->user->role_id->value : (int)$log->user->role_id;
                            if ($targetRoleVal === \App\Enums\UserRole::SUPER_ADMIN->value) {
                                $canEvaluateMobile = false;
                            } elseif ($targetRoleVal === \App\Enums\UserRole::ADMIN->value) {
                                $canEvaluateMobile = Auth::user()->role_id === \App\Enums\UserRole::SUPER_ADMIN;
                            } else {
                                $canEvaluateMobile = true;
                            }
                        }
                    @endphp
                            @php
                                $displayNameMobile = '';
                                if($log->internDetails) {
                                    $displayNameMobile = $log->internDetails->first_name . ' ' . $log->internDetails->last_name;
                                } elseif($log->supervisorDetails) {
                                    $displayNameMobile = $log->supervisorDetails->first_name . ' ' . $log->supervisorDetails->last_name;
                                } else {
                                    $displayNameMobile = ($log->user && $log->user->role_id === \App\Enums\UserRole::ADMIN) ? 'SmarTern Admin' : ($log->user->email ?? 'System');
                                }
                            @endphp
                        <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800/50 space-y-2">
                            @if($log->user->locked_until && now()->lessThan($log->user->locked_until))
                                <div class="text-center mb-2">
                                    <span class="inline-block text-[9px] font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-900/20 px-2 py-0.5 rounded-md border border-rose-100 dark:border-rose-800/50">
                                        Status: @if(\Carbon\Carbon::parse($log->user->locked_until)->year > 9000) Suspended @else Locked (Temp) @endif
                                    </span>
                                </div>
                            @endif
                            <button @click="openEvaluation(@js($log->user), @js($log), '{{ addslashes($displayNameMobile) }}')" type="button" class="w-full min-h-[44px] bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 rounded-lg text-[10px] font-bold uppercase tracking-wider hover:bg-indigo-100 dark:hover:bg-indigo-900/40 border border-indigo-100 dark:border-indigo-800 transition text-center cursor-pointer">
                                Evaluate Account
                            </button>
                        </div>
                </div>
            </div>
            @empty
                <x-empty-state title="No security events recorded" compact="true" />
            @endforelse
        </div>

        <div class="mt-6 lg:mt-10 pt-6 lg:pt-10 border-t border-gray-100 dark:border-gray-800 flex flex-col sm:flex-row justify-between items-center gap-6 lg:gap-8">
            <div class="text-left w-full sm:w-auto">
                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider italic">Event Registry Analysis</p>
                <div class="mt-4">
                    {{ $securityLogs->links('vendor.pagination.tailwind-custom') }}
                </div>
            </div>
        </div>
    </x-table-container>

    {{-- Recent Admin Evaluations Log Trail --}}
    <div class="mt-10">
        <x-table-container 
            title="Recent Admin Evaluations Log Trail" 
            subtitle="Historical record of security decisions (safe, compromised, suspended)"
        >
            {{-- Desktop Table --}}
            <div class="hidden md:block border border-gray-100 dark:border-gray-800 rounded-3xl overflow-hidden shadow-inner">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 dark:bg-gray-800/50 text-[10px] lg:text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider border-b border-gray-100 dark:border-gray-800">
                            <tr>
                                <th class="px-6 lg:px-8 py-5">Date & Time</th>
                                <th class="px-6 lg:px-8 py-5">Evaluator</th>
                                <th class="px-6 lg:px-8 py-5">Action Taken</th>
                                <th class="px-6 lg:px-8 py-5">Target Account</th>
                                <th class="px-6 lg:px-8 py-5">Reference Context</th>
                                <th class="px-6 lg:px-8 py-5">IP & Device</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                            @forelse($unifiedLogs as $log)
                            @php
                                $targetUser = $log->target_user_id ? $targetUsers->get($log->target_user_id) : null;
                                $targetLog = $log->target_log_id ? $targetLogs->get($log->target_log_id) : null;
                                
                                // Color theme based on action
                                if (str_contains($log->action, 'Marked Safe')) {
                                    $badgeColor = 'bg-emerald-50 text-emerald-500 border-emerald-100 dark:bg-emerald-950/20 dark:text-emerald-400 dark:border-emerald-800/30';
                                    $actionLabel = 'Marked Safe';
                                    $icon = '<span class="w-2 h-2 rounded-full bg-emerald-500 mr-2 inline-block"></span>';
                                } elseif (str_contains($log->action, 'Compromised')) {
                                    $badgeColor = 'bg-amber-50 text-amber-600 border-amber-100 dark:bg-amber-950/20 dark:text-amber-400 dark:border-amber-800/30';
                                    $actionLabel = 'Compromised (Reset)';
                                    $icon = '<span class="w-2 h-2 rounded-full bg-amber-500 mr-2 inline-block"></span>';
                                } else {
                                    $badgeColor = 'bg-rose-50 text-rose-500 border-rose-100 dark:bg-rose-950/20 dark:text-rose-400 dark:border-rose-800/30';
                                    $actionLabel = 'Suspended';
                                    $icon = '<span class="w-2 h-2 rounded-full bg-rose-500 mr-2 inline-block"></span>';
                                }
                            @endphp
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors group">
                                <td class="px-6 lg:px-8 py-6 text-xs lg:text-sm text-gray-500 dark:text-gray-400 font-bold whitespace-nowrap">
                                    {{ $log->timestamp->format('Y-m-d H:i:s') }}
                                    <span class="block text-[9px] text-gray-300 dark:text-gray-600 font-medium uppercase tracking-wider mt-1">{{ $log->timestamp->diffForHumans() }}</span>
                                </td>
                                <td class="px-6 lg:px-8 py-6 text-xs lg:text-sm font-bold text-gray-800 dark:text-gray-200">
                                    <span class="block">{{ $log->evaluator_email }}</span>
                                    <span class="inline-block text-[9px] font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/20 px-2 py-0.5 rounded-md border border-indigo-100 dark:border-indigo-800/50 mt-1">{{ $log->evaluator_role }}</span>
                                </td>
                                <td class="px-6 lg:px-8 py-6">
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-wider border {{ $badgeColor }}">
                                        {!! $icon !!}
                                        {{ $actionLabel }}
                                    </span>
                                </td>
                                <td class="px-6 lg:px-8 py-6">
                                    @if($targetUser)
                                        <div class="flex items-center gap-3">
                                            <x-avatar :user="$targetUser->internDetails ?? $targetUser->supervisorDetails ?? $targetUser" size="8" border="1" circular="true" textSize="xs" />
                                            <div>
                                                <span class="block text-xs lg:text-sm font-bold text-gray-700 dark:text-gray-300">
                                                    @if($targetUser->internDetails)
                                                        {{ $targetUser->internDetails->first_name }} {{ $targetUser->internDetails->last_name }}
                                                    @elseif($targetUser->supervisorDetails)
                                                        {{ $targetUser->supervisorDetails->first_name }} {{ $targetUser->supervisorDetails->last_name }}
                                                    @else
                                                        System User
                                                    @endif
                                                </span>
                                                <span class="text-[9px] font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider block mt-0.5">
                                                    {{ $targetUser->email }}
                                                </span>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-xs lg:text-sm text-gray-400 dark:text-gray-500 font-medium italic">Deleted Account (UID: {{ $log->target_user_id }})</span>
                                    @endif
                                </td>
                                <td class="px-6 lg:px-8 py-6 text-xs lg:text-sm">
                                    @if($targetLog)
                                        <span class="font-extrabold text-gray-800 dark:text-gray-200 uppercase tracking-tight block">{{ $targetLog->action }}</span>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-[9px] text-gray-400 dark:text-gray-500 font-bold uppercase tracking-wider">Score: {{ number_format($targetLog->anomaly_score, 2) }}</span>
                                            <span class="w-1.5 h-1.5 rounded-full bg-{{ $targetLog->anomaly_score > 0.7 ? 'rose' : ($targetLog->anomaly_score > 0.5 ? 'orange' : 'emerald') }}-500"></span>
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400 dark:text-gray-500 font-medium italic block">Log context not found</span>
                                        <span class="text-[9px] text-gray-300 dark:text-gray-600 font-bold uppercase tracking-wider mt-1 block">ID: {{ $log->target_log_id }}</span>
                                    @endif
                                </td>
                                <td class="px-6 lg:px-8 py-6 text-xs lg:text-sm text-gray-400 dark:text-gray-500 font-medium leading-relaxed whitespace-nowrap">
                                    <span class="block">IP: {{ $log->ip_address ?? 'N/A' }}</span>
                                    <span class="block text-[10px] text-gray-300 dark:text-gray-600 mt-1 uppercase tracking-tight" title="{{ $log->user_agent }}">{{ $log->device ?? 'Unknown' }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="py-20 text-center">
                                    <x-empty-state title="No admin evaluations logged yet" compact="true" />
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Mobile Cards --}}
            <div class="md:hidden space-y-6">
                @forelse($unifiedLogs as $log)
                @php
                    $targetUser = $log->target_user_id ? $targetUsers->get($log->target_user_id) : null;
                    $targetLog = $log->target_log_id ? $targetLogs->get($log->target_log_id) : null;

                    if (str_contains($log->action, 'Marked Safe')) {
                        $mobileBorder = 'border-emerald-500';
                        $actionLabel = 'Marked Safe';
                        $badgeColor = 'bg-emerald-50 text-emerald-500 border-emerald-100 dark:bg-emerald-950/20 dark:text-emerald-400';
                    } elseif (str_contains($log->action, 'Compromised')) {
                        $mobileBorder = 'border-amber-500';
                        $actionLabel = 'Compromised (Reset)';
                        $badgeColor = 'bg-amber-50 text-amber-600 border-amber-100 dark:bg-amber-950/20 dark:text-amber-400';
                    } else {
                        $mobileBorder = 'border-rose-500';
                        $actionLabel = 'Suspended';
                        $badgeColor = 'bg-rose-50 text-rose-500 border-rose-100 dark:bg-rose-950/20 dark:text-rose-400';
                    }
                @endphp
                <div class="bg-gray-50 dark:bg-gray-800/50 p-6 rounded-3xl border border-gray-100 dark:border-gray-700 relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-2 h-full {{ str_replace('border', 'bg', $mobileBorder) }}"></div>
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <span class="inline-block text-[9px] font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/20 px-2 py-0.5 rounded-md border border-indigo-100 dark:border-indigo-800/50 mb-1.5">{{ $log->evaluator_role }}</span>
                            <h5 class="text-gray-800 dark:text-gray-100 font-black text-xs uppercase tracking-tight">{{ $log->evaluator_email }}</h5>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-[9px] font-bold uppercase tracking-wider border {{ $badgeColor }}">
                            {{ $actionLabel }}
                        </span>
                    </div>

                    <div class="bg-white/50 dark:bg-gray-900/50 p-4 rounded-2xl border border-gray-100 dark:border-gray-700 space-y-3">
                        <div>
                            <p class="text-[9px] text-gray-400 dark:text-gray-500 font-extrabold uppercase tracking-wider mb-1">Target Account</p>
                            @if($targetUser)
                                <p class="text-xs font-bold text-gray-700 dark:text-gray-300">
                                    @if($targetUser->internDetails)
                                        {{ $targetUser->internDetails->first_name }} {{ $targetUser->internDetails->last_name }}
                                    @elseif($targetUser->supervisorDetails)
                                        {{ $targetUser->supervisorDetails->first_name }} {{ $targetUser->supervisorDetails->last_name }}
                                    @else
                                        System User
                                    @endif
                                </p>
                                <p class="text-[10px] text-gray-400 dark:text-gray-500 font-medium">{{ $targetUser->email }}</p>
                            @else
                                <p class="text-xs font-medium text-gray-400 dark:text-gray-500 italic">Deleted Account (UID: {{ $log->target_user_id }})</p>
                            @endif
                        </div>

                        <div>
                            <p class="text-[9px] text-gray-400 dark:text-gray-500 font-extrabold uppercase tracking-wider mb-1">Incident Context</p>
                            @if($targetLog)
                                <p class="text-xs font-bold text-gray-700 dark:text-gray-300">{{ $targetLog->action }}</p>
                                <p class="text-[10px] text-gray-400 dark:text-gray-500 font-bold uppercase">Score: {{ number_format($targetLog->anomaly_score, 2) }}</p>
                            @else
                                <p class="text-xs font-medium text-gray-400 dark:text-gray-500 italic">Context log not found</p>
                            @endif
                        </div>

                        <div class="pt-2 border-t border-gray-100 dark:border-gray-800 flex justify-between text-[9px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                            <p>IP: {{ $log->ip_address ?? 'N/A' }}</p>
                            <p>{{ $log->device ?? 'System' }}</p>
                        </div>
                    </div>

                    <div class="mt-3 flex justify-between items-center text-[9px] text-gray-300 dark:text-gray-600 font-bold uppercase tracking-wider">
                        <span>{{ $log->timestamp->format('m/d Y, H:i') }}</span>
                        <span>{{ $log->timestamp->diffForHumans() }}</span>
                    </div>
                </div>
                @empty
                    <x-empty-state title="No admin evaluations logged yet" compact="true" />
                @endforelse
            </div>
        </x-table-container>
    </div>

    {{-- High-End Contextual Evaluation Modal --}}
    <div x-show="showModal" 
         class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-[150] flex items-center justify-center" 
         style="display: none;"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
    >
        {{-- Backdrop --}}
        <div class="fixed inset-0 transform transition-all" @click="showModal = false">
            <div class="absolute inset-0 bg-gray-950/60 backdrop-blur-sm"></div>
        </div>

        {{-- Modal Box --}}
        <div class="bg-white dark:bg-gray-950 rounded-[35px] border border-gray-100 dark:border-gray-800/80 shadow-2xl transform transition-all w-full max-w-4xl sm:mx-auto overflow-hidden flex flex-col max-h-[85vh] relative z-10"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-4"
        >
            {{-- Modal Header --}}
            <div class="p-6 sm:p-8 border-b border-gray-50 dark:border-gray-900 flex justify-between items-start">
                <div>
                    <h3 class="text-lg sm:text-xl font-black text-gray-800 dark:text-gray-100 uppercase tracking-tight">Evaluate Flagged Account</h3>
                    <p class="text-xs text-gray-400 dark:text-gray-500 font-medium italic mt-1" x-text="`Reviewing user: ${evaluatingDisplayName} (${evaluatingUser?.email || ''})`"></p>
                </div>
                <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors p-2 rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-800">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Modal Content Split (Recent Log Trail + Action Panel) --}}
            <div class="flex-1 overflow-y-auto p-6 sm:p-8 grid grid-cols-1 lg:grid-cols-2 gap-8 custom-scrollbar">
                
                {{-- Left Column: User's Recent Log Trail --}}
                <div class="flex flex-col space-y-4 min-h-0">
                    <h4 class="text-xs font-black uppercase tracking-wider text-gray-400 dark:text-gray-500">Recent Login & Activity Trail</h4>
                    
                    <div class="flex-1 overflow-y-auto space-y-3 pr-2 max-h-[350px] lg:max-h-none custom-scrollbar">
                        <template x-for="log in (userLogsMap[evaluatingUser?.id] || [])" :key="log.log_id">
                            <div class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-2xl border border-gray-100 dark:border-gray-800/80 flex items-start justify-between gap-4">
                                <div class="space-y-1">
                                    <span class="text-[10px] font-black uppercase tracking-tight text-gray-800 dark:text-gray-200" x-text="log.action"></span>
                                    <p class="text-xs text-gray-400 dark:text-gray-500 font-medium italic line-clamp-2" x-text="log.resource"></p>
                                    <div class="flex flex-wrap gap-2 text-[9px] text-gray-300 dark:text-gray-600 font-bold uppercase tracking-wider pt-1">
                                        <span x-text="`IP: ${log.ip_address || 'N/A'}`"></span>
                                        <span>•</span>
                                        <span x-text="log.device"></span>
                                        <span>•</span>
                                        <span x-text="log.time_ago"></span>
                                    </div>
                                </div>
                                <span class="shrink-0 text-[10px] font-bold px-2 py-1 rounded-full uppercase border"
                                      :class="log.anomaly_score > 0.7 ? 'bg-rose-50 text-rose-500 border-rose-100 dark:bg-rose-950/20' : (log.anomaly_score > 0.5 ? 'bg-orange-50 text-orange-500 border-orange-100 dark:bg-orange-950/20' : 'bg-emerald-50 text-emerald-500 border-emerald-100 dark:bg-emerald-950/20')"
                                      x-text="parseFloat(log.anomaly_score).toFixed(2)"
                                ></span>
                            </div>
                        </template>

                        <div x-show="!(userLogsMap[evaluatingUser?.id] || []).length" class="text-center py-12">
                            <p class="text-xs text-gray-400 dark:text-gray-500 italic">No recent activity logs found for this user.</p>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Admin Action Submission Panel --}}
                <div class="flex flex-col space-y-6">
                    <div class="space-y-3 bg-indigo-50/50 dark:bg-indigo-950/10 p-5 rounded-2xl border border-indigo-100/30 dark:border-indigo-900/30">
                        <h4 class="text-xs font-black uppercase tracking-wider text-indigo-600 dark:text-indigo-400">Target Incident details</h4>
                        <div class="space-y-2 text-xs">
                            <p class="text-gray-500 dark:text-gray-400 font-medium"><strong class="text-gray-700 dark:text-gray-200">Flagged Action:</strong> <span x-text="evaluatingLog?.action"></span></p>
                            <p class="text-gray-500 dark:text-gray-400 font-medium"><strong class="text-gray-700 dark:text-gray-200">Incident Score:</strong> <span class="font-bold text-rose-500" x-text="parseFloat(evaluatingLog?.anomaly_score || 0).toFixed(3)"></span></p>
                            <p class="text-gray-500 dark:text-gray-400 font-medium"><strong class="text-gray-700 dark:text-gray-200">Details:</strong> <span class="italic" x-text="evaluatingLog?.resource"></span></p>
                            <p class="text-gray-500 dark:text-gray-400 font-medium"><strong class="text-gray-700 dark:text-gray-200">Time:</strong> <span x-text="evaluatingLog?.timestamp ? new Date(evaluatingLog.timestamp).toLocaleString() : ''"></span></p>
                        </div>
                    </div>

                    <form :action="getSubmitUrl()" method="POST" class="space-y-5 flex-1 flex flex-col justify-between">
                        @csrf
                        <div class="space-y-3">
                            <label for="evaluation_action" class="block text-xs font-black uppercase tracking-wider text-gray-400 dark:text-gray-500">Choose Assessment & Action</label>
                            
                            <select id="evaluation_action" x-model="selectedAction" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl px-5 py-3.5 text-xs font-bold text-gray-700 dark:text-gray-200 hover:border-indigo-500 focus:border-indigo-500 focus:ring-0 uppercase tracking-wider transition-all cursor-pointer">
                                <option value="safe">Mark Safe (False Positive - Restore Account)</option>
                                <option value="compromised">Mark Compromised (Reset Password & MFA - Force Relogin)</option>
                                <option value="suspended">Suspend Account (Indefinite block - Malicious Activity)</option>
                            </select>
                            
                            <p class="text-[10px] text-gray-400 dark:text-gray-500 leading-relaxed italic" x-show="selectedAction === 'safe'">
                                * This will clear the temporary block, whitelist this specific login pattern (IP, device, and browser signature), and update the machine learning baseline so this behavior is trusted in the future.
                            </p>
                            <p class="text-[10px] text-gray-400 dark:text-gray-500 leading-relaxed italic" x-show="selectedAction === 'compromised'">
                                * This will unblock the account but reset credentials. The user will be logged out globally, their MFA (Google 2FA) secret will be cleared, and they must complete a credentials reset flow upon their next login.
                            </p>
                            <p class="text-[10px] text-gray-400 dark:text-gray-500 leading-relaxed italic" x-show="selectedAction === 'suspended'">
                                * This will indefinitely suspend the user account. The user will be logged out globally, and any future authentication attempts will be blocked until an administrator manually restores access.
                            </p>
                        </div>

                        <div class="flex items-center gap-4 pt-6 border-t border-gray-50 dark:border-gray-900">
                            <button type="button" @click="showModal = false" class="flex-1 bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-400 border border-gray-200 dark:border-gray-800 py-3.5 px-6 rounded-2xl text-[10px] font-bold uppercase tracking-wider hover:bg-gray-100 transition active:scale-95 text-center">
                                Cancel
                            </button>
                            <button type="submit" class="flex-1 bg-indigo-600 dark:bg-indigo-500 text-white py-3.5 px-6 rounded-2xl text-[10px] font-bold uppercase tracking-wider hover:bg-indigo-700 dark:hover:bg-indigo-600 shadow-md transition active:scale-95 text-center">
                                Submit Assessment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
        </div>
    </div>
</x-page-layout>
@endsection
