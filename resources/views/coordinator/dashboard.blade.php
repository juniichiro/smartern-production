@extends('layouts.app')

@section('title', 'Dashboard')

@push('styles')
<style>
    @media print {
        aside, 
        .sidebar-wrapper, 
        .notification-sidebar, 
        .x-page-header-actions,
        button,
        nav,
        .bg-gray-50\/50,
        [x-slot="actions"] { 
            display: none !important; 
        }
        
        main { 
            width: 100% !important; 
            margin: 0 !important; 
            padding: 0 !important; 
        }

        .grid {
            display: block !important;
        }

        .grid > * {
            margin-bottom: 2rem !important;
            page-break-inside: avoid;
        }

        body {
            background: white !important;
            color: black !important;
        }

        .bg-white, .dark\:bg-gray-900 {
            background: white !important;
            border: 1px solid #e2e8f0 !important;
        }

        .text-gray-800, .dark\:text-gray-100 {
            color: black !important;
        }
    }
</style>
@endpush

@section('content')
<x-page-layout 
    portalType="School Coordinator" 
    userName="{{ Auth::user()->schoolCoordinatorDetails->first_name }}" 
    userRole="School Coordinator"
    fullHeight="true"
>
    {{-- Header --}}
    <x-page-header 
        title="Dashboard" 
        subtitle="Student Monitoring — {{ Auth::user()->schoolCoordinatorDetails->school }}" 
    >
        <x-slot name="actions">
            <a href="{{ route('coordinator.interns.export') }}" class="flex items-center gap-2 px-6 py-3 bg-[#0B5694] text-white rounded-2xl text-[10px] font-bold hover:bg-blue-700 transition-all shadow-lg shadow-blue-900/20 uppercase tracking-wider active:scale-95">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                Download Registry
            </a>
        </x-slot>
    </x-page-header>

    {{-- Delegation Milestones Grid --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 shrink-0 mb-10">
        {{-- Total Delegation --}}
        <x-stat-card 
            title="Total Students" 
            value="{{ $stats['total_interns'] }}" 
            subtitle="ENROLLED STUDENTS"
            color="white"
            class="hover:border-[#0B5694] dark:hover:border-blue-500/50 transition-all shadow-sm"
            icon='<svg xmlns="http://www.w3.org/2000/svg" class="text-[#0B5694] dark:text-blue-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>'
        />

        {{-- Office Deployments --}}
        <x-stat-card 
            title="Placement Rate" 
            value="{{ $stats['total_interns'] > 0 ? round(($stats['placed_interns'] / $stats['total_interns']) * 100) : 0 }}%" 
            subtitle="{{ $stats['placed_interns'] }} ASSIGNED"
            color="white"
            class="hover:border-[#0B5694] dark:hover:border-blue-500/50 transition-all shadow-sm"
            icon='<svg xmlns="http://www.w3.org/2000/svg" class="text-[#0B5694] dark:text-blue-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 20a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8l-7-5L4 8v12Z"/><path d="M9 22V12h6v10"/></svg>'
        />

        {{-- Accumulated Progress --}}
        <x-stat-card 
            title="Overall Progress" 
            value="{{ $milestones['average_progress'] }}%" 
            subtitle="AVERAGE RENDERED"
            color="white"
            class="hover:border-emerald-200 dark:hover:border-emerald-500/30 transition-all shadow-sm"
            icon='<svg xmlns="http://www.w3.org/2000/svg" class="text-emerald-500 dark:text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>'
        />

        {{-- Training Completed --}}
        <x-stat-card 
            title="Certified Students" 
            value="{{ $stats['completed'] }}" 
            subtitle="TRAINING COMPLETED"
            color="white"
            class="hover:border-emerald-200 dark:hover:border-emerald-500/30 transition-all shadow-sm"
            icon='<svg xmlns="http://www.w3.org/2000/svg" class="text-emerald-500 dark:text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>'
        />
    </div>

    {{-- DASHBOARD MAIN LAYOUT --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 flex-1 min-h-0 mb-6">

        {{-- LEFT COLUMN: Delegation Registry --}}
        <div class="lg:col-span-8 flex flex-col min-h-0">
            {{-- Delegation Registry Card --}}
            <x-card-container 
                class="flex-1 flex flex-col min-h-0"
                padding="p-0"
                title="Student Registry"
            >
                <x-slot name="headerAction">
                    <a href="{{ route('coordinator.interns') }}" class="text-[10px] font-bold text-gray-500 dark:text-slate-400 uppercase hover:text-[#0B5694] dark:hover:text-blue-400 transition-colors tracking-wider bg-gray-50 dark:bg-gray-800/50 px-4 py-2 rounded-full border border-gray-100 dark:border-gray-800">
                        View Full Registry
                    </a>
                </x-slot>

                <div class="w-full h-full flex flex-col min-h-0">
                    {{-- Scrollable table list --}}
                    <div class="flex-1 overflow-y-auto custom-scrollbar">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-gray-50/80 dark:bg-gray-800/50 sticky top-0 z-10 border-b border-gray-100 dark:border-gray-800 backdrop-blur-md">
                                <tr class="text-[10px] font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wider">
                                    <th class="py-6 px-8">Student Details</th>
                                    <th class="py-6 px-4 text-center">Office</th>
                                    <th class="py-6 px-4">Progress</th>
                                    <th class="py-5 px-8 text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 dark:divide-gray-850/50">
                                @forelse($interns as $intern)
                                    @php
                                        $goal = $intern->required_hours ?? 480;
                                        $rendered = round($intern->hours_rendered ?? 0);
                                        $progress = $goal > 0 ? min(100, round(($rendered / $goal) * 100)) : 0;
                                        $isCertified = optional($intern->certificate)->status === 'published';
                                    @endphp
                                    <tr onclick="window.location.href='{{ route('coordinator.interns.show', $intern->user) }}'"
                                        class="hover:bg-blue-50/20 dark:hover:bg-blue-900/5 transition-all group cursor-pointer">
                                        <td class="py-5 px-8">
                                            <div class="flex items-center gap-4">
                                                <x-avatar :user="$intern" size="10" border="0" circular="true" textSize="xs" />
                                                <div class="min-w-0">
                                                    <p class="text-[14px] font-extrabold text-gray-800 dark:text-gray-100 uppercase tracking-normal truncate group-hover:text-[#0B5694] dark:group-hover:text-blue-400 transition-colors">
                                                        {{ $intern->first_name }} {{ $intern->last_name }}
                                                    </p>
                                                    <p class="text-[10px] text-gray-500 dark:text-slate-400 font-bold uppercase tracking-wider mt-0.5">{{ $intern->student_id }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-5 px-4 text-center">
                                            <span class="text-gray-500 dark:text-slate-400 text-[10px] font-bold uppercase tracking-wider truncate max-w-[150px] inline-block" title="{{ $intern->office->name ?? 'Unassigned' }}">
                                                {{ $intern->office->name ?? 'Unassigned' }}
                                            </span>
                                        </td>
                                        <td class="py-5 px-4 min-w-[160px]">
                                            <div class="flex flex-col gap-1.5">
                                                <div class="flex justify-between items-center text-[10px] font-bold uppercase tracking-normal">
                                                    <span class="text-gray-500 dark:text-slate-400">{{ $rendered }} / {{ $goal }}H</span>
                                                    <span class="text-[#0B5694] dark:text-blue-400 font-bold">{{ $progress }}%</span>
                                                </div>
                                                <div class="w-full h-1.5 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                                                    <div class="h-full rounded-full transition-all duration-700 {{ $isCertified ? 'bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.3)]' : 'bg-[#0B5694] dark:bg-blue-500' }}" 
                                                         style="width: {{ $progress }}%"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-5 px-8 text-right">
                                            @if($isCertified)
                                                <span class="text-emerald-500 dark:text-emerald-400 text-[10px] font-bold uppercase tracking-wider flex items-center justify-end gap-1.5">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                                    Certified
                                                </span>
                                            @elseif($rendered >= $goal)
                                                <span class="text-orange-500 dark:text-orange-400 text-[10px] font-bold uppercase tracking-wider animate-pulse">Endorsing</span>
                                            @else
                                                <span class="text-[#0B5694] dark:text-blue-400 text-[10px] font-bold uppercase tracking-wider">Active</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-20 text-center">
                                            <x-empty-state title="No student data detected in current delegation." compact="true" />
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </x-card-container>
        </div>

        {{-- RIGHT COLUMN: Office Deployments --}}
        <div class="lg:col-span-4 flex flex-col min-h-0">
            {{-- Office Deployments Card --}}
            <x-card-container 
                class="flex-1 flex flex-col min-h-0"
                padding="p-8"
                title="Students per Office"
            >
                <x-slot name="headerAction">
                    <span class="text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider bg-gray-50 dark:bg-gray-800/50 px-3 py-1.5 rounded-full border border-gray-100 dark:border-gray-800">SDO Divisions</span>
                </x-slot>

                <div class="flex-1 overflow-y-auto custom-scrollbar space-y-3 mt-4 pr-1">
                    @forelse($placements as $office)
                        <div class="group flex items-center justify-between p-5 rounded-[24px] bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 transition-all hover:bg-gray-50 dark:hover:bg-gray-800/50 hover:shadow-sm">
                            <div class="min-w-0">
                                <p class="text-[13px] font-extrabold text-gray-800 dark:text-gray-100 uppercase truncate tracking-normal group-hover:text-[#0B5694] dark:group-hover:text-blue-400 transition-colors inline-block max-w-[160px]" title="{{ $office->name }}">
                                    {{ $office->name }}
                                </p>
                                <p class="text-[10px] text-gray-400 dark:text-slate-500 font-bold uppercase tracking-wider mt-1">
                                    Assigned Students
                                </p>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-normal leading-none group-hover:text-[#0B5694] dark:group-hover:text-blue-400 transition-colors">
                                    {{ $office->intern_details_count }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <div class="py-12">
                            <x-empty-state title="No divisional data mapped." compact="true" />
                        </div>
                    @endforelse
                </div>
            </x-card-container>
        </div>
    </div>
</x-page-layout>
@endsection
