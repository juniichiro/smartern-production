@extends('layouts.app')

@section('title', 'Intern Directory')

@section('content')
<x-page-layout 
    portalType="Supervisor" 
    userName="{{ Auth::user()->supervisorDetails->first_name }}" 
    userRole="{{ Auth::user()->supervisorDetails->office->name }}"
>
    <div x-data="internDirectoryManager" class="flex flex-col h-full">
        {{-- Header --}}
        <x-page-header 
            title="Intern Directory" 
            subtitle="Manage and monitor interns assigned to your office" 
        />

        {{-- Search Bar --}}
        <x-card-container class="mb-10" padding="p-6">
            <form action="{{ route('supervisor.interns') }}" method="GET" class="w-full md:w-1/3">
                <div class="relative group">
                    <button type="submit" class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 group-focus-within:text-[#0B5694] focus:outline-none transition-colors z-10">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    </button>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search intern name or ID..." 
                        @input.debounce.800ms="$el.form.submit()"
                        class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl pl-12 pr-6 py-4 text-sm focus:ring-2 focus:ring-[#0B5694] dark:focus:ring-blue-600 focus:outline-none transition-all placeholder-gray-400 dark:placeholder-gray-500 font-medium dark:text-gray-100">
                </div>
            </form>
        </x-card-container>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10 items-start">
            <div class="lg:col-span-2 flex flex-col gap-6">
                {{-- Interns List --}}
                <x-table-container 
                    title="Assigned Interns" 
                    subtitle="Total: {{ $totalInterns }} currently deployed"
                >
                    <div class="overflow-hidden border border-gray-100 dark:border-gray-800 rounded-2xl">
                        <div class="overflow-x-auto custom-scrollbar">
                            <table class="w-full text-left">
                                <thead class="bg-gray-50 dark:bg-gray-800/50 text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest border-b border-gray-100 dark:border-gray-700">
                                    <tr>
                                        <th class="px-8 py-5">Intern Name</th>
                                        <th class="px-8 py-5">Student ID</th>
                                        <th class="px-8 py-5">Progress (480h)</th>
                                        <th class="px-8 py-5 text-center">Status</th>
                                        <th class="px-8 py-5 text-right">Actions</th>
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
                                            <td class="px-8 py-6"><x-skeleton class="h-4 w-20 rounded-lg" /></td>
                                            <td class="px-8 py-6"><x-skeleton class="h-4 w-40 rounded-lg" /></td>
                                            <td class="px-8 py-6 text-center"><x-skeleton class="h-6 w-20 mx-auto rounded-full" /></td>
                                            <td class="px-8 py-6 text-right"><x-skeleton class="h-8 w-20 ml-auto rounded-lg" /></td>
                                        </tr>
                                    @endfor
                                </tbody>

                                <tbody x-show="!loading" x-transition.opacity.duration.500ms class="divide-y divide-gray-50 dark:divide-gray-800">
                                    @forelse($interns as $intern)
                                        @php
                                            $isCompleted = optional($intern->certificate)->status === 'published';
                                            $percentage = min(100, round(($intern->hours_rendered / 480) * 100));
                                        @endphp
                                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/20 transition-colors group {{ $isCompleted ? 'bg-gray-50/30 dark:bg-gray-800/10' : '' }}">
                                            <td class="px-8 py-6 whitespace-nowrap">
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
                                            <td class="px-8 py-6 text-gray-500 dark:text-gray-400 font-black uppercase tracking-[0.2em] text-[11px] whitespace-nowrap">
                                                {{ $intern->student_id ?? 'N/A' }}
                                            </td>
                                            <td class="px-8 py-6 whitespace-nowrap">
                                                <div class="flex items-center gap-4">
                                                    <div class="w-32 h-2.5 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden shadow-inner border border-gray-50 dark:border-gray-700">
                                                        <div class="{{ $isCompleted ? 'bg-emerald-500 dark:bg-emerald-400' : 'bg-[#0B5694] dark:bg-blue-600' }} h-full shadow-lg transition-all duration-1000" 
                                                             style="width: {{ $percentage }}%"></div>
                                                    </div>
                                                    <span class="font-black text-gray-700 dark:text-gray-300 text-[11px] tracking-widest">{{ $percentage }}%</span>
                                                </div>
                                            </td>
                                            <td class="px-8 py-6 text-center whitespace-nowrap">
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
                                            <td class="px-8 py-6 whitespace-nowrap">
                                                <div class="flex items-center justify-end gap-4">
                                                    @if(!$isCompleted)
                                                        <button title="Assign Task" class="p-2.5 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 text-gray-400 dark:text-gray-500 hover:text-[#0B5694] dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-xl transition shadow-sm transform hover:scale-110">
                                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                                                        </button>
                                                    @endif
                                                    <a href="{{ route('supervisor.interns.show', $intern->user) }}" class="p-2.5 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 text-gray-400 dark:text-gray-500 hover:text-[#0B5694] dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-xl transition shadow-sm transform hover:scale-110" title="Refine intern details">
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
                <div class="mt-2 flex flex-col sm:flex-row items-center justify-between gap-6 relative z-10" x-show="!loading" x-transition.opacity>
                    <div class="text-[11px] text-gray-400 dark:text-gray-500 font-black uppercase tracking-[0.2em] italic">
                        Showing <b>{{ $interns->firstItem() ?? 0 }}</b> to <b>{{ $interns->lastItem() ?? 0 }}</b> of {{ $interns->total() }}
                    </div>
                    
                    <div class="flex items-center gap-2">
                        @if ($interns->onFirstPage())
                            <span class="p-3 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-300 dark:text-gray-600 cursor-not-allowed"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg></span>
                        @else
                            <a href="{{ $interns->previousPageUrl() }}" class="p-3 rounded-xl bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 text-[#0B5694] dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition shadow-sm"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg></a>
                        @endif

                        <div class="flex items-center gap-1 bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 p-1.5 rounded-2xl shadow-sm no-scrollbar">
                            @foreach ($interns->getUrlRange(max(1, $interns->currentPage() - 2), min($interns->lastPage(), $interns->currentPage() + 2)) as $page => $url)
                                <a href="{{ $url }}" class="w-10 h-10 flex-shrink-0 flex items-center justify-center rounded-xl text-xs font-black transition-all {{ $page == $interns->currentPage() ? 'bg-[#0B5694] text-white shadow-lg' : 'text-gray-400 dark:text-gray-500 hover:text-[#0B5694] dark:hover:text-blue-400' }}">{{ $page }}</a>
                            @endforeach
                        </div>

                        @if ($interns->hasMorePages())
                            <a href="{{ $interns->nextPageUrl() }}" class="p-3 rounded-xl bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 text-[#0B5694] dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition shadow-sm"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg></a>
                        @else
                            <span class="p-3 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-300 dark:text-gray-600 cursor-not-allowed"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg></span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1">
                {{-- Manage Requirements --}}
                <x-card-container title="Office Requirements" padding="p-6 sm:p-8">
                    <form action="{{ route('supervisor.office.requirements') }}" method="POST" class="space-y-6">
                        @csrf
                        <div>
                            <label for="intern_limit" class="block text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-3">
                                Allocated Slots / Limit
                            </label>
                            <input type="number" id="intern_limit" name="intern_limit" 
                                value="{{ Auth::user()->supervisorDetails->office->intern_limit ?? 0 }}" 
                                min="0" required
                                class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl px-6 py-4 text-sm focus:ring-2 focus:ring-[#0B5694] dark:focus:ring-blue-600 focus:outline-none transition-all font-medium dark:text-gray-100">
                            <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-3 font-bold leading-relaxed">
                                Set the maximum number of interns your office can accommodate. Changing this value will automatically notify Ms. Mal-in and the Head Supervisors.
                            </p>
                        </div>
                        <button type="submit" class="w-full bg-[#0B5694] hover:bg-[#084370] text-white font-bold py-4 px-6 rounded-2xl text-[11px] uppercase tracking-widest transition-all shadow-md hover:shadow-lg">
                            Update Limit
                        </button>
                    </form>
                </x-card-container>
            </div>
        </div>
    </div>
</x-page-layout>
@endsection
