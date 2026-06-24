@extends('layouts.app')

@section('title', 'Intern Registry')

@section('content')
<x-page-layout 
    portalType="Head Supervisor" 
    userName="{{ Auth::user()->supervisorDetails->first_name }}" 
    userRole="{{ Auth::user()->supervisorDetails->office->name }}"
>
    <div x-data="internDirectoryManager" class="flex flex-col h-full">
        {{-- Header --}}
        <x-page-header 
            title="Intern Registry" 
            subtitle="Monitor and manage active internship programs division-wide" 
        />

        {{-- Top Statistics Row --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 mb-10">
            <x-stat-card 
                title="Active Interns" 
                :value="number_format($activeCount)" 
                subtitle="Currently working"
                color="white"
                icon='<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>'
            />

            <x-stat-card 
                title="Completed" 
                :value="number_format($completedCount)" 
                subtitle="Finished internship"
                color="white"
                icon='<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>'
            >
                <x-slot name="value">
                    <span class="text-emerald-500">{{ number_format($completedCount) }}</span>
                </x-slot>
            </x-stat-card>

            <x-stat-card 
                title="Avg Progress" 
                :value="round($avgProgress) . '%'" 
                subtitle="Across all students"
                color="white"
                icon='<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M23 6l-9.5 9.5-5-5L1 18"/></svg>'
            >
                <x-slot name="value">
                    <span class="text-[#0B5694] dark:text-blue-400">{{ round($avgProgress) }}%</span>
                </x-slot>
            </x-stat-card>
        </div>

        {{-- Intern Overview Table --}}
        <x-table-container 
            title="Division Interns" 
            subtitle="Real-time tracking of student internship hours"
        >
            <x-slot name="headerAction">
                <div class="flex flex-col sm:flex-row items-center gap-3 sm:gap-4 w-full lg:w-auto">
                    {{-- Search --}}
                    <div class="relative w-full sm:w-64 group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-[#0B5694] transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        </div>
                        <input 
                            type="text" 
                            x-model="searchQuery"
                            placeholder="Search by name or email..." 
                            class="w-full pl-11 pr-4 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl text-xs font-bold text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-[#0B5694]/20 focus:border-[#0B5694] transition-all placeholder:text-gray-400 placeholder:font-semibold"
                        >
                    </div>

                    {{-- Status Filter --}}
                    <div class="relative w-full sm:w-40">
                        <select 
                            x-model="statusFilter"
                            class="w-full pl-4 pr-10 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl text-[10px] font-bold uppercase tracking-wider text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-[#0B5694]/20 focus:border-[#0B5694] transition-all appearance-none cursor-pointer"
                        >
                            <option value="all">All Status</option>
                            <option value="active">Active</option>
                            <option value="completed">Completed</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                        </div>
                    </div>

                    {{-- Department Filter --}}
                    <div class="relative w-full sm:w-48">
                        <select 
                            x-model="deptFilter"
                            class="w-full pl-4 pr-10 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl text-[10px] font-bold uppercase tracking-wider text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-[#0B5694]/20 focus:border-[#0B5694] transition-all appearance-none cursor-pointer"
                        >
                            <option value="all">All Departments</option>
                            @foreach($offices as $office)
                                <option value="{{ $office->name }}">{{ $office->name }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                        </div>
                    </div>
                </div>
            </x-slot>

            <div class="border border-gray-100 dark:border-gray-800 rounded-3xl overflow-hidden shadow-inner">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 dark:bg-gray-800/50 text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider border-b border-gray-100 dark:border-gray-800">
                            <tr>
                                <th class="px-8 py-5">Student Information</th>
                                <th class="px-8 py-5">Assigned Department</th>
                                <th class="px-8 py-5">Training Progress</th>
                                <th class="px-8 py-5">Status</th>
                                <th class="px-8 py-5 text-right">Action</th>
                            </tr>
                        </thead>
                        {{-- Skeleton Rows --}}
                        <tbody x-show="loading" class="divide-y divide-gray-50 dark:divide-gray-800">
                            @for($i = 0; $i < min(count($interns) ?: 5, 5); $i++)
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
                                    <td class="px-8 py-6"><x-skeleton class="h-4 w-40 rounded-lg" /></td>
                                    <td class="px-8 py-6"><x-skeleton class="h-6 w-20 rounded-full" /></td>
                                    <td class="px-8 py-6 text-right"><x-skeleton class="h-10 w-10 ml-auto rounded-xl" /></td>
                                </tr>
                            @endfor
                        </tbody>

                        <tbody x-show="!loading" x-transition.opacity.duration.500ms class="text-sm divide-y divide-gray-50 dark:divide-gray-800">
                            @forelse($interns as $intern)
                            @php 
                                $percentage = ($intern->required_hours ?? 480) > 0 ? min(100, ($intern->hours_rendered / ($intern->required_hours ?? 480)) * 100) : 0;
                                $status = optional($intern->certificate)->status === 'published' ? 'completed' : 'active';
                                $isPublished = $status === 'completed';
                                $renderedInt = round($intern->hours_rendered); 
                                $officeName = $intern->office->name ?? 'Unassigned';
                            @endphp
                            <tr 
                                x-show="matches('{{ $intern->first_name }} {{ $intern->last_name }}', '{{ $intern->user->email ?? '' }}', '{{ $officeName }}', '{{ $status }}')"
                                x-transition.opacity
                                class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition group"
                            >
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-4">
                                        <x-avatar :user="$intern" size="10" border="1" circular="true" textSize="xs" />
                                        <div>
                                            <p class="text-sm font-black text-gray-800 dark:text-gray-100 uppercase tracking-tight group-hover:text-[#0B5694] transition-colors">{{ $intern->first_name }} {{ $intern->last_name }}</p>
                                            <p class="text-[11px] text-gray-400 dark:text-gray-500 font-bold tracking-wider uppercase mt-0.5">{{ $intern->user->email ?? 'No email' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap">
                                    <span class="font-bold text-gray-600 dark:text-gray-400 uppercase tracking-wider text-[11px] bg-gray-50 dark:bg-gray-950 border border-gray-100 dark:border-gray-800 px-3 py-1.5 rounded-lg">
                                        {{ $officeName }}
                                    </span>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-4">
                                        <div class="w-32 h-2.5 bg-gray-100 dark:bg-gray-950 rounded-full overflow-hidden shadow-inner border border-gray-100 dark:border-gray-800 shrink-0">
                                            <div class="{{ $isPublished ? 'bg-emerald-500' : 'bg-[#0B5694] dark:bg-blue-600' }} h-full shadow-lg transition-all duration-1000" style="width: {{ $percentage }}%"></div>
                                        </div>
                                        <span class="font-bold text-gray-700 dark:text-gray-300 text-[11px] tracking-wider">{{ round($percentage) }}%</span>
                                    </div>
                                    <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-2 font-bold uppercase tracking-tighter">{{ $renderedInt }} / {{ $intern->required_hours ?? 480 }} HRS</p>
                                </td>
                                <td class="px-8 py-6">
                                    @if($isPublished)
                                        <span class="bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 text-[10px] font-bold px-4 py-1.5 rounded-full uppercase tracking-wider border border-emerald-100 dark:border-emerald-900/30">completed</span>
                                    @else
                                        <span class="bg-blue-50 dark:bg-blue-900/20 text-[#0B5694] dark:text-blue-400 text-[10px] font-bold px-4 py-1.5 rounded-full uppercase tracking-wider border border-blue-100 dark:border-blue-900/30">active</span>
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
                                <td colspan="5" class="py-20 text-center">
                                    <x-empty-state title="No student records registered in division." compact="true" />
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </x-table-container>

        {{-- Bottom Sections --}}
        <div class="mt-10">
            <x-card-container title="Department Distribution" subtitle="Intern allocation across division units" padding="p-6 sm:p-10">
                {{-- Skeletons --}}
                <div x-show="loading" class="{{ count($distribution) > 3 ? 'flex overflow-x-auto pb-4 gap-6 custom-scrollbar' : 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6' }}">
                    @for($i = 0; $i < 6; $i++)
                        <div class="{{ count($distribution) > 3 ? 'min-w-[280px] sm:min-w-[320px] shrink-0' : '' }} p-5 rounded-[24px] bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 flex items-center justify-between gap-4">
                            <div class="space-y-2.5 flex-1 min-w-0">
                                <x-skeleton class="h-4 w-28 rounded-lg" />
                                <x-skeleton class="h-3 w-20 rounded-lg" />
                            </div>
                            <x-skeleton class="h-8 w-8 rounded-xl shrink-0" />
                        </div>
                    @endfor
                </div>

                <div x-show="!loading" x-transition.opacity.duration.500ms class="{{ count($distribution) > 3 ? 'flex overflow-x-auto pb-4 gap-6 custom-scrollbar' : 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6' }}">
                    @foreach($distribution as $dept)
                    <div class="{{ count($distribution) > 3 ? 'min-w-[280px] sm:min-w-[320px] shrink-0' : '' }} group flex items-center justify-between p-5 rounded-[24px] bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 transition-all hover:bg-gray-50 dark:hover:bg-gray-800/50 hover:shadow-sm">
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
    </div>
</x-page-layout>
@endsection 
