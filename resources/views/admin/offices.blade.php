@extends('layouts.app')

@section('title', 'Office Geofencing')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<x-page-layout 
    portalType="Admin" 
    userName="{{ Auth::user()->email }}" 
    userRole="System Administrator"
    fullHeight="true"
>
    <div x-data="geofenceManager({{ json_encode($offices) }})" class="flex flex-col flex-1 min-h-0">
        {{-- Header --}}
        <x-page-header 
            title="Geofence Overview" 
            subtitle="Office Boundaries & Positional Verification" 
        >
            <x-slot name="actions">
                <button type="button" 
                        @click="geofenceOfficeOpen = true; geofenceData = {
                            latitude: '{{ $offices->first()->latitude ?? '' }}', 
                            longitude: '{{ $offices->first()->longitude ?? '' }}', 
                            radius_meters: '{{ $offices->first()->radius_meters ?? 100 }}'
                        }; initEditMap();"
                        class="w-full sm:w-auto bg-[#0B5694] dark:bg-blue-600 text-white px-6 min-h-[44px] rounded-xl text-[10px] font-bold uppercase tracking-wider shadow-lg hover:bg-[#084577] dark:hover:bg-blue-700 transition active:scale-95 flex items-center justify-center gap-2 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/></svg>
                    Update Global Geofence
                </button>
            </x-slot>
        </x-page-header>

        {{-- SKELETON LOADING VIEW --}}
        <div x-show="loading" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="flex-1 bg-white dark:bg-gray-900 rounded-[20px] sm:rounded-[30px] shadow-sm border border-gray-100 dark:border-gray-800 p-1.5 sm:p-2 overflow-hidden flex flex-col">
            <x-skeleton type="block" class="w-full h-full flex-1 rounded-[20px] sm:rounded-[25px]" />
        </div>

        {{-- Main Map Container (ACTUAL CONTENT) --}}
        <div x-show="!loading" x-cloak x-transition:enter="transition ease-out duration-500 delay-200" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="flex-1 bg-white dark:bg-gray-900 rounded-[30px] shadow-sm border border-gray-100 dark:border-gray-800 p-2 overflow-hidden relative group">
            <div id="main-map" class="w-full h-full rounded-[25px] z-0"></div>
            
            {{-- Quick Stats Overlay --}}
            <div class="absolute bottom-6 left-6 right-6 z-[40]">
                {{-- Location Card --}}
                <div class="bg-white/95 dark:bg-gray-900/95 backdrop-blur-xl p-5 rounded-[28px] border-2 border-blue-500/10 dark:border-blue-500/20 shadow-2xl max-w-full sm:max-w-[450px] animate-in slide-in-from-left-4 duration-500">
                    <div class="flex items-start gap-4">
                        <div class="p-3 bg-[#0B5694] dark:bg-blue-600 text-white rounded-2xl shadow-lg shadow-blue-500/30 shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                        </div>
                        <div class="flex-1 min-w-0 text-left">
                            <p class="text-[10px] font-bold text-[#0B5694] dark:text-blue-400 uppercase tracking-wider mb-1.5">Division Geofence Center</p>
                            <h5 class="text-[13px] sm:text-[14px] text-gray-900 dark:text-white leading-snug tracking-normal font-extrabold uppercase" x-text="geofenceData.address"></h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Geofence Configuration Modal --}}
        <div x-show="geofenceOfficeOpen" 
             x-cloak
             class="fixed inset-0 z-[110] flex items-center justify-center p-4 sm:p-6"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="geofenceOfficeOpen = false"></div>
            
            <div class="relative bg-white dark:bg-gray-900 w-full max-w-4xl rounded-[35px] shadow-2xl p-6 sm:p-8 lg:p-12 border border-gray-100 dark:border-gray-800 transition-all max-h-[90vh] overflow-y-auto custom-scrollbar">
                <header class="mb-8 lg:mb-10 flex items-center gap-4">
                    <div class="p-3 bg-blue-50 dark:bg-blue-900/30 text-[#0B5694] dark:text-blue-400 rounded-2xl">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                    </div>
                    <div>
                        <h3 class="text-2xl font-extrabold text-gray-800 dark:text-white tracking-normal uppercase">Geofence Settings</h3>
                        <p class="text-[10px] text-gray-400 dark:text-gray-500 font-bold uppercase tracking-widest mt-0.5 italic">Configure boundaries for all offices</p>
                    </div>
                </header>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-10">
                    {{-- Map Container --}}
                    <div class="h-[300px] lg:h-auto min-h-[350px] rounded-[25px] overflow-hidden border border-gray-100 dark:border-gray-800 z-0 relative" id="edit-map">
                        <div class="absolute top-4 left-4 right-4 z-[40]">
                            <div class="relative group">
                                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                                </span>
                                <input type="text" 
                                       @input.debounce.500ms="searchLocation($event.target.value)"
                                       placeholder="Search for a place..." 
                                       class="w-full bg-white/90 dark:bg-gray-900/90 backdrop-blur-md border border-gray-100 dark:border-gray-800 rounded-2xl pl-12 pr-6 py-3 text-xs font-bold focus:ring-2 focus:ring-[#0B5694] outline-none transition-all shadow-xl text-gray-700 dark:text-gray-200">
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('admin.offices.geofence.update') }}" method="POST" class="text-left">
                        @csrf
                        @method('PUT')
                        <div class="space-y-6">
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-3 block">Target Office</label>
                                <select name="office_id" x-model="selectedOfficeId" @change="changeTargetOffice()"
                                        class="w-full bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-2xl px-5 py-4 text-xs font-bold text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-[#0B5694] dark:focus:ring-blue-400 focus:outline-none transition-all shadow-sm uppercase tracking-widest">
                                    <option value="">Apply to All Offices (Global)</option>
                                    <template x-for="off in offices" :key="off.id">
                                        <option :value="off.id" x-text="off.name"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-3 block">Exact Location Name / Address</label>
                                <textarea x-model="geofenceData.address" readonly rows="2"
                                          class="w-full bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-2xl px-5 py-4 text-sm font-bold text-gray-700 dark:text-gray-300 cursor-not-allowed resize-none transition-all shadow-sm"></textarea>
                            </div>
                            <div class="relative group">
                                <label class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-3 block">Search Location / Address</label>
                                <div class="relative">
                                    <input type="text" 
                                           x-model="searchQuery"
                                           @keydown.enter.prevent="searchLocation(searchQuery)"
                                           placeholder="Type address and press Enter..." 
                                           class="w-full bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-2xl pl-6 pr-12 py-4 text-sm font-bold text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-[#0B5694] dark:focus:ring-blue-400 focus:outline-none transition-all shadow-sm">
                                    <button type="button" 
                                            @click="searchLocation(searchQuery)"
                                            class="absolute right-2 top-2 bottom-2 px-3 bg-[#0B5694] text-white rounded-xl hover:bg-[#084577] transition flex items-center justify-center">
                                        <template x-if="!isSearching">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                                        </template>
                                        <template x-if="isSearching">
                                            <svg class="animate-spin" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                                        </template>
                                    </button>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                                <div>
                                    <label class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-3 block">Latitude</label>
                                    <input type="number" step="0.00000001" name="latitude" x-model="geofenceData.latitude" @input="updateFromInputs()" required 
                                           class="w-full bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-2xl px-5 py-4 text-sm font-bold text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-[#0B5694] dark:focus:ring-blue-400 focus:outline-none transition-all">
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-3 block">Longitude</label>
                                    <input type="number" step="0.00000001" name="longitude" x-model="geofenceData.longitude" @input="updateFromInputs()" required 
                                           class="w-full bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-2xl px-5 py-4 text-sm font-bold text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-[#0B5694] dark:focus:ring-blue-400 focus:outline-none transition-all">
                                </div>
                            </div>
                            
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-3 block">Radius (Meters)</label>
                                <input type="number" name="radius_meters" x-model="geofenceData.radius_meters" @input="updateFromInputs()" required 
                                       class="w-full bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-2xl px-5 py-4 text-sm font-bold text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-[#0B5694] dark:focus:ring-blue-400 focus:outline-none transition-all">
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-4 mt-12">
                            <button type="submit" class="flex-1 bg-[#0B5694] dark:bg-blue-600 text-white min-h-[44px] rounded-2xl font-bold text-[11px] uppercase tracking-wider shadow-lg hover:bg-[#084577] dark:hover:bg-blue-700 transition active:scale-95 cursor-pointer">
                                <span x-text="selectedOfficeId ? 'Apply to Selected Office' : 'Apply to All Offices'"></span>
                            </button>
                            <button type="button" @click="geofenceOfficeOpen = false" class="sm:w-32 bg-gray-50 dark:bg-gray-800 text-gray-400 dark:text-gray-500 py-4 rounded-2xl font-bold text-[11px] uppercase tracking-wider hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-page-layout>
@endsection
