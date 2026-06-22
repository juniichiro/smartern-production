@extends('layouts.app')

@section('title', 'Security Monitor')

@section('content')
<x-page-layout 
    portalType="Admin" 
    userName="{{ Auth::user()->email }}" 
    userRole="Admin"
>
    <div x-data="securityMonitorManager()" class="flex flex-col flex-1">
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
            $logSkeletonCount = $securityLogs->isEmpty() ? 1 : min($securityLogs->count(), 10);
        @endphp

        <div x-show="loading" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="flex flex-col flex-1">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-10">
                <x-skeleton type="metric" />
                <x-skeleton type="metric" />
                <x-skeleton type="metric" />
            </div>
            <x-table-container title="Neural Audit Registry" subtitle="Hydrating telemetry streams...">
                <x-skeleton type="list" :rows="$logSkeletonCount" />
            </x-table-container>
        </div>

        {{-- ACTUAL CONTENT VIEW --}}
        <div x-show="!loading" x-cloak x-transition:enter="transition ease-out duration-500 delay-200" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="flex flex-col flex-1">
            {{-- Summary Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-10">
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
                        class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl px-6 py-3.5 text-[11px] font-bold text-gray-700 dark:text-gray-200 flex items-center justify-between hover:border-[#0B5694] transition-all uppercase tracking-wider shadow-sm">
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
                                class="w-full text-left px-5 py-3 text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-[#0B5694] dark:hover:text-blue-400 rounded-xl transition-colors">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>
        </x-slot>

        {{-- Desktop Table --}}
        <div class="hidden lg:block border border-gray-100 dark:border-gray-800 rounded-3xl overflow-hidden shadow-inner">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 dark:bg-gray-800/50 text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider border-b border-gray-100 dark:border-gray-800">
                        <tr>
                            <th class="px-8 py-5">Activity Type</th>
                            <th class="px-8 py-5">User Account</th>
                            <th class="px-8 py-5">Incident Details</th>
                            <th class="px-8 py-5">Anomaly Analysis</th>
                            <th class="px-8 py-5">Device</th>
                            <th class="px-8 py-5 text-center">Status</th>
                            <th class="px-8 py-5 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                        @forelse($securityLogs as $log)
                        @php
                            $score = $log->anomaly_score;
                            $color = $score > 0.7 ? 'rose' : ($score > 0.5 ? 'orange' : 'emerald');
                        @endphp
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors group">
                            <td class="px-8 py-6">
                                <span class="font-black text-gray-800 dark:text-gray-200 text-sm uppercase tracking-tight">{{ $log->action }}</span>
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-3">
                                    <x-avatar :user="$log->internDetails ?? $log->supervisorDetails ?? $log->user" size="8" border="1" circular="true" textSize="xs" />
                                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400">
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
                            <td class="px-8 py-6">
                                <p class="text-xs text-gray-400 dark:text-gray-500 font-medium italic leading-relaxed line-clamp-1">{{ $log->resource }}</p>
                                <span class="text-[9px] text-gray-300 dark:text-gray-600 font-bold uppercase tracking-wider mt-1 block">IP: {{ $log->ip_address ?? 'Unknown' }} • {{ $log->timestamp ? $log->timestamp->format('m/d, H:i') : 'N/A' }}</span>
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-32 h-2 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden shadow-inner shrink-0 border border-gray-50 dark:border-gray-700">
                                        <div class="bg-{{ $color }}-500 h-full shadow-lg transition-all duration-1000" style="width: {{ $score * 100 }}%"></div>
                                    </div>
                                    <span class="text-[11px] font-bold text-gray-700 dark:text-gray-300 tracking-tighter">{{ number_format($score, 2) }}</span>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <span class="text-xs text-gray-400 dark:text-gray-500 font-medium leading-relaxed line-clamp-1 max-w-[200px]" title="{{ $log->user_agent ?? 'Direct System Access' }}">
                                    {{ str_replace('|', ' • ', $log->device ?? 'System') }}
                                </span>
                            </td>
                            <td class="px-8 py-6 text-center">
                                <span class="px-4 py-1.5 rounded-full text-[9px] font-bold uppercase tracking-wider border {{ $score > 0.7 ? 'bg-rose-50 text-rose-500 border-rose-100' : ($score > 0.5 ? 'bg-orange-50 text-orange-500 border-orange-100' : 'bg-emerald-50 text-emerald-500 border-emerald-100') }} dark:bg-opacity-10">
                                    {{ $score > 0.7 ? 'Critical' : ($score > 0.5 ? 'Warning' : 'Normal') }}
                                </span>
                            </td>
                            <td class="px-8 py-6 text-right">
                                @if($log->user && $log->user->role_id !== \App\Enums\UserRole::ADMIN && $log->anomaly_score > 0.7 && $log->user->locked_until && now()->lessThan($log->user->locked_until))
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
                                        <div x-data="{ open: false }" class="relative inline-block text-left">
                                            <button @click="open = !open" @click.away="open = false" type="button" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 rounded-lg text-[10px] font-bold uppercase tracking-wider hover:bg-indigo-100 dark:hover:bg-indigo-900/40 border border-indigo-100 dark:border-indigo-800 transition">
                                                Evaluate
                                                <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </button>
                                            <div x-show="open" 
                                                 x-transition:enter="transition ease-out duration-100" 
                                                 x-transition:enter-start="transform opacity-0 scale-95" 
                                                 x-transition:enter-end="transform opacity-100 scale-100" 
                                                 x-transition:leave="transition ease-in duration-75" 
                                                 x-transition:leave-start="transform opacity-100 scale-100" 
                                                 x-transition:leave-end="transform opacity-0 scale-95" 
                                                 class="absolute right-0 mt-2 w-56 rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-xl z-50 py-2 divide-y divide-gray-50 dark:divide-gray-700/50" 
                                                 style="display: none;"
                                            >
                                                <div class="px-4 py-2.5 text-left">
                                                    <p class="text-[9px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500">Security Assessment</p>
                                                </div>
                                                <div class="py-1">
                                                    <form method="POST" action="{{ route('admin.security.markSafe', [$log->user, $log]) }}" class="block">
                                                        @csrf
                                                        <button type="submit" class="w-full text-left px-4 py-2 text-[10px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/30 transition flex items-center gap-2">
                                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                            Mark Safe
                                                        </button>
                                                    </form>
                                                    <form method="POST" action="{{ route('admin.security.markCompromised', [$log->user, $log]) }}" class="block">
                                                        @csrf
                                                        <button type="submit" class="w-full text-left px-4 py-2 text-[10px] font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/30 transition flex items-center gap-2">
                                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                            Mark Compromised
                                                        </button>
                                                    </form>
                                                    <form method="POST" action="{{ route('admin.security.markSuspended', [$log->user, $log]) }}" class="block">
                                                        @csrf
                                                        <button type="submit" class="w-full text-left px-4 py-2 text-[10px] font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition flex items-center gap-2">
                                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                            Suspend Account
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
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
        <div class="lg:hidden space-y-6">
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
                    <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-800 flex justify-between items-center text-[9px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                        <p class="line-clamp-1" title="{{ $log->user_agent }}">Device: {{ str_replace('|', ' • ', $log->device ?? 'System') }}</p>
                        <p class="line-clamp-1 shrink-0 ml-4" title="{{ $log->user_agent }}">UA: {{ $log->user_agent ?? 'System' }}</p>
                    </div>
                    @if($log->user && $log->user->role_id !== \App\Enums\UserRole::ADMIN && $log->anomaly_score > 0.7 && $log->user->locked_until && now()->lessThan($log->user->locked_until))
                        <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800 space-y-2">
                            @if($log->user->locked_until && now()->lessThan($log->user->locked_until))
                                <div class="text-center mb-2">
                                    <span class="inline-block text-[9px] font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-900/20 px-2 py-0.5 rounded-md border border-rose-100 dark:border-rose-800/50">
                                        Status: @if(\Carbon\Carbon::parse($log->user->locked_until)->year > 9000) Suspended @else Locked (Temp) @endif
                                    </span>
                                </div>
                            @endif
                            <div class="grid grid-cols-3 gap-2">
                                <form method="POST" action="{{ route('admin.security.markSafe', [$log->user, $log]) }}" class="w-full">
                                    @csrf
                                    <button type="submit" class="w-full py-2 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 rounded-lg text-[9px] font-bold uppercase tracking-wider hover:bg-emerald-100 dark:hover:bg-emerald-900/40 border border-emerald-100 dark:border-emerald-800 transition text-center">Safe</button>
                                </form>
                                <form method="POST" action="{{ route('admin.security.markCompromised', [$log->user, $log]) }}" class="w-full">
                                    @csrf
                                    <button type="submit" class="w-full py-2 bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 rounded-lg text-[9px] font-bold uppercase tracking-wider hover:bg-amber-100 dark:hover:bg-amber-900/40 border border-amber-100 dark:border-amber-800 transition text-center">Reset</button>
                                </form>
                                <form method="POST" action="{{ route('admin.security.markSuspended', [$log->user, $log]) }}" class="w-full">
                                    @csrf
                                    <button type="submit" class="w-full py-2 bg-rose-50 dark:bg-rose-900/20 text-rose-600 dark:text-rose-400 rounded-lg text-[9px] font-bold uppercase tracking-wider hover:bg-rose-100 dark:hover:bg-rose-900/40 border border-rose-100 dark:border-rose-800 transition text-center">Suspend</button>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            @empty
                <x-empty-state title="No security events recorded" compact="true" />
            @endforelse
        </div>

        <div class="mt-10 pt-10 border-t border-gray-100 dark:border-gray-800 flex flex-col sm:flex-row justify-between items-center gap-8">
            <div class="text-left w-full sm:w-auto">
                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider italic">Event Registry Analysis</p>
                <div class="mt-4">
                    {{ $securityLogs->links('vendor.pagination.tailwind-custom') }}
                </div>
            </div>
        </div>
    </x-table-container>
        </div>
    </div>
</x-page-layout>
@endsection
