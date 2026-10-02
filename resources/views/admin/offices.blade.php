@extends('layouts.app')

@section('title', 'Geofence Overview - SmarTern Admin')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

@php
    $officesJson = $offices->map(function ($office) {
        $loc = is_array($office->location_data) ? $office->location_data : (json_decode($office->location_data, true) ?: []);
        $building = $loc['building'] ?? ($loc['name'] ?? null);
        $floor = $loc['floor'] ?? null;
        $locationStr = trim(($building ? $building : '') . ($floor ? " · {$floor}" : ''));

        return [
            'id' => $office->id,
            'name' => $office->name,
            'latitude' => $office->latitude ? (float) $office->latitude : 14.6150485,
            'longitude' => $office->longitude ? (float) $office->longitude : 121.0105983,
            'radius_meters' => (int) ($office->radius_meters ?? 100),
            'location_str' => $locationStr ?: 'Main Building',
            'interns_count' => $office->intern_details_count ?? 0,
            'supervisors_count' => $office->supervisor_details_count ?? 0,
            'cached_address' => $office->cached_address ?? null,
            'has_geofence' => ($office->latitude && $office->longitude),
        ];
    });

    $firstOffice = $offices->first();
    $divisionLat = $firstOffice?->latitude ? (float) $firstOffice->latitude : 14.6150485;
    $divisionLng = $firstOffice?->longitude ? (float) $firstOffice->longitude : 121.0105983;
    $divisionRadius = (int) ($firstOffice?->radius_meters ?? 100);
    $divisionAddress = $firstOffice?->cached_address ?? null;
    $totalInterns = $offices->sum('intern_details_count');
    $totalSupervisors = $offices->sum('supervisor_details_count');
@endphp

<x-page-layout 
    portalType="Admin" 
    userName="{{ Auth::user()->email }}" 
    userRole="System Administrator"
    fullHeight="true"
>
    <div 
        x-data="geofenceInspector(
            @js($officesJson),
            {{ $divisionLat }},
            {{ $divisionLng }},
            {{ $divisionRadius }},
            @js($divisionAddress)
        )"
        x-init="init()"
        class="flex flex-col flex-1 min-h-0 w-full"
    >
        {{-- PAGE HEADER (Breadcrumb + Title + Subtitle + Action Button) --}}
        {{-- HARD RULE: NO STAT TILES ON THIS PAGE --}}
        <header class="mb-5 sm:mb-6 shrink-0 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                {{-- Breadcrumb --}}
                <nav class="flex items-center gap-1.5 text-xs font-medium text-slate-400 dark:text-gray-400 mb-1.5" aria-label="Breadcrumb">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-[#0B5694] dark:hover:text-blue-400 transition-colors flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                        <span>Dashboard</span>
                    </a>
                    <svg class="w-3 h-3 text-slate-300 dark:text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                    <span class="text-slate-600 dark:text-gray-300 font-semibold">Geofencing</span>
                </nav>

                {{-- Title & Subtitle --}}
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-tight">
                    Geofence Overview
                </h1>
                <p class="text-xs sm:text-sm font-medium text-slate-500 dark:text-gray-400 mt-0.5">
                    Unified Division Office boundary · Covers {{ $offices->count() }} internal units
                    @if($lastUpdatedLog)
                        · Refined {{ $lastUpdatedLog->timestamp->diffForHumans() }}
                    @endif
                </p>
            </div>

            {{-- Header Action --}}
            <div class="flex items-center gap-3">
                <button 
                    type="button" 
                    @click="openEdit()"
                    class="h-10 sm:h-11 px-5 rounded-xl bg-[#0B5694] hover:bg-[#084577] dark:bg-blue-600 dark:hover:bg-blue-700 text-white font-semibold text-xs sm:text-sm flex items-center gap-2 transition-all shadow-md shadow-blue-900/10 active:scale-95 cursor-pointer"
                    aria-haspopup="dialog"
                >
                    <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/>
                        <circle cx="12" cy="11" r="3"/>
                    </svg>
                    <span>Update Division Geofence</span>
                </button>
            </div>
        </header>

        {{-- FLASH MESSAGES --}}
        @if(session('success'))
            <div class="mb-4 p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200/80 dark:border-emerald-800/40 flex items-center gap-3 text-emerald-800 dark:text-emerald-300 text-xs sm:text-sm font-medium animate-in fade-in slide-in-from-top-2">
                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200/80 dark:border-rose-800/40 flex items-start gap-3 text-rose-800 dark:text-rose-300 text-xs sm:text-sm font-medium animate-in fade-in slide-in-from-top-2">
                <svg class="w-5 h-5 text-rose-600 dark:text-rose-400 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <div>
                    <span class="font-bold block">Please check the following errors:</span>
                    <ul class="list-disc list-inside mt-1 space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        {{-- MAIN TWO-REGION LAYOUT: Side Panel + Map --}}
        <div class="flex-1 min-h-[520px] flex flex-col lg:flex-row gap-5 lg:gap-6 relative overflow-hidden">
            
            {{-- DESKTOP SIDE PANEL --}}
            <aside 
                class="hidden lg:flex w-[360px] xl:w-[400px] shrink-0 flex-col gap-4 overflow-y-auto pr-1 custom-scrollbar"
                aria-label="Geofence details and office directory"
            >
                {{-- 1. DIVISION GEOFENCE CARD (Unified Facility Boundary) --}}
                <div class="p-5 rounded-[22px] bg-white dark:bg-gray-900 border border-slate-200/80 dark:border-gray-800 shadow-sm flex flex-col gap-4">
                    {{-- Card Header: Title & Status Pill --}}
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-[#0B5694] dark:text-blue-400 block mb-0.5">Division Geofence Center</span>
                            <h2 class="text-base font-bold text-slate-900 dark:text-white leading-tight">DepEd Division Office</h2>
                            <p class="text-xs text-slate-500 dark:text-gray-400 font-medium mt-0.5">Unified perimeter for all {{ $offices->count() }} units</p>
                        </div>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800/40 shrink-0">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Active
                        </span>
                    </div>

                    {{-- Address Display with Skeleton & Coordinates Fallback --}}
                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-gray-800/60 border border-slate-200/60 dark:border-gray-700/60 flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-[#0B5694] dark:text-blue-400 flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-gray-500 block mb-0.5">Facility Location</span>
                            <template x-if="loadingAddress">
                                <div class="space-y-1.5 py-1">
                                    <div class="h-3 bg-slate-200 dark:bg-gray-700 rounded-md animate-pulse w-5/6"></div>
                                    <div class="h-3 bg-slate-200 dark:bg-gray-700 rounded-md animate-pulse w-3/4"></div>
                                </div>
                            </template>
                            <template x-if="!loadingAddress && divisionData.address">
                                <p class="text-xs text-slate-800 dark:text-gray-200 font-medium leading-relaxed break-words" x-text="divisionData.address"></p>
                            </template>
                            <template x-if="!loadingAddress && !divisionData.address">
                                <div>
                                    <p class="text-xs font-mono font-semibold text-slate-700 dark:text-gray-300" x-text="divisionData.latitude.toFixed(5) + ', ' + divisionData.longitude.toFixed(5)"></p>
                                    <button 
                                        type="button" 
                                        @click="fetchDivisionAddress(true)"
                                        class="mt-1 text-[11px] font-medium text-[#0B5694] dark:text-blue-400 hover:underline flex items-center gap-1 cursor-pointer"
                                    >
                                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 21h5v-5"/></svg>
                                        <span>Address unavailable · Retry</span>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Metrics Grid: Radius + Coordinates --}}
                    <div class="grid grid-cols-2 gap-2.5">
                        {{-- Radius --}}
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-gray-800/60 border border-slate-200/60 dark:border-gray-700/60">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-gray-500 block mb-0.5">Perimeter Radius</span>
                            <div class="flex items-baseline gap-1.5">
                                <span class="text-lg font-extrabold text-slate-900 dark:text-white" x-text="divisionData.radius_meters"></span>
                                <span class="text-xs font-medium text-slate-500 dark:text-gray-400">meters</span>
                            </div>
                        </div>

                        {{-- Coordinates with Copy Action --}}
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-gray-800/60 border border-slate-200/60 dark:border-gray-700/60 flex flex-col justify-between">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-gray-500">Center</span>
                                <button 
                                    type="button" 
                                    @click="copyCoordinates()"
                                    class="text-slate-400 hover:text-[#0B5694] dark:hover:text-blue-400 transition-colors p-0.5 rounded cursor-pointer"
                                    :title="copiedCoords ? 'Copied!' : 'Copy coordinates'"
                                    aria-label="Copy coordinates"
                                >
                                    <template x-if="!copiedCoords">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
                                    </template>
                                    <template x-if="copiedCoords">
                                        <svg class="w-3.5 h-3.5 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                    </template>
                                </button>
                            </div>
                            <span 
                                class="text-[11px] font-mono font-bold text-slate-700 dark:text-gray-300 mt-1 truncate"
                                x-text="divisionData.latitude.toFixed(4) + ', ' + divisionData.longitude.toFixed(4)"
                            ></span>
                        </div>
                    </div>

                    {{-- Staffing Info & Actions --}}
                    <div class="pt-2 border-t border-slate-100 dark:border-gray-800 flex items-center justify-between gap-3 text-xs">
                        <div class="flex items-center gap-2 text-slate-500 dark:text-gray-400">
                            <span class="inline-flex items-center gap-1 font-medium">
                                <svg class="w-3.5 h-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                <span>{{ $totalInterns }} interns</span>
                            </span>
                            <span>·</span>
                            <span class="font-medium">{{ $totalSupervisors }} supervisors</span>
                        </div>

                        <button 
                            type="button" 
                            @click="openEdit()"
                            class="px-3.5 py-1.5 rounded-lg text-xs font-semibold text-[#0B5694] dark:text-blue-400 bg-blue-50/80 dark:bg-blue-900/30 hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-colors flex items-center gap-1.5 cursor-pointer"
                        >
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                            <span>Edit Geofence</span>
                        </button>
                    </div>
                </div>

                {{-- 2. ALL OFFICES DIRECTORY (Housed in Facility) --}}
                <div class="p-4 rounded-[22px] bg-white dark:bg-gray-900 border border-slate-200/80 dark:border-gray-800 shadow-sm flex flex-col gap-3">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-700 dark:text-gray-300">Offices In Facility</h3>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-gray-800 text-slate-600 dark:text-gray-400" x-text="filteredOffices.length"></span>
                        </div>
                        <span class="text-[10px] text-slate-400 font-medium">Single Premises</span>
                    </div>

                    {{-- Search Input --}}
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 dark:text-gray-500">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        </span>
                        <input 
                            type="text" 
                            x-model="searchQuery"
                            placeholder="Filter internal units or floors..."
                            class="w-full pl-8 pr-7 py-2 rounded-xl text-xs font-medium bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 text-slate-900 dark:text-gray-100 placeholder-slate-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-[#0B5694]/20 focus:border-[#0B5694] transition-all"
                            aria-label="Filter internal units"
                        >
                        <template x-if="searchQuery">
                            <button 
                                type="button" 
                                @click="searchQuery = ''"
                                class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-gray-300"
                                aria-label="Clear filter"
                            >
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                            </button>
                        </template>
                    </div>

                    {{-- Office Rows List --}}
                    <div 
                        class="space-y-1.5 max-h-[280px] overflow-y-auto pr-0.5 custom-scrollbar" 
                        role="listbox" 
                        aria-label="Internal offices list"
                    >
                        <template x-for="office in filteredOffices" :key="office.id">
                            <div 
                                @click="selectOffice(office.id)"
                                :class="selectedOfficeId === office.id 
                                    ? 'bg-blue-50/70 dark:bg-blue-950/40 border-[#0B5694] dark:border-blue-500 ring-1 ring-[#0B5694]/20' 
                                    : 'bg-slate-50/70 dark:bg-gray-800/40 hover:bg-slate-100/70 dark:hover:bg-gray-800/80 border-slate-200/60 dark:border-gray-700/60'"
                                class="p-3 rounded-xl border transition-all cursor-pointer flex items-center justify-between gap-3 text-left focus:outline-none"
                                role="option"
                                :aria-selected="selectedOfficeId === office.id"
                            >
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-1.5 mb-0.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                                        <h4 
                                            class="text-xs font-bold truncate"
                                            :class="selectedOfficeId === office.id ? 'text-[#0B5694] dark:text-blue-400' : 'text-slate-800 dark:text-gray-200'"
                                            x-text="office.name"
                                        ></h4>
                                    </div>
                                    <p class="text-[11px] text-slate-500 dark:text-gray-400 font-medium truncate" x-text="office.location_str"></p>
                                </div>
                                <div class="shrink-0 flex items-center gap-1.5 text-[10px] font-semibold text-slate-500 dark:text-gray-400">
                                    <span x-text="office.interns_count + ' interns'"></span>
                                </div>
                            </div>
                        </template>

                        <template x-if="filteredOffices.length === 0">
                            <div class="p-4 text-center text-xs text-slate-400 dark:text-gray-500 font-medium">
                                No units match your search.
                            </div>
                        </template>
                    </div>
                </div>

                {{-- 3. RECENT BOUNDARY ACTIVITY (Section H - Real audit data) --}}
                @if(isset($recentBoundaryActivity) && $recentBoundaryActivity->isNotEmpty())
                    <div class="p-4 rounded-[22px] bg-white dark:bg-gray-900 border border-slate-200/80 dark:border-gray-800 shadow-sm flex flex-col gap-3">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-gray-500">Recent Boundary Activity</span>
                            <a href="{{ route('admin.security') }}" class="text-[11px] font-semibold text-[#0B5694] dark:text-blue-400 hover:underline">
                                Security Monitor →
                            </a>
                        </div>
                        <div class="space-y-2">
                            @foreach($recentBoundaryActivity as $activity)
                                <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-gray-800/50 border border-slate-200/60 dark:border-gray-700/60 flex items-start gap-2.5 text-xs">
                                    <div class="p-1.5 rounded-lg shrink-0 {{ $activity->action === 'Geofence Violation' ? 'bg-amber-100 dark:bg-amber-950/50 text-amber-700 dark:text-amber-400' : 'bg-blue-100 dark:bg-blue-950/50 text-[#0B5694] dark:text-blue-400' }}">
                                        @if($activity->action === 'Geofence Violation')
                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                                        @else
                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/></svg>
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center justify-between gap-1 mb-0.5">
                                            <span class="font-bold text-slate-800 dark:text-gray-200 truncate">{{ $activity->action }}</span>
                                            <span class="text-[10px] text-slate-400 dark:text-gray-500 shrink-0">{{ $activity->timestamp->diffForHumans() }}</span>
                                        </div>
                                        <p class="text-[11px] text-slate-500 dark:text-gray-400 truncate">{{ $activity->resource ?? ($activity->user->email ?? 'System') }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </aside>

            {{-- MAP REGION (Full height container, fills remaining desktop width or mobile space) --}}
            <main 
                id="map-region-container"
                class="flex-1 min-h-[380px] lg:min-h-0 h-full rounded-[24px] sm:rounded-[28px] overflow-hidden border border-slate-200/80 dark:border-gray-800 bg-slate-100 dark:bg-gray-900 relative shadow-sm"
                role="region"
                aria-label="Interactive division geofence map"
            >
                {{-- Leaflet Map Element --}}
                <div id="main-map" class="w-full h-full z-0"></div>

                {{-- FLOATING MAP CONTROLS (Recenter, Fullscreen) --}}
                <div class="absolute top-4 right-4 z-[400] flex flex-col gap-2">
                    {{-- Recenter Button --}}
                    <button 
                        type="button" 
                        @click="recenter()"
                        class="w-10 h-10 rounded-xl bg-white/95 dark:bg-gray-900/95 backdrop-blur-md border border-slate-200/80 dark:border-gray-800 text-slate-700 dark:text-gray-200 shadow-md flex items-center justify-center hover:bg-slate-50 dark:hover:bg-gray-800 hover:text-[#0B5694] dark:hover:text-blue-400 transition-all active:scale-95 cursor-pointer"
                        title="Recenter on division premises"
                        aria-label="Recenter map"
                    >
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3"/><line x1="12" y1="2" x2="12" y2="5"/><line x1="12" y1="19" x2="12" y2="22"/><line x1="2" y1="12" x2="5" y2="12"/><line x1="19" y1="12" x2="22" y2="12"/></svg>
                    </button>

                    {{-- Fullscreen Toggle --}}
                    <button 
                        type="button" 
                        @click="toggleFullscreen()"
                        class="w-10 h-10 rounded-xl bg-white/95 dark:bg-gray-900/95 backdrop-blur-md border border-slate-200/80 dark:border-gray-800 text-slate-700 dark:text-gray-200 shadow-md flex items-center justify-center hover:bg-slate-50 dark:hover:bg-gray-800 hover:text-[#0B5694] dark:hover:text-blue-400 transition-all active:scale-95 cursor-pointer"
                        :title="isFullscreen ? 'Exit fullscreen' : 'View fullscreen'"
                        aria-label="Toggle fullscreen"
                    >
                        <template x-if="!isFullscreen">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 3 21 3 21 9"/><polyline points="9 21 3 21 3 15"/><line x1="21" y1="3" x2="14" y2="10"/><line x1="3" y1="21" x2="10" y2="14"/></svg>
                        </template>
                        <template x-if="isFullscreen">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="4 14 10 14 10 20"/><polyline points="20 10 14 10 14 4"/><line x1="14" y1="10" x2="21" y2="3"/><line x1="3" y1="21" x2="10" y2="14"/></svg>
                        </template>
                    </button>
                </div>

                {{-- TABLET & MOBILE FLOATING PILL (Opens side panel drawer on medium screens) --}}
                <div class="lg:hidden absolute top-4 left-4 z-[400]">
                    <button 
                        type="button" 
                        @click="mobileDrawerOpen = true"
                        class="px-4 py-2 rounded-xl bg-white/95 dark:bg-gray-900/95 backdrop-blur-md border border-slate-200/80 dark:border-gray-800 text-slate-800 dark:text-white text-xs font-bold shadow-lg flex items-center gap-2 hover:bg-slate-50 active:scale-95 transition-all cursor-pointer"
                        aria-label="Open division geofence drawer"
                    >
                        <svg class="w-4 h-4 text-[#0B5694] dark:text-blue-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                        <span>Division Geofence</span>
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    </button>
                </div>

                {{-- TOUCH DEVICE TWO-FINGER HINT TOAST --}}
                <div 
                    x-show="showTouchHint" 
                    x-cloak
                    x-transition:enter="transition ease-out duration-200" 
                    x-transition:enter-start="opacity-0 translate-y-2" 
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="absolute bottom-6 left-1/2 -translate-x-1/2 z-[400] px-4 py-2 rounded-full bg-slate-900/90 text-white text-xs font-semibold shadow-xl backdrop-blur-md flex items-center gap-2 pointer-events-none"
                    role="alert"
                >
                    <svg class="w-4 h-4 text-blue-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M8 12h8"/><path d="M12 8v8"/></svg>
                    <span>Use two fingers to move the map</span>
                </div>
            </main>
        </div>

        {{-- MOBILE & TABLET DRAWER (Screens < 1024px) --}}
        <div 
            x-show="mobileDrawerOpen" 
            x-cloak
            class="lg:hidden fixed inset-0 z-[120] flex"
            role="dialog"
            aria-modal="true"
            aria-labelledby="drawer-title"
        >
            {{-- Backdrop --}}
            <div 
                x-show="mobileDrawerOpen"
                x-transition:enter="transition-opacity ease-linear duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity ease-linear duration-300"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"
                @click="mobileDrawerOpen = false"
            ></div>

            {{-- Drawer Content Panel --}}
            <div 
                x-show="mobileDrawerOpen"
                x-transition:enter="transition ease-in-out duration-300 transform"
                x-transition:enter-start="-translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transition ease-in-out duration-300 transform"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="-translate-x-full"
                class="relative w-full max-w-sm bg-white dark:bg-gray-900 h-full p-5 overflow-y-auto flex flex-col gap-4 shadow-2xl z-10"
                @keydown.escape.window="mobileDrawerOpen = false"
            >
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-gray-800">
                    <h2 id="drawer-title" class="text-sm font-bold uppercase tracking-wider text-slate-900 dark:text-white">Division Geofence</h2>
                    <button 
                        type="button" 
                        @click="mobileDrawerOpen = false"
                        class="p-1.5 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-gray-200 hover:bg-slate-100 dark:hover:bg-gray-800 cursor-pointer"
                        aria-label="Close drawer"
                    >
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>

                {{-- Division Card for Drawer --}}
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-gray-800 border border-slate-200/80 dark:border-gray-700 space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-[#0B5694] dark:text-blue-400">Division Perimeter</span>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white">DepEd Division Office</h3>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200">Active</span>
                    </div>
                    <p class="text-xs text-slate-600 dark:text-gray-300 font-medium" x-text="divisionData.address || (divisionData.latitude.toFixed(5) + ', ' + divisionData.longitude.toFixed(5))"></p>
                    <div class="flex items-center justify-between text-xs pt-2 border-t border-slate-200 dark:border-gray-700">
                        <span class="font-bold text-slate-700 dark:text-gray-300" x-text="'Radius: ' + divisionData.radius_meters + 'm'"></span>
                        <button 
                            type="button" 
                            @click="mobileDrawerOpen = false; openEdit()"
                            class="px-3 py-1 rounded-lg bg-[#0B5694] text-white text-xs font-semibold cursor-pointer"
                        >
                            Edit
                        </button>
                    </div>
                </div>

                {{-- Directory List for Drawer --}}
                <div class="space-y-2 flex-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Offices In Facility</span>
                    <div class="space-y-1.5">
                        <template x-for="office in filteredOffices" :key="'drawer-' + office.id">
                            <button 
                                type="button" 
                                @click="selectOffice(office.id); mobileDrawerOpen = false"
                                class="w-full p-3 rounded-xl border text-left flex items-center justify-between gap-2 transition-all cursor-pointer"
                                :class="selectedOfficeId === office.id ? 'bg-blue-50 dark:bg-blue-950/40 border-[#0B5694]' : 'bg-slate-50 dark:bg-gray-800 border-slate-200 dark:border-gray-700'"
                            >
                                <div class="min-w-0 flex-1">
                                    <h4 class="text-xs font-bold text-slate-800 dark:text-gray-200 truncate" x-text="office.name"></h4>
                                    <span class="text-[10px] text-slate-400 truncate block" x-text="office.location_str"></span>
                                </div>
                                <span class="text-[10px] font-semibold text-slate-500" x-text="office.interns_count + ' interns'"></span>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- EDIT SLIDE-OVER (Unified Division Geofence Boundary)                      --}}
        {{-- All offices belong to one facility: editing updates the shared boundary   --}}
        {{-- ========================================================================= --}}
        <div 
            x-show="editOpen" 
            x-cloak
            class="fixed inset-0 z-[130] flex justify-end"
            role="dialog"
            aria-modal="true"
            aria-labelledby="edit-slideover-title"
            @keydown.escape.window="handleCancelEdit()"
        >
            {{-- Backdrop --}}
            <div 
                x-show="editOpen"
                x-transition:enter="transition-opacity ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"
                @click="handleCancelEdit()"
            ></div>

            {{-- Slide-over Panel --}}
            <div 
                x-show="editOpen"
                x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="translate-x-full"
                class="relative w-full sm:w-[460px] md:w-[480px] bg-white dark:bg-gray-900 h-full shadow-2xl flex flex-col z-10 border-l border-slate-200 dark:border-gray-800"
            >
                {{-- Header --}}
                <div class="p-5 sm:p-6 border-b border-slate-100 dark:border-gray-800 flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-blue-50 dark:bg-blue-900/30 text-[#0B5694] dark:text-blue-400 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/><circle cx="12" cy="11" r="3"/></svg>
                        </div>
                        <div>
                            <h2 id="edit-slideover-title" class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-white leading-tight">
                                Edit Division Geofence
                            </h2>
                            <p class="text-xs text-slate-500 dark:text-gray-400 font-medium mt-0.5">
                                Positional boundary for Division Office building
                            </p>
                        </div>
                    </div>
                    <button 
                        type="button" 
                        @click="handleCancelEdit()"
                        class="p-2 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-gray-200 hover:bg-slate-100 dark:hover:bg-gray-800 transition-colors cursor-pointer"
                        aria-label="Close edit slide-over"
                    >
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>

                {{-- Slide-over Body: Scrollable Form --}}
                <div class="flex-1 overflow-y-auto p-5 sm:p-6 space-y-5 custom-scrollbar">
                    
                    {{-- Shared Facility Info Banner --}}
                    <div class="p-3.5 rounded-xl bg-blue-50/70 dark:bg-blue-950/30 border border-blue-200/60 dark:border-blue-900/40 flex items-start gap-2.5 text-xs text-blue-900 dark:text-blue-300">
                        <svg class="w-4 h-4 text-[#0B5694] dark:text-blue-400 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                        <span>All {{ $offices->count() }} offices are located in this single division facility. Updating the coordinates or radius refines the verification perimeter across all internal units.</span>
                    </div>

                    {{-- Address Search with Server-Side Autocomplete --}}
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider block mb-2">
                            Search Place or Address
                        </label>
                        <div class="relative">
                            <input 
                                type="text" 
                                x-model="editSearchQuery"
                                @input.debounce.400ms="executeSearchLocation()"
                                @keydown.enter.prevent="executeSearchLocation()"
                                placeholder="Search building, street, or city..."
                                class="w-full bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl pl-4 pr-10 py-3 text-xs sm:text-sm font-medium text-slate-800 dark:text-gray-200 focus:ring-2 focus:ring-[#0B5694] outline-none transition-all"
                            >
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">
                                <template x-if="!editSearching">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                                </template>
                                <template x-if="editSearching">
                                    <svg class="w-4 h-4 animate-spin text-[#0B5694]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                                </template>
                            </div>
                        </div>

                        {{-- Search Autocomplete Dropdown --}}
                        <div 
                            x-show="editSearchResults.length > 0" 
                            x-cloak
                            class="mt-1.5 p-1 rounded-xl bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 shadow-xl max-h-48 overflow-y-auto z-20 space-y-1 custom-scrollbar"
                        >
                            <template x-for="(res, rIdx) in editSearchResults" :key="rIdx">
                                <button 
                                    type="button" 
                                    @click="selectSearchResult(res)"
                                    class="w-full p-2.5 rounded-lg text-left text-xs font-medium text-slate-700 dark:text-gray-300 hover:bg-slate-100 dark:hover:bg-gray-700 flex items-start gap-2 transition-colors cursor-pointer"
                                >
                                    <svg class="w-4 h-4 text-[#0B5694] shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                                    <span class="truncate" x-text="res.display_name"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    {{-- Live Preview Mini Map --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider block">
                                Perimeter Center (Drag marker to reposition)
                            </label>
                            <span class="text-[10px] text-blue-600 dark:text-blue-400 font-semibold">Live Preview</span>
                        </div>
                        <div class="h-44 sm:h-52 rounded-2xl overflow-hidden border border-slate-200 dark:border-gray-700 relative shadow-inner">
                            <div id="edit-map" class="w-full h-full z-0"></div>
                        </div>
                        <p class="text-[11px] text-slate-400 dark:text-gray-500 mt-1 font-medium flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                            <span>Click on map or drag the center pin to adjust division coordinates.</span>
                        </p>
                    </div>

                    {{-- Form targeting global update --}}
                    <form 
                        id="geofence-update-form" 
                        action="{{ route('admin.offices.geofence.update') }}" 
                        method="POST" 
                        @submit.prevent="prepareConfirmation()"
                    >
                        @csrf
                        @method('PUT')

                        {{-- Radius Slider Paired with Numeric Input (min: 10, max: 5000) --}}
                        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-gray-800/80 border border-slate-200/80 dark:border-gray-700 space-y-3">
                            <div class="flex items-center justify-between">
                                <label class="text-[10px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-wider">
                                    Allowed Geofence Radius
                                </label>
                                <div class="flex items-center gap-1">
                                    <input 
                                        type="number" 
                                        name="radius_meters" 
                                        x-model.number="editForm.radius_meters"
                                        @input="updateFromEditInputs()"
                                        min="10" 
                                        max="5000" 
                                        step="5"
                                        required
                                        class="w-20 px-2 py-1 text-right text-sm font-extrabold text-[#0B5694] dark:text-blue-400 bg-white dark:bg-gray-900 border border-slate-200 dark:border-gray-700 rounded-lg focus:ring-1 focus:ring-[#0B5694] outline-none"
                                    >
                                    <span class="text-xs font-semibold text-slate-500">m</span>
                                </div>
                            </div>

                            {{-- Range Slider --}}
                            <input 
                                type="range" 
                                min="10" 
                                max="5000" 
                                step="5"
                                x-model.number="editForm.radius_meters"
                                @input="updateFromEditInputs()"
                                class="w-full accent-[#0B5694] cursor-pointer"
                                aria-label="Radius in meters"
                            >

                            <div class="flex justify-between text-[10px] font-medium text-slate-400">
                                <span>10m (Tight)</span>
                                <span>100m (Standard)</span>
                                <span>500m</span>
                                <span>5000m (Max)</span>
                            </div>
                        </div>

                        {{-- Coordinates (Lat / Lng) --}}
                        <div class="grid grid-cols-2 gap-3 mt-4">
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider block mb-1.5">
                                    Latitude
                                </label>
                                <input 
                                    type="number" 
                                    step="0.00000001" 
                                    name="latitude" 
                                    x-model="editForm.latitude" 
                                    @input="updateFromEditInputs()"
                                    required 
                                    class="w-full bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-xs font-mono font-semibold text-slate-800 dark:text-gray-200 focus:ring-2 focus:ring-[#0B5694] outline-none transition-all"
                                >
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider block mb-1.5">
                                    Longitude
                                </label>
                                <input 
                                    type="number" 
                                    step="0.00000001" 
                                    name="longitude" 
                                    x-model="editForm.longitude" 
                                    @input="updateFromEditInputs()"
                                    required 
                                    class="w-full bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-xs font-mono font-semibold text-slate-800 dark:text-gray-200 focus:ring-2 focus:ring-[#0B5694] outline-none transition-all"
                                >
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="pt-4 flex items-center gap-3">
                            <button 
                                type="submit" 
                                class="flex-1 h-11 px-5 rounded-xl bg-[#0B5694] hover:bg-[#084577] dark:bg-blue-600 dark:hover:bg-blue-700 text-white font-semibold text-xs sm:text-sm flex items-center justify-center gap-2 transition-all shadow-md active:scale-95 cursor-pointer"
                            >
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                <span>Review & Save</span>
                            </button>
                            <button 
                                type="button" 
                                @click="handleCancelEdit()"
                                class="h-11 px-5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-slate-600 dark:text-gray-300 font-semibold text-xs sm:text-sm transition-all cursor-pointer"
                            >
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- CONFIRMATION MODAL --}}
        <div 
            x-show="showConfirmationModal" 
            x-cloak
            class="fixed inset-0 z-[140] flex items-center justify-center p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="confirm-modal-title"
        >
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showConfirmationModal = false"></div>

            <div 
                x-show="showConfirmationModal"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                class="relative w-full max-w-md bg-white dark:bg-gray-900 rounded-[24px] p-6 shadow-2xl border border-slate-200 dark:border-gray-800 space-y-4 z-10"
            >
                <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                </div>

                <div>
                    <h3 id="confirm-modal-title" class="text-lg font-extrabold text-slate-900 dark:text-white">
                        Confirm Division Geofence Update
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-gray-400 mt-1 leading-relaxed">
                        This update applies to the entire Division Office building and affects attendance verification for all {{ $offices->count() }} internal units.
                    </p>
                </div>

                {{-- Difference Summary --}}
                <div class="p-4 rounded-xl bg-slate-50 dark:bg-gray-800/80 border border-slate-200/80 dark:border-gray-700 space-y-2.5 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400 font-medium">Scope:</span>
                        <span class="font-bold text-slate-800 dark:text-gray-200">Division Office Premises (All Units)</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400 font-medium">Radius:</span>
                        <div class="flex items-center gap-1.5 font-bold">
                            <span class="text-slate-500 line-through" x-text="editOriginalData.radius_meters + 'm'"></span>
                            <span class="text-[#0B5694] dark:text-blue-400" x-text="'→ ' + editForm.radius_meters + 'm'"></span>
                        </div>
                    </div>
                    <div class="flex items-start justify-between gap-2">
                        <span class="text-slate-400 font-medium">Coordinates:</span>
                        <span class="font-mono font-semibold text-right text-slate-700 dark:text-gray-300" x-text="parseFloat(editForm.latitude).toFixed(5) + ', ' + parseFloat(editForm.longitude).toFixed(5)"></span>
                    </div>
                </div>

                {{-- Confirmation Buttons --}}
                <div class="flex items-center gap-3 pt-2">
                    <button 
                        type="button" 
                        @click="submitGeofenceForm()"
                        class="flex-1 h-11 px-5 rounded-xl bg-[#0B5694] hover:bg-[#084577] text-white font-semibold text-xs sm:text-sm flex items-center justify-center gap-2 transition-all shadow-md active:scale-95 cursor-pointer"
                    >
                        <span>Confirm & Apply</span>
                    </button>
                    <button 
                        type="button" 
                        @click="showConfirmationModal = false"
                        class="h-11 px-5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-slate-600 dark:text-gray-300 font-semibold text-xs sm:text-sm transition-all cursor-pointer"
                    >
                        Go Back
                    </button>
                </div>
            </div>
        </div>

    </div>
</x-page-layout>

{{-- SCRIPT: Alpine Component & Interactive Leaflet Handler --}}
<script>
function geofenceInspector(initialOffices, divisionLat, divisionLng, divisionRadius, initialAddress) {
    return {
        offices: initialOffices,
        selectedOfficeId: initialOffices.length > 0 ? initialOffices[0].id : null,
        searchQuery: '',
        loadingAddress: false,
        copiedCoords: false,
        mobileDrawerOpen: false,
        isFullscreen: false,
        showTouchHint: false,
        touchHintTimer: null,

        // Division Geofence Center (Unified boundary)
        divisionData: {
            latitude: divisionLat || 14.6150485,
            longitude: divisionLng || 121.0105983,
            radius_meters: divisionRadius || 100,
            address: initialAddress || null,
        },

        // Leaflet Map objects
        mainMap: null,
        divisionCircle: null,
        divisionMarker: null,

        // Edit slide-over state
        editOpen: false,
        showConfirmationModal: false,
        editMap: null,
        editMarker: null,
        editCircle: null,
        editSearchQuery: '',
        editSearching: false,
        editSearchResults: [],
        editOriginalData: {},
        editForm: {
            latitude: '',
            longitude: '',
            radius_meters: 100,
        },

        get selectedOffice() {
            return this.offices.find(o => o.id === this.selectedOfficeId) || this.offices[0] || null;
        },

        get filteredOffices() {
            if (!this.searchQuery.trim()) return this.offices;
            const q = this.searchQuery.toLowerCase();
            return this.offices.filter(o => 
                (o.name && o.name.toLowerCase().includes(q)) || 
                (o.location_str && o.location_str.toLowerCase().includes(q))
            );
        },

        init() {
            this.$nextTick(() => {
                this.initMainMap();
                if (!this.divisionData.address) {
                    this.fetchDivisionAddress();
                }
            });

            window.addEventListener('resize', () => {
                if (this.mainMap) {
                    this.mainMap.invalidateSize();
                }
            });

            document.addEventListener('fullscreenchange', () => {
                this.isFullscreen = !!document.fullscreenElement;
                setTimeout(() => {
                    if (this.mainMap) this.mainMap.invalidateSize();
                }, 200);
            });
        },

        initMainMap() {
            if (typeof L === 'undefined') {
                setTimeout(() => this.initMainMap(), 60);
                return;
            }

            const lat = this.divisionData.latitude;
            const lng = this.divisionData.longitude;
            const rad = this.divisionData.radius_meters;

            this.mainMap = L.map('main-map', {
                center: [lat, lng],
                zoom: 17,
                scrollWheelZoom: false,
                touchZoom: true,
                zoomControl: true,
            });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a> contributors',
                maxZoom: 19,
            }).addTo(this.mainMap);

            this.mainMap.on('focus', () => { this.mainMap.scrollWheelZoom.enable(); });
            this.mainMap.on('blur', () => { this.mainMap.scrollWheelZoom.disable(); });

            // Touch scroll-trap prevention
            const mapEl = document.getElementById('main-map');
            if (mapEl) {
                mapEl.addEventListener('touchstart', (e) => {
                    if (e.touches.length === 1) {
                        this.triggerTouchHint();
                    }
                }, { passive: true });
            }

            // Draw single unified division circle
            this.divisionCircle = L.circle([lat, lng], {
                radius: rad,
                color: '#0B5694',
                weight: 2.5,
                opacity: 0.9,
                fillColor: '#0B5694',
                fillOpacity: 0.18,
            }).addTo(this.mainMap);

            this.divisionCircle.bindTooltip(`${rad}m`, {
                permanent: false,
                direction: 'top',
                className: 'geofence-radius-tooltip',
            });

            // Center marker
            this.divisionMarker = L.marker([lat, lng]).addTo(this.mainMap);
            this.divisionMarker.bindPopup(`<b>DepEd Division Office</b><br><span style="font-size:11px;color:#666">Houses ${this.offices.length} internal administrative units</span><br><b>Radius:</b> ${rad}m`);

            this.mainMap.fitBounds(this.divisionCircle.getBounds().pad(0.2));

            setTimeout(() => {
                if (this.mainMap) this.mainMap.invalidateSize();
            }, 300);
        },

        selectOffice(id) {
            this.selectedOfficeId = id;
            const off = this.selectedOffice;
            if (off && this.divisionMarker) {
                this.divisionMarker.setPopupContent(`<b>${off.name}</b><br><span style="font-size:11px;color:#666">${off.location_str}</span><br><b>Division Boundary:</b> ${this.divisionData.radius_meters}m`);
                this.divisionMarker.openPopup();
            }
        },

        async fetchDivisionAddress(force = false) {
            if (this.divisionData.address && !force) return;

            this.loadingAddress = true;
            try {
                const response = await fetch(`/admin/geofence/reverse-geocode?latitude=${encodeURIComponent(this.divisionData.latitude)}&longitude=${encodeURIComponent(this.divisionData.longitude)}`, {
                    headers: { 'Accept': 'application/json' }
                });
                if (response.ok) {
                    const data = await response.json();
                    this.divisionData.address = data.address || null;
                } else {
                    this.divisionData.address = null;
                }
            } catch (e) {
                this.divisionData.address = null;
            } finally {
                this.loadingAddress = false;
            }
        },

        copyCoordinates() {
            const text = `${this.divisionData.latitude.toFixed(5)}, ${this.divisionData.longitude.toFixed(5)}`;
            navigator.clipboard.writeText(text).then(() => {
                this.copiedCoords = true;
                setTimeout(() => { this.copiedCoords = false; }, 2000);
            });
        },

        recenter() {
            if (!this.mainMap) return;
            this.mainMap.setView([this.divisionData.latitude, this.divisionData.longitude], 17);
        },

        toggleFullscreen() {
            const container = document.getElementById('map-region-container');
            if (!container) return;

            if (!document.fullscreenElement) {
                container.requestFullscreen().catch(() => {});
            } else {
                document.exitFullscreen().catch(() => {});
            }
        },

        triggerTouchHint() {
            this.showTouchHint = true;
            clearTimeout(this.touchHintTimer);
            this.touchHintTimer = setTimeout(() => {
                this.showTouchHint = false;
            }, 1800);
        },

        // ==========================================
        // EDIT SLIDE-OVER FLOW (Division Geofence)
        // ==========================================
        openEdit() {
            this.editForm.latitude = this.divisionData.latitude;
            this.editForm.longitude = this.divisionData.longitude;
            this.editForm.radius_meters = this.divisionData.radius_meters;

            // Save original state so Cancel can restore
            this.editOriginalData = {
                latitude: this.editForm.latitude,
                longitude: this.editForm.longitude,
                radius_meters: this.editForm.radius_meters,
            };

            this.editSearchQuery = '';
            this.editSearchResults = [];
            this.editOpen = true;

            this.$nextTick(() => {
                this.initEditMap();
            });
        },

        initEditMap() {
            const lat = parseFloat(this.editForm.latitude) || 14.6150485;
            const lng = parseFloat(this.editForm.longitude) || 121.0105983;
            const radius = parseInt(this.editForm.radius_meters) || 100;

            if (!this.editMap) {
                this.editMap = L.map('edit-map', {
                    center: [lat, lng],
                    zoom: 16,
                    attributionControl: false,
                });

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                }).addTo(this.editMap);

                this.editMarker = L.marker([lat, lng], { draggable: true }).addTo(this.editMap);
                this.editCircle = L.circle([lat, lng], {
                    radius: radius,
                    color: '#0B5694',
                    fillColor: '#0B5694',
                    fillOpacity: 0.25,
                    weight: 2.5,
                }).addTo(this.editMap);

                this.editMarker.on('dragend', (e) => {
                    const pos = e.target.getLatLng();
                    this.editForm.latitude = pos.lat.toFixed(8);
                    this.editForm.longitude = pos.lng.toFixed(8);
                    this.editCircle.setLatLng(pos);
                    this.liveUpdateMainMap();
                });

                this.editMap.on('click', (e) => {
                    const pos = e.latlng;
                    this.editForm.latitude = pos.lat.toFixed(8);
                    this.editForm.longitude = pos.lng.toFixed(8);
                    this.editMarker.setLatLng(pos);
                    this.editCircle.setLatLng(pos);
                    this.liveUpdateMainMap();
                });
            } else {
                this.editMap.setView([lat, lng], 16);
                if (this.editMarker) this.editMarker.setLatLng([lat, lng]);
                if (this.editCircle) {
                    this.editCircle.setLatLng([lat, lng]);
                    this.editCircle.setRadius(radius);
                }
            }

            setTimeout(() => {
                if (this.editMap) this.editMap.invalidateSize();
            }, 250);
        },

        updateFromEditInputs() {
            const lat = parseFloat(this.editForm.latitude);
            const lng = parseFloat(this.editForm.longitude);
            const radius = parseInt(this.editForm.radius_meters);

            if (!isNaN(lat) && !isNaN(lng)) {
                if (this.editMarker) this.editMarker.setLatLng([lat, lng]);
                if (this.editCircle) this.editCircle.setLatLng([lat, lng]);
                if (this.editMap) this.editMap.panTo([lat, lng]);
            }

            if (!isNaN(radius) && this.editCircle) {
                this.editCircle.setRadius(radius);
            }

            this.liveUpdateMainMap();
        },

        liveUpdateMainMap() {
            const lat = parseFloat(this.editForm.latitude);
            const lng = parseFloat(this.editForm.longitude);
            const rad = parseInt(this.editForm.radius_meters);

            if (!isNaN(lat) && !isNaN(lng) && !isNaN(rad)) {
                if (this.divisionCircle) {
                    this.divisionCircle.setLatLng([lat, lng]);
                    this.divisionCircle.setRadius(rad);
                    this.divisionCircle.setTooltipContent(`${rad}m`);
                }
                if (this.divisionMarker) {
                    this.divisionMarker.setLatLng([lat, lng]);
                }
            }
        },

        async executeSearchLocation() {
            const q = this.editSearchQuery.trim();
            if (q.length < 3) {
                this.editSearchResults = [];
                return;
            }

            this.editSearching = true;
            try {
                const response = await fetch(`/admin/geofence/search-location?query=${encodeURIComponent(q)}`, {
                    headers: { 'Accept': 'application/json' }
                });
                if (response.ok) {
                    const data = await response.json();
                    this.editSearchResults = data.results || [];
                }
            } catch (e) {
                this.editSearchResults = [];
            } finally {
                this.editSearching = false;
            }
        },

        selectSearchResult(result) {
            this.editForm.latitude = result.latitude.toFixed(8);
            this.editForm.longitude = result.longitude.toFixed(8);
            this.editSearchResults = [];
            this.editSearchQuery = result.display_name;
            this.updateFromEditInputs();
            if (this.editMap) {
                this.editMap.setView([result.latitude, result.longitude], 17);
            }
        },

        handleCancelEdit() {
            if (this.editOriginalData && this.editOriginalData.latitude) {
                const lat = this.editOriginalData.latitude;
                const lng = this.editOriginalData.longitude;
                const rad = this.editOriginalData.radius_meters;

                if (this.divisionCircle) {
                    this.divisionCircle.setLatLng([lat, lng]);
                    this.divisionCircle.setRadius(rad);
                    this.divisionCircle.setTooltipContent(`${rad}m`);
                }
                if (this.divisionMarker) {
                    this.divisionMarker.setLatLng([lat, lng]);
                }
            }
            this.editOpen = false;
            this.showConfirmationModal = false;
        },

        prepareConfirmation() {
            this.showConfirmationModal = true;
        },

        submitGeofenceForm() {
            const form = document.getElementById('geofence-update-form');
            if (form) {
                form.submit();
            }
        }
    };
}
</script>

<style>
/* Custom Tooltip Styling for Leaflet */
.geofence-radius-tooltip {
    background: #0B5694 !important;
    border: none !important;
    color: white !important;
    font-size: 10px !important;
    font-weight: 700 !important;
    border-radius: 9999px !important;
    padding: 2px 8px !important;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
}
.geofence-radius-tooltip::before {
    display: none !important;
}
</style>
@endsection
