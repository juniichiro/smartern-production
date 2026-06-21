@extends('layouts.app')

@section('title', 'Intern Management')

@section('content')
<x-page-layout 
    portalType="School Coordinator" 
    userName="{{ Auth::user()->schoolCoordinatorDetails->first_name }}" 
    userRole="School Coordinator"
>
    {{-- Header Section --}}
    <x-page-header 
        title="Student Monitoring" 
        subtitle="Monitor student progress and attendance across all departments" 
    />

    {{-- Action Shortcuts (Refined SmarTern Style) --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10" x-data="{
        getExportUrl() {
            const form = document.getElementById('filterForm');
            const params = new URLSearchParams(new FormData(form)).toString();
            return `{{ route('coordinator.interns.export') }}?${params}`;
        }
    }">
        {{-- Triage Risk --}}
        <a href="{{ route('coordinator.interns', ['risk' => 1]) }}" 
           @click.preventDefault="fetchTable($el.href)"
           class="group p-8 bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-[35px] hover:border-rose-200 dark:hover:border-rose-500/30 transition-all shadow-sm flex items-center gap-6">
            <div class="w-16 h-16 bg-rose-50 dark:bg-rose-900/20 text-rose-500 dark:text-rose-400 rounded-2xl flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform border border-rose-100/50 dark:border-rose-900/30">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </div>
            <div>
                <h4 class="text-[15px] font-black text-gray-800 dark:text-gray-100 uppercase tracking-tight group-hover:text-rose-500 transition-colors">Show At-Risk</h4>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-1">View attendance issues</p>
            </div>
        </a>

        {{-- Manage Completion --}}
        <a href="{{ route('coordinator.certificates') }}" class="group p-8 bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-[35px] hover:border-emerald-200 dark:hover:border-emerald-500/30 transition-all shadow-sm flex items-center gap-6">
            <div class="w-16 h-16 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 rounded-2xl flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform border border-emerald-100/50 dark:border-emerald-900/30">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>
            </div>
            <div>
                <h4 class="text-[15px] font-black text-gray-800 dark:text-gray-100 uppercase tracking-tight group-hover:text-emerald-500 transition-colors">Student Records</h4>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-1">Manage certificates</p>
            </div>
        </a>

        {{-- Export Registry --}}
        <a :href="getExportUrl()" 
           class="group p-8 bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-[35px] hover:border-[#0B5694] dark:hover:border-blue-500/30 transition-all shadow-sm flex items-center gap-6 text-left w-full">
            <div class="w-16 h-16 bg-blue-50 dark:bg-blue-900/20 text-[#0B5694] dark:text-blue-400 rounded-2xl flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform border border-blue-100/50 dark:border-blue-900/30">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
            </div>
            <div>
                <h4 class="text-[15px] font-black text-gray-800 dark:text-gray-100 uppercase tracking-tight group-hover:text-[#0B5694] transition-colors">Download List</h4>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-1">Export PDF Registry</p>
            </div>
        </a>
    </div>

    <x-card-container class="mb-10 relative z-[60]" padding="p-8" overflow="overflow-visible">
        <form action="{{ route('coordinator.interns') }}" method="GET" id="filterForm" 
              x-data="{ 
                officeOpen: false, 
                statusOpen: false,
                loading: false,
                fetchTable(url = null) {
                    this.loading = true;
                    const form = document.getElementById('filterForm');
                    
                    // If URL is provided, we might want to sync form inputs if they are in the URL
                    // But for simplicity, we just fetch the URL.
                    // If no URL, we use form data.
                    const fetchUrl = url || `${form.action}?${new URLSearchParams(new FormData(form)).toString()}`;
                    
                    fetch(fetchUrl, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.text())
                    .then(html => {
                        document.getElementById('internTableContainer').innerHTML = html;
                        window.history.pushState({}, '', fetchUrl);
                        this.loading = false;
                        this.attachPaginationListeners();

                        // Sync risk input if URL has it
                        if (url) {
                            const urlParams = new URL(url).searchParams;
                            if (urlParams.has('risk')) {
                                document.getElementById('riskInput').value = urlParams.get('risk');
                            } else if (!urlParams.has('page')) {
                                // Only clear risk if we are not just paginating
                                document.getElementById('riskInput').value = '';
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching table:', error);
                        this.loading = false;
                    });
                },
                attachPaginationListeners() {
                    const container = document.getElementById('internTableContainer');
                    const links = container.querySelectorAll('nav[role=\'navigation\'] a');
                    links.forEach(link => {
                        link.addEventListener('click', (e) => {
                            e.preventDefault();
                            this.fetchTable(link.href);
                        });
                    });
                },
                init() {
                    this.attachPaginationListeners();
                }
              }" 
              class="flex flex-col lg:flex-row items-end gap-6 w-full"
              @submit.prevent="fetchTable()">
            
            <input type="hidden" name="risk" id="riskInput" value="{{ request('risk') }}">
            
            {{-- Search Input --}}
            <div class="flex-1 w-full space-y-2">
                <label class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest px-1">Search Students</label>
                <div class="relative group">
                    <button type="submit" class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 hover:text-[#0B5694] focus:outline-none transition-colors z-10">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    </button>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or ID..." 
                           @input.debounce.500ms="fetchTable()"
                           class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl pl-12 pr-4 py-3.5 text-sm font-medium text-gray-700 dark:text-gray-200 outline-none focus:ring-2 focus:ring-[#0B5694] transition-all">
                </div>
            </div>

            {{-- Office Dropdown --}}
            <div class="w-full lg:w-64 space-y-2">
                <label class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest px-1">Assigned Department</label>
                <div class="relative z-[70]" @click.away="officeOpen = false">
                    <input type="hidden" name="office" x-ref="officeInput" value="{{ request('office') }}">
                    <button type="button" @click="officeOpen = !officeOpen; statusOpen = false"
                            class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl px-6 py-3.5 text-[11px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest flex items-center justify-between hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-all">
                        <span x-text="officeOpen ? 'Selecting...' : ($refs.officeInput.value ? document.querySelector(`[data-office-id='${$refs.officeInput.value}']`)?.innerText : 'All Departments')">{{ $offices->find(request('office'))?->name ?? 'All Departments' }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="transition-transform duration-200" :class="officeOpen ? 'rotate-180' : ''"><path d="m6 9 6 6 6-6"/></svg>
                    </button>

                    <div x-show="officeOpen" x-transition.opacity.duration.150ms x-cloak
                         class="absolute z-[80] left-0 right-0 mt-2 bg-white dark:bg-gray-950 border border-gray-100 dark:border-gray-800 rounded-2xl shadow-2xl overflow-hidden py-2 ring-1 ring-black/5">
                        <div class="max-h-[250px] overflow-y-auto custom-scrollbar">
                            <div @click="$refs.officeInput.value = ''; officeOpen = false; fetchTable()" 
                                 class="px-5 py-3 text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 hover:text-[#0B5694] dark:hover:text-blue-400 cursor-pointer transition-all">
                                All Departments
                            </div>
                            @foreach($offices as $office)
                                <div @click="$refs.officeInput.value = '{{ $office->id }}'; officeOpen = false; fetchTable()" 
                                     data-office-id="{{ $office->id }}"
                                     class="px-5 py-3 text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 hover:text-[#0B5694] dark:hover:text-blue-400 cursor-pointer transition-all {{ request('office') == $office->id ? 'bg-blue-50/50 dark:bg-blue-900/20 text-[#0B5694] dark:text-blue-400' : '' }}">
                                    {{ $office->name }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Status Dropdown --}}
            <div class="w-full lg:w-64 space-y-2">
                <label class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest px-1">Current Status</label>
                <div class="relative z-[70]" @click.away="statusOpen = false">
                    <input type="hidden" name="status" x-ref="statusInput" value="{{ request('status') }}">
                    <button type="button" @click="statusOpen = !statusOpen; officeOpen = false"
                            class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl px-6 py-3.5 text-[11px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest flex items-center justify-between hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-all">
                        <span x-text="statusOpen ? 'Selecting...' : ($refs.statusInput.value ? document.querySelector(`[data-status-val='${$refs.statusInput.value}']`)?.innerText : 'All Statuses')">
                            @switch(request('status'))
                                @case('active') Active Training @break
                                @case('endorsing') Endorsement Due @break
                                @case('completed') Certified @break
                                @default All Statuses
                            @endswitch
                        </span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="transition-transform duration-200" :class="statusOpen ? 'rotate-180' : ''"><path d="m6 9 6 6 6-6"/></svg>
                    </button>

                    <div x-show="statusOpen" x-transition.opacity.duration.150ms x-cloak
                         class="absolute z-[80] left-0 right-0 mt-2 bg-white dark:bg-gray-950 border border-gray-100 dark:border-gray-800 rounded-2xl shadow-2xl overflow-hidden py-2 ring-1 ring-black/5">
                        <div class="py-1">
                            @foreach([
                                '' => 'All Statuses',
                                'active' => 'Active Training',
                                'endorsing' => 'Endorsement Due',
                                'completed' => 'Certified'
                            ] as $val => $label)
                                <div @click="$refs.statusInput.value = '{{ $val }}'; statusOpen = false; fetchTable()" 
                                     data-status-val="{{ $val }}"
                                     class="px-5 py-3 text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 hover:text-[#0B5694] dark:hover:text-blue-400 cursor-pointer transition-all {{ request('status') == $val ? 'bg-blue-50/50 dark:bg-blue-900/20 text-[#0B5694] dark:text-blue-400' : '' }}">
                                    {{ $label }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </x-card-container>

    {{-- Table Container --}}
    <x-table-container 
        title="Student Registry" 
        subtitle="List of students currently in training"
    >
        <div id="internTableContainer" class="relative">
            {{-- Loading Overlay --}}
            <div x-show="loading" x-cloak 
                 class="absolute inset-0 bg-white/50 dark:bg-gray-900/50 backdrop-blur-[2px] z-50 flex items-center justify-center rounded-3xl transition-opacity">
                <div class="flex flex-col items-center gap-4">
                    <div class="w-10 h-10 border-4 border-[#0B5694] border-t-transparent rounded-full animate-spin"></div>
                    <p class="text-[10px] font-black text-[#0B5694] uppercase tracking-widest">Updating Registry...</p>
                </div>
            </div>

            @include('coordinator.partials._intern_table')
        </div>
    </x-table-container>
</x-page-layout>
@endsection
