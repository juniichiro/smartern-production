@extends('layouts.app')

@section('title', 'Onboarding Management')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<x-page-layout 
    portalType="Head Supervisor" 
    userName="{{ Auth::user()->supervisorDetails->first_name }}" 
    userRole="Head Supervisor"
>
    <div x-data="unifiedOnboardingManager" class="flex flex-col">
        
        {{-- Unified Hub Header --}}
        <x-page-header 
            title="Onboarding" 
            highlight="Hub"
            class="mb-12"
        >
            <x-slot:subtitle>
                <span x-text="activeTab === 'applicants' ? 'Pipeline monitoring and intake registry management' : 'Departmental configurations and matching requirement control'"></span>
            </x-slot:subtitle>

            <x-slot:actions>
                {{-- TAB NAVIGATION --}}
                <div class="flex p-1.5 bg-gray-100 dark:bg-gray-900 rounded-[22px] border border-gray-200/50 dark:border-gray-800 shrink-0 shadow-inner mr-2">
                    <button @click="activeTab = 'applicants'" 
                            :class="activeTab === 'applicants' ? 'bg-white dark:bg-gray-800 text-[#0B5694] dark:text-blue-400 shadow-sm' : 'text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300'"
                            class="px-6 py-2.5 rounded-[18px] text-[11px] font-bold uppercase tracking-wider transition-all duration-300">
                        Queue
                    </button>
                    <button @click="activeTab = 'offices'" 
                            :class="activeTab === 'offices' ? 'bg-white dark:bg-gray-800 text-[#0B5694] dark:text-blue-400 shadow-sm' : 'text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300'"
                            class="px-6 py-2.5 rounded-[18px] text-[11px] font-bold uppercase tracking-wider transition-all duration-300">
                        Offices
                    </button>
                    <button @click="activeTab = 'rejections'" 
                            :class="activeTab === 'rejections' ? 'bg-white dark:bg-gray-800 text-[#0B5694] dark:text-blue-400 shadow-sm' : 'text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300'"
                            class="px-6 py-2.5 rounded-[18px] text-[11px] font-bold uppercase tracking-wider transition-all duration-300">
                        Rejections
                    </button>
                </div>

                {{-- APPLICANT ACTIONS --}}
                <div x-show="activeTab === 'applicants'" x-cloak class="flex items-center gap-3">


                    <a href="{{ route('head-supervisor.onboarding.export') }}" class="flex items-center justify-center gap-3 bg-[#0B5694] dark:bg-blue-600 text-white px-8 py-3.5 rounded-xl font-bold text-[10px] uppercase tracking-wider shadow-lg hover:scale-[1.02] active:scale-95 transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Export
                    </a>
                </div>

                {{-- OFFICE ACTIONS --}}
                <div x-show="activeTab === 'offices'" x-cloak class="flex items-center gap-3">
                    <a href="{{ route('head-supervisor.assessment-items.index') }}" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 p-3 rounded-xl hover:bg-gray-50 transition active:scale-95 shadow-sm" title="Manage Assessment Form">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/></svg>
                    </a>
                    <button @click="geofenceOfficeOpen = true; geofenceOffice = {
                            latitude: '{{ $offices->first()->latitude ?? '' }}', 
                            longitude: '{{ $offices->first()->longitude ?? '' }}', 
                            radius_meters: '{{ $offices->first()->radius_meters ?? 100 }}'
                        }; initMap();" 
                        class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-[#0B5694] dark:text-blue-400 p-3 rounded-xl hover:bg-blue-50 transition active:scale-95 shadow-sm" title="View SDO Geofence">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                    </button>
                    <button @click="addOfficeOpen = true" class="bg-[#0B5694] dark:bg-blue-600 text-white px-8 py-3.5 rounded-xl font-bold text-[10px] uppercase tracking-wider shadow-lg hover:scale-[1.02] active:scale-95 transition-all">
                        New Office
                    </button>
                </div>
            </x-slot:actions>
        </x-page-header>

        {{-- Applicant Queue Section --}}
        <template x-if="activeTab === 'applicants'">
            <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
                <x-table-container title="Applicant Registry" subtitle="Live intake monitoring">
                    {{-- DESKTOP TABLE (lg+) --}}
                    <div class="hidden lg:block border border-gray-100 dark:border-gray-800 rounded-3xl overflow-hidden shadow-inner bg-white dark:bg-gray-900/50">
                        <div class="overflow-x-auto custom-scrollbar">
                            <table class="w-full text-left border-collapse">
                                <thead class="bg-gray-50 dark:bg-gray-800/50 text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest border-b border-gray-100 dark:border-gray-800">
                                    <tr>
                                        <th class="px-8 py-5">Application ID</th>
                                        <th class="px-8 py-5">Intern Candidate</th>
                                        <th class="px-8 py-5">Institutional Affiliate</th>
                                        <th class="px-8 py-5">Skills Meta</th>
                                        <th class="px-8 py-5 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                                    @forelse($applicants as $applicant)
                                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition group">
                                            <td class="px-8 py-6 whitespace-nowrap">
                                                <div class="flex items-center gap-4">
                                                    <span class="text-[11px] font-black text-[#0B5694] dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30 px-3 py-1.5 rounded-lg border border-blue-100 dark:border-blue-900/30 uppercase tracking-tighter shadow-sm"># {{ $applicant['id'] }}</span>
                                                    @if(!empty($applicant['resume_path']))
                                                        <button @click="openResume('{{ route('head-supervisor.matching.resume', $applicant['route_key']) }}')" class="inline-flex items-center justify-center p-2.5 bg-[#0B5694]/10 dark:bg-blue-500/10 hover:bg-[#0B5694] hover:text-white border border-[#0B5694]/25 dark:border-blue-500/25 text-[#0B5694] dark:text-blue-400 rounded-xl transition shadow-sm active:scale-95 shrink-0" title="Preview Resume">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/></svg>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-8 py-6 whitespace-nowrap">
                                                <p class="text-sm font-black text-gray-800 dark:text-gray-100 uppercase tracking-tight group-hover:text-[#0B5694] transition-colors">{{ $applicant['name'] }}</p>
                                                <p class="text-[11px] text-gray-400 dark:text-gray-500 font-bold uppercase tracking-widest mt-0.5 leading-none">{{ $applicant['email'] }}</p>
                                                @if(!empty($applicant['phone']))
                                                    <p class="text-[10px] text-gray-400 dark:text-gray-500 font-bold tracking-wider mt-1.5">{{ $applicant['phone'] }}</p>
                                                @endif
                                            </td>
                                            <td class="px-8 py-6 whitespace-nowrap">
                                                <span class="text-[12px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest italic leading-none truncate max-w-[150px] sm:max-w-[200px] inline-block" title="{{ $applicant['school'] }}">{{ $applicant['school'] }}</span>
                                            </td>
                                            <td class="px-8 py-6 whitespace-nowrap">
                                                <div class="text-[11px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-tighter truncate max-w-[180px]" title="{{ implode(', ', $applicant['tech_list']) }}">
                                                    {{ implode(', ', array_slice($applicant['tech_list'], 0, 2)) }}{{ count($applicant['tech_list']) > 2 ? ' + ' . (count($applicant['tech_list']) - 2) . ' more' : '' }}
                                                </div>
                                            </td>
                                            <td class="px-8 py-6 text-right whitespace-nowrap">
                                                <div class="flex items-center justify-end gap-2">
                                                    @if($applicant['is_pending'])
                                                        <button type="button" @click="openReject('{{ addslashes($applicant['name']) }}', '{{ route('head-supervisor.onboarding.reject', $applicant['route_key']) }}')" class="inline-flex items-center gap-1.5 px-4 py-2 bg-rose-50 hover:bg-rose-100 text-rose-600 dark:bg-rose-950/20 dark:hover:bg-rose-900/30 dark:text-rose-400 rounded-xl text-xs font-bold transition active:scale-95 border border-rose-100 dark:border-rose-900/20">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                                            Reject
                                                        </button>
                                                    @else
                                                        <span class="text-[10px] font-bold text-green-600 bg-green-50 dark:bg-green-950/20 dark:text-green-400 border border-green-100 dark:border-green-900/20 px-3 py-1.5 rounded-xl uppercase tracking-wider">Matched</span>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="py-32"><x-empty-state title="Onboarding queue is currently clear" compact="true" /></td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- TABLET CARD LAYOUT (md-lg) --}}
                    <div class="hidden md:grid lg:hidden grid-cols-2 gap-4">
                        @forelse($applicants as $applicant)
                        <div class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden flex flex-col">
                            <div class="h-1.5 bg-gradient-to-r from-[#0B5694] to-blue-400 dark:from-blue-600 dark:to-blue-400"></div>
                            <div class="p-5 flex-1 flex flex-col">
                                <div class="flex items-start gap-3 mb-4">
                                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-[#0B5694] to-blue-400 dark:from-blue-600 dark:to-blue-400 flex items-center justify-center text-white text-[11px] font-black shadow-lg shrink-0"># {{ $applicant['id'] }}</div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-black text-gray-800 dark:text-gray-100 uppercase tracking-tight truncate leading-tight">{{ $applicant['name'] }}</p>
                                        <p class="text-[10px] text-gray-400 dark:text-gray-500 font-bold uppercase tracking-wider truncate mt-0.5">{{ $applicant['email'] }}</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-4 mb-4">
                                    <div class="flex-1">
                                        <span class="text-[9px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider block mb-1">School</span>
                                        <span class="text-[11px] font-black text-gray-600 dark:text-gray-300 uppercase tracking-widest italic leading-tight">{{ $applicant['school'] }}</span>
                                    </div>
                                    <div class="shrink-0">
                                        <span class="text-[9px] font-black text-rose-500 dark:text-rose-400 uppercase tracking-widest bg-rose-50 dark:bg-rose-900/20 px-3 py-1.5 rounded-full border border-rose-100 dark:border-rose-900/30 shadow-sm">{{ $applicant['emergency'] ?? 'N/A' }}</span>
                                    </div>
                                </div>

                                <div class="flex flex-wrap gap-1.5 mb-4">
                                    @foreach(array_slice($applicant['tech_list'], 0, 3) as $skill)
                                        <span class="text-[8px] font-black text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-950 border border-gray-100 dark:border-gray-800 px-2 py-1 rounded-md uppercase tracking-tight">{{ $skill }}</span>
                                    @endforeach
                                    @if(count($applicant['tech_list']) > 3)
                                        <span class="text-[8px] font-black text-[#0B5694] dark:text-blue-400 leading-7">+{{ count($applicant['tech_list']) - 3 }}</span>
                                    @endif
                                </div>

                                 <div class="mt-auto space-y-2">
                                    <div class="grid grid-cols-2 gap-2">
                                        @if(!empty($applicant['resume_path']))
                                         <button @click="openResume('{{ route('head-supervisor.matching.resume', $applicant['route_key']) }}')" class="w-full flex items-center justify-center gap-2 py-3 bg-[#0B5694]/10 dark:bg-blue-500/10 hover:bg-[#0B5694] hover:text-white border border-[#0B5694]/25 dark:border-blue-500/25 text-[#0B5694] dark:text-blue-400 rounded-2xl text-[10px] font-black uppercase tracking-wider transition active:scale-[0.97] shadow-sm">
                                                 <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/></svg>
                                                 Resume
                                             </button>
                                        @else
                                            <div class="py-3 text-center text-[9px] text-gray-300 dark:text-gray-700 font-bold uppercase tracking-wider italic">No Resume</div>
                                        @endif

                                        @if($applicant['is_pending'])
                                            <button type="button" @click="openReject('{{ addslashes($applicant['name']) }}', '{{ route('head-supervisor.onboarding.reject', $applicant['route_key']) }}')" class="w-full flex items-center justify-center gap-1.5 py-3 bg-rose-50 hover:bg-rose-100 text-rose-600 dark:bg-rose-950/20 dark:hover:bg-rose-900/30 dark:text-rose-400 rounded-2xl text-[10px] font-black uppercase tracking-wider transition active:scale-[0.97] border border-rose-100 dark:border-rose-900/20 shadow-sm">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                                Reject
                                            </button>
                                        @else
                                            <div class="w-full flex items-center justify-center py-3 bg-green-50 dark:bg-green-950/20 text-green-600 dark:text-green-400 border border-green-100 dark:border-green-900/20 rounded-2xl text-[10px] font-black uppercase tracking-wider">
                                                Matched
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-span-full py-16 text-center">
                            <x-empty-state title="Onboarding queue is currently clear" compact="true" />
                        </div>
                        @endforelse
                    </div>

                    {{-- MOBILE CARD LAYOUT (< md) --}}
                    <div class="flex md:hidden flex-col gap-3">
                        @forelse($applicants as $applicant)
                        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden active:bg-gray-50 dark:active:bg-gray-800 transition-colors">
                            <div class="flex items-center gap-3 p-4">
                                <span class="shrink-0 text-[10px] font-black text-[#0B5694] dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30 px-2.5 py-1.5 rounded-lg border border-blue-100 dark:border-blue-900/30 uppercase tracking-tighter shadow-sm"># {{ $applicant['id'] }}</span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-black text-gray-800 dark:text-gray-100 uppercase tracking-tight truncate leading-tight">{{ $applicant['name'] }}</p>
                                    <p class="text-[10px] text-gray-400 dark:text-gray-500 font-bold uppercase tracking-wider truncate mt-0.5">{{ $applicant['email'] }}</p>
                                </div>
                                <div class="shrink-0 flex items-center gap-2">
                                    <span class="text-[9px] font-black text-rose-500 dark:text-rose-400 uppercase tracking-widest bg-rose-50 dark:bg-rose-900/20 px-2.5 py-1 rounded-full border border-rose-100 dark:border-rose-900/30">{{ $applicant['emergency'] ?? 'N/A' }}</span>
                                    @if(!empty($applicant['resume_path']))
                                     <button @click="openResume('{{ route('head-supervisor.matching.resume', $applicant['route_key']) }}')" class="h-9 px-3 flex items-center justify-center gap-1.5 bg-[#0B5694]/10 dark:bg-blue-500/10 hover:bg-[#0B5694] hover:text-white border border-[#0B5694]/25 dark:border-blue-500/25 text-[#0B5694] dark:text-blue-400 rounded-xl text-[9px] font-black uppercase tracking-wider transition active:scale-90 shadow-sm" title="Preview Resume">
                                         <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/></svg>
                                         <span>Resume</span>
                                     </button>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-4 px-4 pb-3 pt-1">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <span class="text-[9px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider shrink-0">School:</span>
                                    <span class="text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest italic truncate">{{ $applicant['school'] }}</span>
                                </div>
                                <div class="flex flex-wrap justify-end gap-1 shrink-0">
                                    @foreach(array_slice($applicant['tech_list'], 0, 2) as $skill)
                                        <span class="text-[7px] font-black text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-gray-950 border border-gray-100 dark:border-gray-800 px-1.5 py-0.5 rounded-md uppercase tracking-tight">{{ $skill }}</span>
                                    @endforeach
                                    @if(count($applicant['tech_list']) > 2)
                                        <span class="text-[7px] font-black text-[#0B5694] dark:text-blue-400">+{{ count($applicant['tech_list']) - 2 }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center justify-between border-t border-gray-50 dark:border-gray-800 px-4 py-3 bg-gray-50/50 dark:bg-gray-900/50">
                                <span class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">Status:</span>
                                <div>
                                    @if($applicant['is_pending'])
                                        <button type="button" @click="openReject('{{ addslashes($applicant['name']) }}', '{{ route('head-supervisor.onboarding.reject', $applicant['route_key']) }}')" class="inline-flex items-center gap-1 px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 dark:bg-rose-950/20 dark:hover:bg-rose-900/30 dark:text-rose-400 rounded-xl text-[9px] font-black uppercase tracking-wider transition active:scale-95 border border-rose-100 dark:border-rose-900/20 shadow-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                            Reject
                                        </button>
                                    @else
                                        <span class="text-[9px] font-bold text-green-600 bg-green-50 dark:bg-green-950/20 dark:text-green-400 border border-green-100 dark:border-green-900/20 px-2.5 py-1 rounded-xl uppercase tracking-wider">Matched</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="py-16 text-center">
                            <x-empty-state title="Onboarding queue is currently clear" compact="true" />
                        </div>
                        @endforelse
                    </div>

                    {{-- Pagination Links --}}
                    <div class="mt-8 flex justify-end">
                        {{ $applicants->links('vendor.pagination.tailwind-custom') }}
                    </div>
                </x-table-container>
            </div>
        </template>

        {{-- Rejected Applicants Section --}}
        <template x-if="activeTab === 'rejections'">
            <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
                <x-table-container title="Rejected Applicants Archive" subtitle="Documented rejections (2-month re-application block active)">
                    {{-- DESKTOP TABLE (lg+) --}}
                    <div class="hidden lg:block border border-gray-100 dark:border-gray-800 rounded-3xl overflow-hidden shadow-inner bg-white dark:bg-gray-900/50">
                        <div class="overflow-x-auto custom-scrollbar">
                            <table class="w-full text-left border-collapse">
                                <thead class="bg-gray-50 dark:bg-gray-800/50 text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest border-b border-gray-100 dark:border-gray-800">
                                    <tr>
                                        <th class="px-8 py-5">Candidate Name</th>
                                        <th class="px-8 py-5">Contact Details</th>
                                        <th class="px-8 py-5">Rejection Timestamp</th>
                                        <th class="px-8 py-5">Reason</th>
                                        <th class="px-8 py-5 text-right">Block Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                                    @forelse($rejectedApplicants as $rejected)
                                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition group">
                                            <td class="px-8 py-6 whitespace-nowrap">
                                                <p class="text-sm font-black text-gray-800 dark:text-gray-100 uppercase tracking-tight">{{ $rejected->full_name }}</p>
                                            </td>
                                            <td class="px-8 py-6 whitespace-nowrap">
                                                <p class="text-xs text-gray-500 dark:text-gray-400 font-bold">{{ $rejected->email }}</p>
                                                <p class="text-[11px] text-gray-400 dark:text-gray-500 font-bold mt-1">{{ $rejected->phone_number }}</p>
                                            </td>
                                            <td class="px-8 py-6 whitespace-nowrap">
                                                <span class="text-xs font-bold text-gray-500 dark:text-gray-400">{{ $rejected->rejected_at->format('M d, Y h:i A') }}</span>
                                            </td>
                                            <td class="px-8 py-6">
                                                <p class="text-xs text-gray-600 dark:text-gray-400 font-medium leading-normal max-w-xs truncate" title="{{ $rejected->reason }}">{{ $rejected->reason ?? 'N/A' }}</p>
                                            </td>
                                            <td class="px-8 py-6 text-right whitespace-nowrap">
                                                @php
                                                    $isBlocked = $rejected->rejected_at->gt(now()->subMonths(2));
                                                @endphp
                                                @if($isBlocked)
                                                    <span class="text-[10px] font-bold text-rose-600 bg-rose-50 dark:bg-rose-950/20 dark:text-rose-400 border border-rose-100 dark:border-rose-900/20 px-3 py-1.5 rounded-xl uppercase tracking-wider">Blocked ({{ (int) ceil(now()->diffInHours($rejected->rejected_at->addMonths(2)) / 24) }} days left)</span>
                                                @else
                                                    <span class="text-[10px] font-bold text-gray-500 bg-gray-50 dark:bg-gray-950/20 dark:text-gray-400 border border-gray-100 dark:border-gray-800 px-3 py-1.5 rounded-xl uppercase tracking-wider">Expired</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="py-32"><x-empty-state title="No rejected applicants in archive" compact="true" /></td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- MOBILE/TABLET CARD LAYOUT (< lg) --}}
                    <div class="block lg:hidden space-y-4">
                        @forelse($rejectedApplicants as $rejected)
                            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm p-5 space-y-4">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="text-sm font-black text-gray-800 dark:text-gray-100 uppercase tracking-tight">{{ $rejected->full_name }}</p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 font-bold mt-1">{{ $rejected->email }} • {{ $rejected->phone_number }}</p>
                                    </div>
                                    @php
                                        $isBlocked = $rejected->rejected_at->gt(now()->subMonths(2));
                                    @endphp
                                    @if($isBlocked)
                                        <span class="text-[9px] font-bold text-rose-600 bg-rose-50 dark:bg-rose-950/20 dark:text-rose-400 border border-rose-100 dark:border-rose-900/20 px-2.5 py-1 rounded-full uppercase tracking-wider">Blocked ({{ (int) ceil(now()->diffInHours($rejected->rejected_at->addMonths(2)) / 24) }} days)</span>
                                    @else
                                        <span class="text-[9px] font-bold text-gray-500 bg-gray-50 dark:bg-gray-950/20 dark:text-gray-400 border border-gray-100 dark:border-gray-800 px-2.5 py-1 rounded-full uppercase tracking-wider">Expired</span>
                                    @endif
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-950 p-4 rounded-xl border border-gray-100 dark:border-gray-800 text-xs space-y-2">
                                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-wider">Rejection Details</p>
                                    <p class="text-gray-600 dark:text-gray-400 font-medium leading-relaxed">{{ $rejected->reason ?? 'No reason provided.' }}</p>
                                    <p class="text-[10px] text-gray-400 dark:text-gray-500 font-bold pt-1 border-t border-gray-200/50 dark:border-gray-800/50">Rejected on: {{ $rejected->rejected_at->format('M d, Y h:i A') }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="py-16 text-center">
                                <x-empty-state title="No rejected applicants in archive" compact="true" />
                            </div>
                        @endforelse
                    </div>

                    {{-- Pagination Links --}}
                    <div class="mt-8 flex justify-end">
                        {{ $rejectedApplicants->appends(request()->except('rejections_page'))->links('vendor.pagination.tailwind-custom') }}
                    </div>
                </x-table-container>
            </div>
        </template>

        {{-- Office Configuration Section --}}
        <template x-if="activeTab === 'offices'">
            <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 sm:gap-6 lg:gap-8">
                    @forelse($offices as $office)
                        <div class="bg-white dark:bg-gray-900 rounded-[20px] sm:rounded-[30px] shadow-sm border border-gray-100 dark:border-gray-800 p-5 sm:p-8 flex flex-col justify-between transition-all hover:shadow-md group">
                            <div>
                                <div class="flex justify-between items-start mb-6">
                                    <div class="p-3 bg-blue-50 dark:bg-blue-950/30 text-[#0B5694] dark:text-blue-400 rounded-2xl border border-blue-100 dark:border-blue-900/50">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="8" height="18" x="3" y="3" rx="1"/><rect width="8" height="10" x="13" y="3" rx="1"/><rect width="8" height="5" x="13" y="16" rx="1"/></svg>
                                    </div>
                                    <div class="flex gap-2">
                                        <button @click="editOfficeOpen = true; editOffice = {id: '{{ $office->getRouteKey() }}', name: '{{ addslashes($office->name) }}'}" class="p-2 text-gray-300 dark:text-gray-600 hover:text-[#0B5694] dark:hover:text-blue-400 transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                        </button>
                                        <button @click="deleteOfficeOpen = true; deleteOffice = {id: '{{ $office->getRouteKey() }}', name: '{{ addslashes($office->name) }}'}" class="p-2 text-gray-300 dark:text-gray-600 hover:text-rose-500 transition-colors" title="Delete Office">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2 2v2"></path></svg>
                                        </button>
                                    </div>
                                </div>
                                
                                <h3 class="text-lg sm:text-xl font-black text-gray-900 dark:text-white mb-2 group-hover:text-[#0B5694] dark:group-hover:text-blue-400 transition-colors tracking-tight uppercase">{{ $office->name }}</h3>
                                <p class="text-xs sm:text-sm text-gray-400 dark:text-gray-500 font-bold mb-6 italic tracking-tight">
                                    {{ $office->supervisorDetails->first() ? ($office->supervisorDetails->first()->user->email ?? 'Assigned Supervisor') : 'No supervisor assigned' }}
                                </p>

                                <div class="grid grid-cols-2 gap-4 mb-8">
                                    <div class="bg-gray-50 dark:bg-gray-950 p-4 rounded-2xl border border-gray-100 dark:border-gray-800 transition-colors">
                                        <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Interns</p>
                                        <p class="text-lg sm:text-xl font-black text-gray-800 dark:text-gray-100 tracking-tighter">{{ $office->intern_details_count }}</p>
                                    </div>
                                    <div class="bg-gray-50 dark:bg-gray-950 p-4 rounded-2xl border border-gray-100 dark:border-gray-800 transition-colors">
                                        <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Rules</p>
                                        <p class="text-lg sm:text-xl font-black text-gray-800 dark:text-gray-100 tracking-tighter">{{ $office->rules_count }}</p>
                                    </div>
                                </div>
                            </div>

                            <a href="{{ route('head-supervisor.offices.rules', $office) }}" class="w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-[#0B5694] dark:text-blue-400 text-[10px] font-bold uppercase tracking-wider py-4 rounded-xl text-center hover:bg-blue-50 dark:hover:bg-blue-900/30 transition shadow-sm active:scale-95">
                                Edit Rules
                            </a>
                        </div>
                    @empty
                        <div class="col-span-full py-20">
                            <x-empty-state title="No offices configured yet" compact="true" />
                        </div>
                    @endforelse
                </div>
            </div>
        </template>

        {{-- RESUME PREVIEW MODAL --}}
        <div x-show="showResumePreview" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="fixed inset-0 z-[110] flex items-center justify-center p-4 sm:p-10" 
             x-cloak
             @keydown.escape.window="showResumePreview = false">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="showResumePreview = false"></div>
            
            <div class="relative bg-white dark:bg-gray-900 w-full max-w-6xl h-[85vh] sm:h-full rounded-[25px] sm:rounded-[40px] shadow-2xl flex flex-col overflow-hidden border border-gray-100 dark:border-gray-800">
                <div class="px-6 sm:px-10 py-4 sm:py-6 border-b border-gray-100 dark:border-gray-800 flex justify-between items-center bg-gray-50/50 dark:bg-gray-950/50">
                    <div class="flex items-center gap-3 sm:gap-4">
                        <div class="p-3 bg-blue-50 dark:bg-blue-900/30 rounded-2xl text-blue-600 dark:text-blue-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/></svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-black uppercase tracking-widest text-gray-800 dark:text-white">Resume Artifact</h4>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-0.5 italic">Secure System Preview</p>
                        </div>
                    </div>
                    <button @click="showResumePreview = false" class="p-3 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl text-gray-400 hover:text-red-500 transition-all shadow-sm active:scale-90">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>
                </div>
                <div class="flex-1 bg-gray-100 dark:bg-gray-950 relative">
                    <template x-if="resumeUrl">
                        <iframe :src="resumeUrl" class="w-full h-full border-none shadow-inner" loading="lazy"></iframe>
                    </template>
                </div>
            </div>
        </div>

        {{-- GEOFENCE MODAL --}}
        <div x-show="geofenceOfficeOpen" 
             class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6 bg-gray-900/60 dark:bg-black/80 backdrop-blur-sm"
             x-cloak
             @keydown.escape.window="geofenceOfficeOpen = false">
            <div class="bg-white dark:bg-gray-900 w-full max-w-4xl mx-4 rounded-[25px] sm:rounded-[40px] shadow-2xl p-5 sm:p-8 border border-gray-100 dark:border-gray-800 transition-all" @click.away="geofenceOfficeOpen = false">
                <div class="flex items-center gap-4 mb-6">
                    <div class="p-3 bg-blue-50 dark:bg-blue-900/30 text-[#0B5694] dark:text-blue-400 rounded-2xl shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                    </div>
                    <div>
                        <h3 class="text-xl sm:text-2xl font-extrabold text-gray-900 dark:text-white">SDO Geofence View</h3>
                        <p class="text-[10px] sm:text-xs text-gray-400 dark:text-gray-500 font-medium tracking-tight">Visual boundary for attendance verification (Read-only)</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8">
                    <div class="h-[250px] sm:h-[350px] rounded-[20px] sm:rounded-[25px] overflow-hidden border border-gray-100 dark:border-gray-800 z-0" id="map"></div>

                    <div class="space-y-6">
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-3 block">Exact Location Name / Address</label>
                            <textarea x-model="geofenceOffice.address" readonly rows="2"
                                      class="w-full bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-2xl px-5 py-4 text-sm font-bold text-gray-700 dark:text-gray-300 cursor-not-allowed resize-none"></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-3 block">Latitude</label>
                                <input type="number" x-model="geofenceOffice.latitude" readonly 
                                       class="w-full bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-2xl px-5 py-4 text-sm font-bold text-gray-700 dark:text-gray-300 cursor-not-allowed">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-3 block">Longitude</label>
                                <input type="number" x-model="geofenceOffice.longitude" readonly 
                                       class="w-full bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-2xl px-5 py-4 text-sm font-bold text-gray-700 dark:text-gray-300 cursor-not-allowed">
                            </div>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-3 block">Radius (Meters)</label>
                            <input type="number" x-model="geofenceOffice.radius_meters" readonly 
                                   class="w-full bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-2xl px-5 py-4 text-sm font-bold text-gray-400 dark:text-gray-500 cursor-not-allowed">
                        </div>

                        <div class="pt-4">
                            <button type="button" @click="geofenceOfficeOpen = false" class="w-full px-6 py-4 bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 rounded-2xl font-bold text-xs uppercase tracking-wider hover:bg-gray-200 dark:hover:bg-gray-700 transition">
                                Close View
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ADD OFFICE MODAL --}}
        <div x-show="addOfficeOpen" 
             class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-gray-900/60 dark:bg-black/80 backdrop-blur-sm"
             x-cloak
             @keydown.escape.window="addOfficeOpen = false">
            <div class="bg-white dark:bg-gray-900 w-full max-w-md mx-4 rounded-[25px] sm:rounded-[40px] shadow-2xl p-6 sm:p-10 border border-gray-100 dark:border-gray-800 transition-all" @click.away="addOfficeOpen = false">
                <h3 class="text-xl sm:text-2xl font-extrabold text-gray-900 dark:text-white mb-2">Add New Office</h3>
                <p class="text-xs sm:text-sm text-gray-400 dark:text-gray-500 mb-6 sm:mb-8 font-medium tracking-tight">Create a new department. You can define matching rules later.</p>
                
                <form action="{{ route('head-supervisor.offices.store') }}" method="POST">
                    @csrf
                    <div class="mb-8">
                        <label class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-3 block">Office Name</label>
                        <input type="text" name="name" required class="w-full bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-2xl px-5 py-4 text-sm font-bold text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-[#0B5694] dark:focus:ring-blue-400 focus:outline-none transition-all placeholder-gray-400" placeholder="e.g. ICT Department">
                    </div>

                    <div class="flex gap-4">
                        <button type="button" @click="addOfficeOpen = false" class="flex-1 px-6 py-4 bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 rounded-2xl font-bold text-xs uppercase tracking-wider hover:bg-gray-200 dark:hover:bg-gray-700 transition">
                            Cancel
                        </button>
                        <button type="submit" class="flex-1 px-6 py-4 bg-[#0B5694] dark:bg-blue-600 text-white rounded-2xl font-bold text-xs uppercase tracking-wider hover:bg-[#084577] dark:hover:bg-blue-700 transition shadow-lg">
                            Create Office
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- EDIT OFFICE MODAL --}}
        <div x-show="editOfficeOpen" 
             class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-gray-900/60 dark:bg-black/80 backdrop-blur-sm"
             x-cloak
             @keydown.escape.window="editOfficeOpen = false">
            <div class="bg-white dark:bg-gray-900 w-full max-w-md mx-4 rounded-[25px] sm:rounded-[40px] shadow-2xl p-6 sm:p-10 border border-gray-100 dark:border-gray-800 transition-all" @click.away="editOfficeOpen = false">
                <h3 class="text-xl sm:text-2xl font-extrabold text-gray-900 dark:text-white mb-2">Edit Office</h3>
                <p class="text-xs sm:text-sm text-gray-400 dark:text-gray-500 mb-6 sm:mb-8 font-medium tracking-tight">Update the department name.</p>
                
                <form :action="`/head-supervisor/offices/${editOffice.id}`" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-8">
                        <label class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-3 block">Office Name</label>
                        <input type="text" name="name" x-model="editOffice.name" required class="w-full bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-2xl px-5 py-4 text-sm font-bold text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-[#0B5694] dark:focus:ring-blue-400 focus:outline-none transition-all">
                    </div>

                    <div class="flex gap-4">
                        <button type="button" @click="editOfficeOpen = false" class="flex-1 px-6 py-4 bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 rounded-2xl font-bold text-xs uppercase tracking-wider hover:bg-gray-200 dark:hover:bg-gray-700 transition">
                            Cancel
                        </button>
                        <button type="submit" class="flex-1 px-6 py-4 bg-[#0B5694] dark:bg-blue-600 text-white rounded-2xl font-bold text-xs uppercase tracking-wider hover:bg-[#084577] dark:hover:bg-blue-700 transition shadow-lg">
                            Update
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- DELETE OFFICE MODAL --}}
        <div x-show="deleteOfficeOpen" 
             class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-gray-900/60 dark:bg-black/80 backdrop-blur-sm"
             x-cloak
             @keydown.escape.window="deleteOfficeOpen = false">
            <div class="bg-white dark:bg-gray-900 w-full max-w-md mx-4 rounded-[25px] sm:rounded-[40px] shadow-2xl p-6 sm:p-10 border border-gray-100 dark:border-gray-800 transition-all text-center" @click.away="deleteOfficeOpen = false">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-rose-100 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                </div>
                
                <h3 class="text-2xl font-extrabold text-gray-900 dark:text-white mb-2">Delete Office</h3>
                <p class="text-sm text-gray-400 dark:text-gray-500 mb-8 font-medium tracking-tight">
                    Are you sure you want to delete <span class="font-extrabold text-rose-500 dark:text-rose-400" x-text="deleteOffice.name"></span>? This will permanently remove the office and all associated rules. This action cannot be undone.
                </p>
                
                <form :action="`/head-supervisor/offices/${deleteOffice.id}`" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="flex gap-4">
                        <button type="button" @click="deleteOfficeOpen = false" class="flex-1 px-6 py-4 bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 rounded-2xl font-bold text-xs uppercase tracking-wider hover:bg-gray-200 dark:hover:bg-gray-700 transition">
                            Cancel
                        </button>
                        <button type="submit" class="flex-1 px-6 py-4 bg-rose-600 text-white rounded-2xl font-bold text-xs uppercase tracking-wider hover:bg-rose-700 transition shadow-lg shadow-rose-500/25">
                            Delete Office
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- REJECTION CONFIRMATION MODAL --}}
        <div x-show="rejectOpen" 
             class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6 bg-gray-900/60 dark:bg-black/80 backdrop-blur-sm"
             x-cloak
             @keydown.escape.window="rejectOpen = false">
            <div class="bg-white dark:bg-gray-900 w-full max-w-md sm:max-w-lg md:max-w-xl mx-4 rounded-[25px] sm:rounded-[30px] shadow-2xl p-6 sm:p-8 border border-gray-100 dark:border-gray-800 transition-all cursor-default" @click.away="rejectOpen = false">
                <div class="flex items-center gap-4 mb-6">
                    <div class="p-3 bg-rose-50 dark:bg-rose-950/30 text-rose-600 dark:text-rose-400 rounded-2xl shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg sm:text-xl font-black text-gray-900 dark:text-white uppercase tracking-tight">Reject Applicant</h3>
                        <p class="text-xs text-gray-400 dark:text-gray-500 font-medium tracking-tight">Confirm application rejection</p>
                    </div>
                </div>

                <form :action="rejectApplicantUrl" method="POST" class="space-y-6">
                    @csrf
                    <input type="hidden" name="reason" value="Rejected during live onboarding intake review">
                    <div>
                        <p class="text-xs sm:text-sm font-bold text-gray-650 dark:text-gray-400 leading-relaxed">
                            Are you sure you want to reject <span class="font-black text-[#0B5694] dark:text-blue-400 uppercase" x-text="rejectApplicantName"></span>? This will reject their application and block them from re-applying for 2 months.
                        </p>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="flex-1 px-4 py-3 bg-rose-600 hover:bg-rose-700 text-white font-bold text-[10px] uppercase tracking-wider rounded-xl transition active:scale-95 shadow-lg shadow-rose-600/10 cursor-pointer text-center">
                            Reject Applicant
                        </button>
                        <button type="button" @click="rejectOpen = false" class="px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-650 dark:text-gray-300 font-bold text-[10px] uppercase tracking-wider rounded-xl hover:bg-gray-50 dark:hover:bg-gray-850 transition active:scale-95 cursor-pointer text-center">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-page-layout>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('unifiedOnboardingManager', () => ({
        activeTab: 'applicants',
        loading: true,
        resumeUrl: '', 
        showResumePreview: false,
        addOfficeOpen: false, 
        editOfficeOpen: false, 
        deleteOfficeOpen: false,
        geofenceOfficeOpen: false,
        rejectOpen: false,
        rejectApplicantName: '',
        rejectApplicantUrl: '',
        editOffice: {id: '', name: ''},
        deleteOffice: {id: '', name: ''},
        geofenceOffice: {latitude: '', longitude: '', radius_meters: 100, address: 'Fetching address...'},
        map: null,
        marker: null,
        circle: null,

        init() {
            // Check for hash in URL to set initial tab
            const hash = window.location.hash.replace('#', '');
            if (hash === 'offices') this.activeTab = 'offices';
            if (hash === 'rejections' || window.location.search.includes('rejections_page')) this.activeTab = 'rejections';
            
            setTimeout(() => {
                this.loading = false;
            }, 800);
        },

        openResume(url) {
            this.resumeUrl = url;
            this.showResumePreview = true;
        },

        openReject(name, url) {
            this.rejectApplicantName = name;
            this.rejectApplicantUrl = url;
            this.rejectOpen = true;
        },

        async reverseGeocode(lat, lng) {
            try {
                const response = await fetch(`https:// nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`);
                const data = await response.json();
                this.geofenceOffice.address = data.display_name || 'Address not found';
            } catch (error) {
                this.geofenceOffice.address = 'Unable to fetch address';
            }
        },

        initMap() {
            this.$nextTick(() => {
                if (!this.map) {
                    this.map = L.map('map').setView([this.geofenceOffice.latitude || 14.6495, this.geofenceOffice.longitude || 121.0494], 16);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© OpenStreetMap contributors'
                    }).addTo(this.map);
                } else {
                    this.map.setView([this.geofenceOffice.latitude, this.geofenceOffice.longitude], 16);
                }

                if (this.marker) this.map.removeLayer(this.marker);
                if (this.circle) this.map.removeLayer(this.circle);

                if (this.geofenceOffice.latitude && this.geofenceOffice.longitude) {
                    this.marker = L.marker([this.geofenceOffice.latitude, this.geofenceOffice.longitude]).addTo(this.map);
                    this.circle = L.circle([this.geofenceOffice.latitude, this.geofenceOffice.longitude], {
                        color: '#0B5694',
                        fillColor: '#0B5694',
                        fillOpacity: 0.2,
                        radius: parseInt(this.geofenceOffice.radius_meters)
                    }).addTo(this.map);
                    
                    this.reverseGeocode(this.geofenceOffice.latitude, this.geofenceOffice.longitude);
                }

                // Force a resize fix for Leaflet
                setTimeout(() => { this.map.invalidateSize(); }, 200);
            });
        }
    }));
});
</script>
@endsection
