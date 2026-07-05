@extends('layouts.app')

@section('title', 'Office Management')

@section('content')
<x-page-layout 
    portalType="Supervisor" 
    userName="{{ Auth::user()->supervisorDetails->first_name }}" 
    userRole="{{ Auth::user()->supervisorDetails->office->name }}"
>
    <div x-data="{ activeTab: 'interns', loading: true }" x-init="setTimeout(() => loading = false, 800)" class="flex flex-col h-full">
        {{-- Header --}}
        <x-page-header 
            title="Office Management" 
            subtitle="Manage assigned interns and office limits/requirements" 
        >
            <x-slot:actions>
                <nav class="flex items-center gap-1 p-1 bg-gray-50/50 dark:bg-gray-800/50 rounded-2xl w-full border border-gray-100 dark:border-gray-800">
                    <button @click="activeTab = 'interns'" 
                            :class="activeTab === 'interns' ? 'bg-white dark:bg-gray-700 text-[#0B5694] dark:text-blue-400 shadow-sm border-gray-100 dark:border-gray-600' : 'text-gray-400 dark:text-slate-500 border-transparent hover:text-gray-600 dark:hover:text-gray-300'" 
                            class="flex-1 min-h-[44px] px-4 sm:px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest border transition-all whitespace-nowrap active:scale-95 focus-visible:ring-2 focus-visible:ring-[#0B5694] dark:focus-visible:ring-blue-500 focus-visible:ring-offset-2 focus-visible:outline-none">
                        <span class="sm:hidden">Intern</span>
                        <span class="hidden sm:inline">Intern Directory</span>
                    </button>
                    <button @click="activeTab = 'requirements'" 
                            :class="activeTab === 'requirements' ? 'bg-white dark:bg-gray-700 text-[#0B5694] dark:text-blue-400 shadow-sm border-gray-100 dark:border-gray-600' : 'text-gray-400 dark:text-slate-500 border-transparent hover:text-gray-600 dark:hover:text-gray-300'" 
                            class="flex-1 min-h-[44px] px-4 sm:px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest border transition-all whitespace-nowrap active:scale-95 focus-visible:ring-2 focus-visible:ring-[#0B5694] dark:focus-visible:ring-blue-500 focus-visible:ring-offset-2 focus-visible:outline-none">
                        <span class="sm:hidden">Office</span>
                        <span class="hidden sm:inline">Office Requirements</span>
                    </button>
                </nav>
            </x-slot:actions>
        </x-page-header>

        {{-- Intern Directory Tab --}}
        <div x-show="activeTab === 'interns'" class="space-y-6 animate-in fade-in duration-300">
            {{-- Search Bar --}}
            <x-card-container class="mb-6 md:mb-10" padding="p-4 md:p-6">
                <form action="{{ route('supervisor.interns') }}" method="GET" class="w-full md:w-2/3 lg:w-1/2">
                    <div class="relative group">
                        <button type="submit" class="absolute inset-y-0 left-0 pl-3 md:pl-4 flex items-center text-gray-400 group-focus-within:text-[#0B5694] focus:outline-none transition-colors z-10" aria-label="Search interns">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        </button>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search intern name or ID..." 
                            @input.debounce.800ms="$el.form.submit()"
                            class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl pl-10 md:pl-12 pr-4 md:pr-6 py-3 md:py-4 text-sm focus:ring-2 focus:ring-[#0B5694] dark:focus:ring-blue-600 focus:outline-none transition-all placeholder-gray-400 dark:placeholder-gray-500 font-medium dark:text-gray-100">
                    </div>
                </form>
            </x-card-container>

            {{-- Interns List --}}
            <x-table-container 
                title="Assigned Interns" 
                subtitle="Total: {{ $totalInterns }} currently deployed"
            >
                {{-- Mobile & Tablet: Card Grid --}}
                <div class="lg:hidden space-y-4">
                    {{-- Skeleton Cards --}}
                    <div x-show="loading" class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 motion-reduce:transition-none">
                        @for($i = 0; $i < min(count($interns) ?: 4, 4); $i++)
                            <div class="bg-white dark:bg-gray-900 rounded-2xl sm:rounded-[30px] border border-gray-100 dark:border-gray-800 p-4 sm:p-5 space-y-4">
                                <div class="flex items-start gap-3">
                                    <x-skeleton class="w-12 h-12 rounded-full shrink-0" />
                                    <div class="flex-1 space-y-2 pt-1">
                                        <x-skeleton class="h-4 w-3/4 rounded-lg" />
                                        <x-skeleton class="h-3 w-1/2 rounded-lg" />
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <x-skeleton class="h-3 w-full rounded-lg" />
                                    <x-skeleton class="h-2.5 w-2/3 rounded-lg" />
                                </div>
                                <div class="flex items-center justify-end gap-2 pt-2">
                                    <x-skeleton class="h-11 w-11 rounded-xl" />
                                    <x-skeleton class="h-11 w-11 rounded-xl" />
                                </div>
                            </div>
                        @endfor
                    </div>

                    {{-- Actual Cards --}}
                    <div x-show="!loading" x-transition.opacity.duration.500ms class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 motion-reduce:transition-none">
                        @forelse($interns as $intern)
                            @php
                                $isCompleted = optional($intern->certificate)->status === 'published';
                                $targetHours = $intern->required_hours ?? 480;
                                $percentage = $targetHours > 0 ? min(100, round(($intern->hours_rendered / $targetHours) * 100)) : 0;
                            @endphp
                            <div class="bg-white dark:bg-gray-900 rounded-2xl sm:rounded-[30px] border border-gray-100 dark:border-gray-800 p-4 sm:p-5 shadow-sm flex flex-col gap-4 motion-reduce:transition-none">
                                {{-- Header: Avatar, Name, Status --}}
                                <div class="flex items-start gap-3 sm:gap-4">
                                    <x-avatar :user="$intern" size="12" border="1" circular="true" textSize="sm" />
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-start justify-between gap-2">
                                            <a href="{{ route('supervisor.interns.show', $intern->user) }}" class="font-black text-sm sm:text-base text-gray-800 dark:text-gray-100 uppercase tracking-tight leading-tight hover:text-[#0B5694] dark:hover:text-blue-400 transition-colors truncate block">
                                                {{ $intern->first_name }} {{ $intern->last_name }}
                                            </a>
                                            @if($isCompleted)
                                                <span class="shrink-0 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-widest border border-emerald-100 dark:border-emerald-900/30">
                                                    Completed
                                                </span>
                                            @else
                                                <span class="shrink-0 bg-blue-50 dark:bg-blue-900/20 text-[#0B5694] dark:text-blue-400 text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-widest border border-blue-100 dark:border-blue-900/30">
                                                    Active
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 font-bold uppercase tracking-wider mt-0.5 truncate">{{ $intern->user->email ?? 'No Email' }}</p>
                                        <p class="text-[11px] text-gray-500 dark:text-gray-400 font-black uppercase tracking-[0.2em] mt-1">{{ $intern->student_id ?? 'N/A' }}</p>
                                    </div>
                                </div>

                                {{-- Progress --}}
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[10px] sm:text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Progress</span>
                                        <span class="font-black text-gray-700 dark:text-gray-300 text-[11px] sm:text-xs tracking-widest">{{ $percentage }}%</span>
                                    </div>
                                    <div class="w-full h-2.5 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden shadow-inner border border-gray-50 dark:border-gray-700">
                                        <div class="{{ $isCompleted ? 'bg-emerald-500 dark:bg-emerald-400' : 'bg-[#0B5694] dark:bg-blue-600' }} h-full shadow-lg transition-all duration-1000 motion-reduce:transition-none" style="width: {{ $percentage }}%"></div>
                                    </div>
                                    <p class="text-[10px] sm:text-xs text-gray-400 dark:text-gray-500 font-medium">{{ round($intern->hours_rendered) }} / {{ $targetHours }} hrs</p>
                                </div>

                                {{-- Actions --}}
                                <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-50 dark:border-gray-800/60">
                                    @if(!$isCompleted)
                                        <a href="{{ route('supervisor.tasks') }}#assign-{{ $intern->user->getRouteKey() }}" title="Assign Task" class="min-h-[44px] min-w-[44px] flex items-center justify-center p-2.5 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 text-gray-400 dark:text-gray-500 hover:text-[#0B5694] dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-xl transition shadow-sm transform hover:scale-110 focus-visible:ring-2 focus-visible:ring-[#0B5694] dark:focus-visible:ring-blue-500 focus-visible:ring-offset-2 focus-visible:outline-none motion-reduce:transform-none">
                                            <span class="sr-only">Assign Task</span>
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                                        </a>
                                    @endif
                                    <a href="{{ route('supervisor.interns.show', $intern->user) }}" class="min-h-[44px] min-w-[44px] flex items-center justify-center p-2.5 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 text-gray-400 dark:text-gray-500 hover:text-[#0B5694] dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-xl transition shadow-sm transform hover:scale-110 focus-visible:ring-2 focus-visible:ring-[#0B5694] dark:focus-visible:ring-blue-500 focus-visible:ring-offset-2 focus-visible:outline-none motion-reduce:transform-none" title="Refine intern details">
                                        <span class="sr-only">View intern details</span>
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full py-12 sm:py-20 bg-white dark:bg-gray-900 rounded-2xl sm:rounded-[35px] border border-gray-100 dark:border-gray-800 text-center">
                                <x-empty-state title="No interns found in your department." compact="true" />
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Desktop: Table --}}
                <div class="hidden lg:block overflow-hidden border border-gray-100 dark:border-gray-800 rounded-2xl">
                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left">
                            <thead class="bg-gray-50 dark:bg-gray-800/50 text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest border-b border-gray-100 dark:border-gray-700">
                                <tr>
                                    <th class="px-8 py-5">Intern Name</th>
                                    <th class="px-8 py-5">Student ID</th>
                                    <th class="px-8 py-5">Progress</th>
                                    <th class="px-8 py-5 text-center">Status</th>
                                    <th class="px-8 py-5 text-right">Actions</th>
                                </tr>
                            </thead>
                            {{-- Skeleton Rows --}}
                            <tbody x-show="loading" class="divide-y divide-gray-50 dark:divide-gray-800 motion-reduce:transition-none">
                                @for($i = 0; $i < min(count($interns) ?: 5, 5); $i++)
                                    <tr>
                                        <td class="px-4 md:px-8 py-4 md:py-6">
                                            <div class="flex items-center gap-4">
                                                <x-skeleton class="w-10 h-10 rounded-full shrink-0" />
                                                <div class="space-y-2">
                                                    <x-skeleton class="h-4 w-32 rounded-lg" />
                                                    <x-skeleton class="h-3 w-24 rounded-lg" />
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 md:px-8 py-4 md:py-6"><x-skeleton class="h-4 w-20 rounded-lg" /></td>
                                        <td class="px-4 md:px-8 py-4 md:py-6"><x-skeleton class="h-4 w-40 rounded-lg" /></td>
                                        <td class="px-4 md:px-8 py-4 md:py-6 text-center"><x-skeleton class="h-6 w-20 mx-auto rounded-full" /></td>
                                        <td class="px-4 md:px-8 py-4 md:py-6 text-right"><x-skeleton class="h-8 w-20 ml-auto rounded-lg" /></td>
                                    </tr>
                                @endfor
                            </tbody>

                            <tbody x-show="!loading" x-transition.opacity.duration.500ms class="divide-y divide-gray-50 dark:divide-gray-800 motion-reduce:transition-none">
                                @forelse($interns as $intern)
                                    @php
                                        $isCompleted = optional($intern->certificate)->status === 'published';
                                        $targetHours = $intern->required_hours ?? 480;
                                        $percentage = $targetHours > 0 ? min(100, round(($intern->hours_rendered / $targetHours) * 100)) : 0;
                                    @endphp
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/20 transition-colors group {{ $isCompleted ? 'bg-gray-50/30 dark:bg-gray-800/10' : '' }} motion-reduce:transition-none">
                                        <td class="px-4 md:px-8 py-4 md:py-6 whitespace-nowrap">
                                            <a href="{{ route('supervisor.interns.show', $intern->user) }}" class="flex items-center gap-4 hover:opacity-80 transition-opacity">
                                                <x-avatar :user="$intern" size="10" border="1" circular="true" textSize="xs" />
                                                <div>
                                                    <p class="text-sm font-black text-gray-800 dark:text-gray-100 group-hover:text-[#0B5694] dark:group-hover:text-blue-400 transition-colors uppercase tracking-tight">
                                                        {{ $intern->first_name }} {{ $intern->last_name }}
                                                    </p>
                                                    <p class="text-[11px] text-gray-400 dark:text-gray-500 font-bold uppercase tracking-widest mt-0.5">{{ $intern->user->email ?? 'No Email' }}</p>
                                                </div>
                                            </a>
                                        </td>
                                        <td class="px-4 md:px-8 py-4 md:py-6 text-gray-500 dark:text-gray-400 font-black uppercase tracking-[0.2em] text-[11px] whitespace-nowrap">
                                            {{ $intern->student_id ?? 'N/A' }}
                                        </td>
                                        <td class="px-4 md:px-8 py-4 md:py-6 whitespace-nowrap">
                                            <div class="flex items-center gap-4">
                                                <div class="w-32 h-2.5 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden shadow-inner border border-gray-50 dark:border-gray-700">
                                                    <div class="{{ $isCompleted ? 'bg-emerald-500 dark:bg-emerald-400' : 'bg-[#0B5694] dark:bg-blue-600' }} h-full shadow-lg transition-all duration-1000 motion-reduce:transition-none" style="width: {{ $percentage }}%"></div>
                                                </div>
                                                <span class="font-black text-gray-700 dark:text-gray-300 text-[11px] tracking-widest">{{ $percentage }}% ({{ round($intern->hours_rendered) }}/{{ $targetHours }}h)</span>
                                            </div>
                                        </td>
                                        <td class="px-4 md:px-8 py-4 md:py-6 text-center whitespace-nowrap">
                                            @if($isCompleted)
                                                <span class="bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 text-[10px] font-black px-4 py-1.5 rounded-full uppercase tracking-widest border border-emerald-100 dark:border-emerald-900/30">
                                                    Completed
                                                </span>
                                            @else
                                                <span class="bg-blue-50 dark:bg-blue-900/20 text-[#0B5694] dark:text-blue-400 text-[10px] font-black px-4 py-1.5 rounded-full uppercase tracking-widest border border-blue-100 dark:border-blue-900/30">
                                                    Active
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 md:px-8 py-4 md:py-6 whitespace-nowrap">
                                            <div class="flex items-center justify-end gap-4">
                                                @if(!$isCompleted)
                                                    <a href="{{ route('supervisor.tasks') }}#assign-{{ $intern->user->getRouteKey() }}" title="Assign Task" class="min-h-[44px] min-w-[44px] flex items-center justify-center p-2.5 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 text-gray-400 dark:text-gray-500 hover:text-[#0B5694] dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-xl transition shadow-sm transform hover:scale-110 focus-visible:ring-2 focus-visible:ring-[#0B5694] dark:focus-visible:ring-blue-500 focus-visible:ring-offset-2 focus-visible:outline-none motion-reduce:transform-none">
                                                        <span class="sr-only">Assign Task</span>
                                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                                                    </a>
                                                @endif
                                                <a href="{{ route('supervisor.interns.show', $intern->user) }}" class="min-h-[44px] min-w-[44px] flex items-center justify-center p-2.5 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 text-gray-400 dark:text-gray-500 hover:text-[#0B5694] dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-xl transition shadow-sm transform hover:scale-110 focus-visible:ring-2 focus-visible:ring-[#0B5694] dark:focus-visible:ring-blue-500 focus-visible:ring-offset-2 focus-visible:outline-none motion-reduce:transform-none" title="Refine intern details">
                                                    <span class="sr-only">View intern details</span>
                                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-8 py-20">
                                            <x-empty-state title="No interns found in your department." compact="true" />
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </x-table-container>

            {{-- Pagination --}}
            <div class="mt-2 flex flex-col md:flex-row items-center justify-between gap-6 relative z-10" x-show="!loading" x-transition.opacity>
                <div class="text-[11px] text-gray-400 dark:text-gray-500 font-black uppercase tracking-[0.2em] italic">
                    Showing <b>{{ $interns->firstItem() ?? 0 }}</b> to <b>{{ $interns->lastItem() ?? 0 }}</b> of {{ $interns->total() }}
                </div>
                
                <div class="flex items-center gap-2">
                    @if ($interns->onFirstPage())
                        <span class="min-h-[44px] min-w-[44px] flex items-center justify-center p-3 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-300 dark:text-gray-600 cursor-not-allowed"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg></span>
                    @else
                        <a href="{{ $interns->previousPageUrl() }}" class="min-h-[44px] min-w-[44px] flex items-center justify-center p-3 rounded-xl bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 text-[#0B5694] dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition shadow-sm focus-visible:ring-2 focus-visible:ring-[#0B5694] dark:focus-visible:ring-blue-500 focus-visible:ring-offset-2 focus-visible:outline-none"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg></a>
                    @endif

                    <div class="flex items-center gap-1 bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 p-1.5 rounded-2xl shadow-sm no-scrollbar">
                        @foreach ($interns->getUrlRange(max(1, $interns->currentPage() - 2), min($interns->lastPage(), $interns->currentPage() + 2)) as $page => $url)
                            <a href="{{ $url }}" class="min-h-[40px] min-w-[40px] flex-shrink-0 flex items-center justify-center rounded-xl text-xs font-black transition-all focus-visible:ring-2 focus-visible:ring-[#0B5694] dark:focus-visible:ring-blue-500 focus-visible:ring-offset-2 focus-visible:outline-none {{ $page == $interns->currentPage() ? 'bg-[#0B5694] text-white shadow-lg' : 'text-gray-400 dark:text-gray-500 hover:text-[#0B5694] dark:hover:text-blue-400' }}">{{ $page }}</a>
                        @endforeach
                    </div>

                    @if ($interns->hasMorePages())
                        <a href="{{ $interns->nextPageUrl() }}" class="min-h-[44px] min-w-[44px] flex items-center justify-center p-3 rounded-xl bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 text-[#0B5694] dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition shadow-sm focus-visible:ring-2 focus-visible:ring-[#0B5694] dark:focus-visible:ring-blue-500 focus-visible:ring-offset-2 focus-visible:outline-none"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg></a>
                    @else
                        <span class="min-h-[44px] min-w-[44px] flex items-center justify-center p-3 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-300 dark:text-gray-600 cursor-not-allowed"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg></span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Office Requirements Tab --}}
        <div x-show="activeTab === 'requirements'" class="space-y-6 animate-in fade-in duration-300" x-cloak>
            <div class="max-w-2xl mx-auto w-full">
                <x-card-container title="Office Requirements & Allocation Settings" padding="p-4 md:p-6 lg:p-8">
                    <form action="{{ route('supervisor.office.requirements') }}" method="POST" class="space-y-6">
                        @csrf
                        <div>
                            <label for="intern_limit" class="block text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-3">
                                Allocated Slots / Limit
                            </label>
                            <input type="number" id="intern_limit" name="intern_limit" 
                                value="{{ Auth::user()->supervisorDetails->office->intern_limit ?? 0 }}" 
                                min="0" required
                                class="w-full bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-2xl px-6 py-4 text-sm focus:ring-2 focus:ring-[#0B5694] dark:focus:ring-blue-600 focus:outline-none transition-all font-medium dark:text-gray-100">
                            <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-3 font-bold leading-relaxed">
                                Set the maximum number of interns your office can accommodate. Changing this value will automatically notify Ms. Mal-in and the Head Supervisors.
                            </p>
                        </div>
                        <button type="submit" class="w-full min-h-[48px] bg-[#0B5694] hover:bg-[#084370] text-white font-bold py-4 px-6 rounded-2xl text-[11px] uppercase tracking-widest transition-all shadow-md hover:shadow-lg focus-visible:ring-2 focus-visible:ring-[#0B5694] dark:focus-visible:ring-blue-500 focus-visible:ring-offset-2 focus-visible:outline-none active:scale-[0.98]">
                            Update Limit
                        </button>
                    </form>
                    <div class="border-t border-gray-100 dark:border-gray-800 pt-6 mt-6">
                        <a href="{{ route('supervisor.office.rules') }}" class="w-full min-h-[48px] bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400 font-bold py-4 px-6 rounded-2xl text-[11px] uppercase tracking-widest transition hover:bg-gray-200 dark:hover:bg-gray-700 flex items-center justify-center gap-2 focus-visible:ring-2 focus-visible:ring-[#0B5694] dark:focus-visible:ring-blue-500 focus-visible:ring-offset-2 focus-visible:outline-none active:scale-[0.98] text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                            Competency Requirements Rules
                        </a>
                    </div>
                </x-card-container>
            </div>
        </div>
    </div>
</x-page-layout>
@endsection
