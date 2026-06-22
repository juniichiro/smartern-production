@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<x-page-layout portalType="Admin" userName="{{ Auth::user()->email }}" userRole="Admin">
    <div x-data="adminDashboardManager()" class="flex flex-col flex-1">
        <x-page-header title="Dashboard" subtitle="System Metrics & Administrative Oversight" />

        {{-- SKELETON LOADING VIEW --}}
        @php
            $newUserCount = $newUsers->isEmpty() ? 1 : min($newUsers->count(), 5);
            $anomalyCount = $highRiskAnomalies->isEmpty() ? 1 : min($highRiskAnomalies->count(), 5);
        @endphp

            <div x-show="loading" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="flex flex-col flex-1">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 mb-6 lg:mb-10">
                <x-card-container class="lg:col-span-8 h-48 lg:h-64" padding="p-6 lg:p-10">
                    <x-skeleton type="text" class="w-1/3 lg:w-1/4 mb-6 lg:mb-10" />
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 lg:gap-8">
                        <x-skeleton type="text" rows="2" />
                        <x-skeleton type="text" rows="2" />
                        <x-skeleton type="text" rows="2" />
                    </div>
                </x-card-container>
                <x-skeleton type="metric" class="lg:col-span-4 h-48 lg:h-64" />
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 mb-6 lg:mb-10">
                <x-card-container class="lg:col-span-5 h-72 lg:h-[400px]" padding="p-6 lg:p-10">
                    <x-skeleton type="text" class="w-1/3 lg:w-1/3 mb-6 lg:mb-10" />
                    <x-skeleton type="list" :rows="min($newUserCount, 3)" />
                </x-card-container>
                <x-card-container class="lg:col-span-7 h-72 lg:h-[400px]" padding="p-6 lg:p-10">
                    <x-skeleton type="text" class="w-1/3 lg:w-1/3 mb-6 lg:mb-10" />
                    <x-skeleton type="list" :rows="min($anomalyCount, 3)" />
                </x-card-container>
            </div>
        </div>

        {{-- ACTUAL CONTENT VIEW --}}
        <div x-show="!loading" x-cloak x-transition:enter="transition ease-out duration-500 delay-200" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="flex flex-col flex-1">
            {{-- TOP ROW: SECURITY & HEALTH --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 mb-6 lg:mb-10">
        {{-- SYSTEM HEALTH LIST --}}
        <x-card-container class="lg:col-span-8 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-emerald-50/20 dark:bg-emerald-900/5 rounded-bl-full -z-0"></div>
            
            <div class="relative z-10">
                <div class="flex justify-between items-center mb-10">
                    <h4 class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">System Services</h4>
                    <span class="px-4 py-1.5 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 text-[10px] font-bold uppercase rounded-full border border-emerald-100 dark:border-emerald-800 tracking-wider animate-pulse">Operational Status</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 lg:gap-8">
                    @foreach($services as $service)
                    @php
                        $dotColor = match($service['color']) {
                            'green' => 'bg-emerald-500',
                            'rose' => 'bg-rose-500',
                            'orange' => 'bg-orange-500',
                            default => 'bg-gray-500'
                        };
                    @endphp
                    <div class="flex flex-col">
                        <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-3">{{ $service['name'] }}</span>
                        <div class="flex items-center gap-3">
                            <div class="w-2.5 h-2.5 rounded-full {{ $dotColor }} shadow-sm"></div>
                            <span class="text-[13px] sm:text-base font-extrabold text-gray-800 dark:text-gray-100 uppercase tracking-normal">{{ $service['status'] }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </x-card-container>

        {{-- ANOMALY COUNTER --}}
        <x-stat-card 
            class="lg:col-span-4"
            title="Security Alerts" 
            :value="$flaggedLogins" 
            subtitle="Flagged Security Anomalies"
            color="blue"
            :link="route('admin.security')"
            linkText="Run Diagnostics"
            animate="true"
            icon='<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>'
        />
    </div>

    {{-- MIDDLE ROW: QUICK ACTIONS & USERS --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 mb-6 lg:mb-10">
        {{-- RECENT USERS --}}
        <x-card-container 
            class="lg:col-span-5"
            title="Recent User Accounts"
            :headerActionLink="route('admin.account')"
            headerActionText="Manage Accounts"
        >
            <div class="space-y-6">
                @foreach($newUsers as $user)
                <div class="flex items-center gap-4 group">
                    <x-avatar :user="$user" size="10" border="1" circular="true" textSize="xs" />
                    <div class="flex-1 min-w-0 text-left">
                        <p class="text-[15px] font-bold text-gray-800 dark:text-gray-200 truncate">{{ $user->email }}</p>
                        <p class="text-[11px] text-gray-400 font-bold uppercase tracking-wider mt-0.5 italic">{{ $user->role->name }} • Joined {{ $user->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </x-card-container>

        {{-- HIGH RISK ANOMALIES --}}
        <x-card-container 
            class="lg:col-span-7 group hover:border-rose-100"
            title="High-Risk Security Logs"
        >
            <div class="space-y-8">
                @forelse($highRiskAnomalies as $anomaly)
                <div class="flex items-start gap-5 p-5 rounded-3xl bg-rose-50/30 dark:bg-rose-900/5 border border-rose-100/50 dark:border-rose-900/20 group/item hover:border-rose-200 transition-all">
                    <div class="w-12 h-12 bg-rose-500 rounded-2xl flex items-center justify-center text-white shrink-0 shadow-lg shadow-rose-500/20">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    </div>
                    <div class="flex-1 min-w-0 text-left">
                        <p class="text-[15px] font-extrabold text-gray-800 dark:text-gray-100 uppercase tracking-normal">{{ $anomaly->action }}</p>
                        <p class="text-[12px] text-rose-600 dark:text-rose-400 font-bold uppercase mt-1 italic">
                            Risk Score: {{ number_format($anomaly->anomaly_score, 2) }} • {{ $anomaly->timestamp->diffForHumans() }}
                        </p>
                    </div>
                    <a href="{{ route('admin.security') }}" class="px-5 min-h-[44px] inline-flex items-center bg-rose-500 text-white rounded-xl text-[10px] font-bold uppercase tracking-wider hover:scale-105 active:scale-95 transition-all shadow-lg shadow-rose-500/20 cursor-pointer">Inspect</a>
                </div>
                @empty
                    <x-empty-state 
                        title="No high-risk anomalies detected" 
                        compact="true"
                    />
                @endforelse
            </div>
        </x-card-container>
    </div>

    {{-- BOTTOM ROW: GLOBAL STATS --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <x-stat-card 
            title="Total Users" 
            :value="number_format($totalUsers)" 
            subtitle="Provisioned Accounts" 
        />
        <x-stat-card 
            title="Certificates" 
            :value="number_format($totalCertificates)" 
            subtitle="Total Generated"
        >
            <x-slot name="value">
                <span class="text-emerald-500">{{ number_format($totalCertificates) }}</span>
            </x-slot>
        </x-stat-card>
        <x-stat-card 
            title="Active Offices" 
            :value="number_format($totalDepartments)" 
            subtitle="Operational Units" 
        />
        <x-stat-card 
            title="Backup Snapshots" 
            :value="$totalBackups" 
            subtitle="Stored Locally" 
        />
        </div>
    </div>
</x-page-layout>
@endsection
