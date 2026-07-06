@extends('layouts.app')

@section('title', 'Schedule')

@section('content')
<x-page-layout 
    portalType="Head Supervisor" 
    userName="{{ Auth::user()->supervisorDetails->first_name }}" 
    userRole="Head Supervisor"
>
    <div class="flex-1 text-left transition-all duration-300"
          x-data="calendarManager(JSON.parse('{{ addslashes($schedulesJson) }}'), JSON.parse('{{ addslashes($schoolSchedulesJson) }}'), JSON.parse('{{ addslashes($internsJson) }}'))">
        
        {{-- Header --}}
        <div class="mb-8">
            <x-page-header 
                title="Schedule" 
                subtitle="Configure system-wide calendar overrides for holidays or set school-specific default schedules." 
            />
        </div>

        {{-- Success/Error Messages --}}
        @if(session('success'))
            <div x-data="{ show: true }" 
                 x-show="show" 
                 x-init="setTimeout(() => show = false, 5000)"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform -translate-y-2"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 x-transition:leave="transition ease-in duration-500"
                 x-transition:leave-start="opacity-100 transform translate-y-0"
                 x-transition:leave-end="opacity-0 transform -translate-y-2"
                 class="mb-8 p-4 bg-emerald-50 border border-emerald-200 dark:bg-emerald-950/20 dark:border-emerald-800 text-emerald-600 dark:text-emerald-400 rounded-2xl font-semibold shadow-sm">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button @click="show = false" class="text-emerald-400 hover:text-emerald-600 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div x-data="{ show: true }"
                 x-show="show"
                 x-init="setTimeout(() => show = false, 5000)"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform -translate-y-2"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 x-transition:leave="transition ease-in duration-500"
                 x-transition:leave-start="opacity-100 transform translate-y-0"
                 x-transition:leave-end="opacity-0 transform -translate-y-2"
                 class="mb-8 p-4 bg-rose-50 border border-rose-200 dark:bg-rose-950/20 dark:border-rose-800 text-rose-600 dark:text-rose-400 rounded-2xl font-semibold shadow-sm">
                <div class="flex items-center justify-between">
                    <ul class="list-disc pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button @click="show = false" class="text-rose-400 hover:text-rose-600 transition-colors ml-4 shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
            </div>
        @endif

        {{-- Skeletons --}}
        <div x-show="loading" class="flex flex-col gap-8">
            <div class="bg-white dark:bg-gray-900 rounded-[35px] shadow-sm border border-gray-100 dark:border-gray-800 p-6 lg:p-10 flex flex-col space-y-8">
                <div class="flex justify-between items-center">
                    <x-skeleton class="h-8 w-48 rounded-lg" />
                    <div class="flex gap-2">
                        <x-skeleton class="h-10 w-10 rounded-2xl" />
                        <x-skeleton class="h-10 w-10 rounded-2xl" />
                    </div>
                </div>
                <x-skeleton class="h-[500px] w-full rounded-[25px]" />
            </div>

            {{-- Summary Bar Skeleton --}}
            <div class="w-full bg-white dark:bg-gray-900 rounded-[35px] shadow-sm border border-gray-100 dark:border-gray-800 p-6 sm:p-8 flex flex-col sm:flex-row items-start sm:items-center gap-4 sm:gap-6 justify-between">
                <div class="flex items-center gap-6">
                    <x-skeleton class="w-14 h-14 rounded-2xl shrink-0" />
                    <div class="space-y-2 text-left">
                        <x-skeleton class="h-5 w-32 rounded-md" />
                        <x-skeleton class="h-3 w-48 rounded-md" />
                    </div>
                </div>
                <div class="flex gap-6 sm:gap-10">
                    <div class="space-y-2 text-left">
                        <x-skeleton class="h-3 w-16 rounded-md" />
                        <x-skeleton class="h-4 w-24 rounded-md" />
                    </div>
                    <div class="space-y-2 text-left">
                        <x-skeleton class="h-3 w-16 rounded-md" />
                        <x-skeleton class="h-4 w-24 rounded-md" />
                    </div>
                </div>
            </div>
        </div>

        {{-- MAIN CONTENT GRID --}}
        <div x-show="!loading" x-transition.opacity.duration.500ms class="flex flex-col gap-8 items-start">

            {{-- CONFIGURATION CONTAINER --}}
            <div class="w-full bg-white dark:bg-gray-900 rounded-[25px] sm:rounded-[35px] shadow-sm border border-gray-100 dark:border-gray-800 p-6 sm:p-8 lg:p-10 flex flex-col gap-6">
                <div @click="toggleConfigSection()"
                     class="flex items-center justify-between cursor-pointer select-none">
                    <div class="flex items-center gap-6">
                        <div class="w-14 h-14 bg-blue-50 dark:bg-blue-900/30 text-[#0B5694] dark:text-blue-400 rounded-2xl flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                        </div>
                        <div class="text-left">
                            <h4 class="text-lg font-black text-gray-800 dark:text-white uppercase tracking-tight">Configure Schedules</h4>
                            <p class="text-xs text-gray-400 dark:text-gray-500 font-bold uppercase tracking-widest mt-1">Manage intern assigned days and institutional schedule defaults</p>
                        </div>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"
                         class="text-gray-400 dark:text-gray-500 transition-transform duration-300 shrink-0"
                         :class="{ 'rotate-180': configSectionOpen }">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </div>

                <div class="pt-4 border-t border-gray-100 dark:border-gray-800" x-show="configSectionOpen" x-collapse.duration.300ms x-cloak>
                    {{-- TAB CONTROLS --}}
                    <div class="flex gap-4 sm:gap-6 border-b border-gray-100 dark:border-gray-800 mb-6 overflow-x-auto no-scrollbar">
                        <button @click="activeTab = 'intern'"
                                :class="activeTab === 'intern' ? 'border-[#0B5694] text-[#0B5694] dark:border-blue-400 dark:text-blue-400' : 'border-transparent text-gray-400 hover:text-gray-600 dark:hover:text-gray-300'"
                                class="pb-3 border-b-2 font-black text-[10px] sm:text-xs uppercase tracking-wider transition-all whitespace-nowrap shrink-0">
                            Intern Configuration
                        </button>
                        <button @click="activeTab = 'school'"
                                :class="activeTab === 'school' ? 'border-[#0B5694] text-[#0B5694] dark:border-blue-400 dark:text-blue-400' : 'border-transparent text-gray-400 hover:text-gray-600 dark:hover:text-gray-300'"
                                class="pb-3 border-b-2 font-black text-[10px] sm:text-xs uppercase tracking-wider transition-all whitespace-nowrap shrink-0">
                            Schedule Configuration
                        </button>
                    </div>

                    {{-- TAB 1: INTERN CONFIGURATION --}}
                    <div x-show="activeTab === 'intern'">
                        <form :action="`/head-supervisor/interns/${selectedInternId}/schedule`" method="POST" class="pt-2">
                            @csrf
                            @method('PUT')

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-stretch">
                                {{-- Left panel: selection & hours --}}
                                <div class="space-y-6 flex flex-col justify-between">
                                    <div class="space-y-6">
                                        <div class="space-y-2 text-left">
                                            <label class="text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Select Intern</label>
                                            <select x-model="selectedInternId" @change="onInternChange()" required
                                                    class="w-full bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-2xl px-5 py-4 text-sm font-bold text-gray-700 dark:text-gray-300 shadow-none outline-none focus:ring-2 focus:ring-[#0B5694] dark:focus:ring-blue-400 transition-all cursor-pointer">
                                                <option value="" disabled selected>Choose an intern...</option>
                                                <template x-for="intern in interns" :key="intern.id">
                                                    <option :value="intern.id" x-text="`${intern.name} (${intern.email})`"></option>
                                                </template>
                                            </select>
                                        </div>

                                        <div class="space-y-2 text-left" x-show="selectedInternId" x-cloak>
                                            <label class="text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Required Internship Hours</label>
                                            <input type="number" name="required_hours" x-model="selectedInternHours" min="1" required
                                                    class="w-full bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-2xl px-5 py-4 text-sm font-bold text-gray-700 dark:text-gray-300 shadow-none outline-none focus:ring-2 focus:ring-[#0B5694] dark:focus:ring-blue-400 transition-all">
                                        </div>
                                    </div>

                                    <div class="pt-4" x-show="selectedInternId" x-cloak>
                                        <button type="submit" class="w-full px-6 py-4 bg-[#0B5694] dark:bg-blue-600 text-white rounded-2xl font-bold text-xs uppercase tracking-wider shadow-lg shadow-blue-500/10 hover:scale-[1.02] active:scale-95 transition-all cursor-pointer">
                                            Update Intern Schedule
                                        </button>
                                    </div>
                                </div>

                                {{-- Right panel: active reporting days or placeholder --}}
                                <div class="h-full">
                                    <div x-show="selectedInternId" x-cloak class="space-y-4 text-left">
                                        <label class="text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Active Reporting Days</label>
                                        <div class="grid grid-cols-2 gap-3">
                                            <template x-for="day in ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']" :key="day">
                                                <label class="flex items-center gap-3 p-4 bg-gray-50 dark:bg-gray-950 rounded-2xl border border-gray-150 dark:border-gray-800 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-900 transition-all select-none">
                                                    <input type="checkbox" name="schedule_assignment[]" :value="day" x-model="selectedInternDays"
                                                        class="rounded text-[#0B5694] focus:ring-[#0B5694] border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 w-4 h-4">
                                                    <span class="text-xs font-bold text-gray-700 dark:text-gray-200" x-text="day"></span>
                                                </label>
                                            </template>
                                        </div>
                                    </div>

                                    <div x-show="!selectedInternId" class="flex flex-col items-center justify-center p-8 bg-gray-50 dark:bg-gray-950 border border-dashed border-gray-200 dark:border-gray-800 rounded-3xl h-full min-h-[220px] text-center">
                                        <div class="w-12 h-12 bg-blue-50 dark:bg-blue-900/20 text-[#0B5694] dark:text-blue-400 rounded-full flex items-center justify-center mb-4 shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M20 8v6M23 11h-6"/></svg>
                                        </div>
                                        <h5 class="text-xs font-black text-gray-800 dark:text-white uppercase tracking-wider mb-1">No Intern Selected</h5>
                                        <p class="text-[10px] text-gray-400 dark:text-gray-500 font-medium max-w-[240px]">Choose an intern from the selection dropdown to configure their weekly hours and reporting days.</p>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    {{-- TAB 2: SCHEDULE CONFIGURATION --}}
                    <div x-show="activeTab === 'school'" class="space-y-6">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-100 dark:border-gray-800 pb-4">
                            <div>
                                <h5 class="text-sm font-black text-gray-850 dark:text-white uppercase tracking-wider">Institutional Default Schedules</h5>
                                <p class="text-[10px] text-gray-400 dark:text-gray-500 font-bold uppercase mt-0.5 tracking-wider">Set specific default hours for different schools</p>
                            </div>
                            <button @click="resetSchoolForm(); showSchoolModal = true"
                                    class="w-full sm:w-auto px-5 py-3.5 bg-[#0B5694] dark:bg-blue-600 text-white rounded-2xl font-bold text-[10px] uppercase tracking-wider shadow-lg shadow-blue-500/10 hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                                Register School Schedule
                            </button>
                        </div>

                        {{-- INLINE EDIT VIEW --}}
                        <div class="bg-gray-50 dark:bg-gray-950 p-6 sm:p-8 rounded-3xl border border-gray-100 dark:border-gray-850" x-show="editingSchoolInline" x-cloak>
                            <div class="flex items-center justify-between mb-6">
                                <div>
                                    <h4 class="text-lg font-black text-gray-800 dark:text-white uppercase tracking-tight">Edit Institutional Schedule</h4>
                                    <p class="text-xs text-[#0B5694] dark:text-blue-400 font-bold uppercase tracking-widest mt-1" x-text="schoolFormData.school_name"></p>
                                </div>
                                <button @click="cancelSchoolInlineEdit()"
                                        class="p-3 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 rounded-2xl transition-all active:scale-95">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                </button>
                            </div>

                            <form :action="schoolFormAction" method="POST" class="space-y-6">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="type" value="school_default">
                                <input type="hidden" name="school_name" :value="schoolFormData.school_name">

                                <div class="grid grid-cols-2 gap-4">
                                    <div class="space-y-2 text-left">
                                        <label class="text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Internship Start</label>
                                        <input type="date" name="start_date" x-model="schoolFormData.start_date"
                                               class="w-full bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl px-5 py-4 text-sm font-bold text-gray-700 dark:text-gray-300 shadow-none outline-none focus:ring-2 focus:ring-[#0B5694] transition-all">
                                    </div>
                                    <div class="space-y-2 text-left">
                                        <label class="text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Target Completion</label>
                                        <input type="date" name="target_end_date" x-model="schoolFormData.target_end_date"
                                               class="w-full bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl px-5 py-4 text-sm font-bold text-gray-700 dark:text-gray-300 shadow-none outline-none focus:ring-2 focus:ring-[#0B5694] transition-all">
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div class="space-y-2 text-left">
                                        <label class="text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Start Time</label>
                                        <input type="time" name="start_time" required x-model="schoolFormData.start_time"
                                               class="w-full bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl px-5 py-4 text-sm font-bold text-gray-700 dark:text-gray-300 shadow-none outline-none focus:ring-2 focus:ring-[#0B5694] transition-all">
                                    </div>
                                    <div class="space-y-2 text-left">
                                        <label class="text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">End Time</label>
                                        <input type="time" name="end_time" required x-model="schoolFormData.end_time"
                                               class="w-full bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl px-5 py-4 text-sm font-bold text-gray-700 dark:text-gray-300 shadow-none outline-none focus:ring-2 focus:ring-[#0B5694] transition-all">
                                    </div>
                                </div>

                                <div class="space-y-2 text-left">
                                    <label class="text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Daily Hour Credit</label>
                                    <div class="relative">
                                        <input type="number" name="daily_hours" required step="1" x-model="schoolFormData.daily_hours"
                                               class="w-full bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl px-5 py-4 text-sm font-bold text-gray-700 dark:text-gray-300 shadow-none outline-none focus:ring-2 focus:ring-[#0B5694] transition-all">
                                        <span class="absolute right-5 top-1/2 -translate-y-1/2 text-[10px] font-black text-gray-400 uppercase tracking-widest">Hours / Day</span>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div class="space-y-2 text-left">
                                        <label class="text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Hour Calculation Rule</label>
                                        <div class="grid grid-cols-3 gap-3">
                                            <label class="cursor-pointer group">
                                                <input type="radio" name="rounding_mode" value="none" x-model="schoolFormData.rounding_mode" class="hidden peer">
                                                <div class="p-3 text-center border border-gray-200 dark:border-gray-800 rounded-xl peer-checked:bg-[#0B5694] peer-checked:text-white transition-all bg-white dark:bg-gray-900">
                                                    <p class="text-[10px] font-black uppercase tracking-widest">Standard</p>
                                                </div>
                                            </label>
                                            <label class="cursor-pointer group">
                                                <input type="radio" name="rounding_mode" value="floor" x-model="schoolFormData.rounding_mode" class="hidden peer">
                                                <div class="p-3 text-center border border-gray-200 dark:border-gray-800 rounded-xl peer-checked:bg-[#0B5694] peer-checked:text-white transition-all bg-white dark:bg-gray-900">
                                                    <p class="text-[10px] font-black uppercase tracking-widest">Floor</p>
                                                </div>
                                            </label>
                                            <label class="cursor-pointer group">
                                                <input type="radio" name="rounding_mode" value="ceil" x-model="schoolFormData.rounding_mode" class="hidden peer">
                                                <div class="p-3 text-center border border-gray-200 dark:border-gray-800 rounded-xl peer-checked:bg-[#0B5694] peer-checked:text-white transition-all bg-white dark:bg-gray-900">
                                                    <p class="text-[10px] font-black uppercase tracking-widest">Ceil</p>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="space-y-2 text-left">
                                        <label class="text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Late Clock-In Policy</label>
                                        <div class="grid grid-cols-3 gap-3">
                                            <label class="cursor-pointer group">
                                                <input type="radio" name="late_policy" value="actual" x-model="schoolFormData.late_policy" class="hidden peer">
                                                <div class="p-3 text-center border border-gray-200 dark:border-gray-800 rounded-xl peer-checked:bg-[#0B5694] peer-checked:text-white transition-all bg-white dark:bg-gray-900">
                                                    <p class="text-[10px] font-black uppercase tracking-widest">Actual Mins</p>
                                                </div>
                                            </label>
                                            <label class="cursor-pointer group">
                                                <input type="radio" name="late_policy" value="next_half_hour" x-model="schoolFormData.late_policy" class="hidden peer">
                                                <div class="p-3 text-center border border-gray-200 dark:border-gray-800 rounded-xl peer-checked:bg-[#0B5694] peer-checked:text-white transition-all bg-white dark:bg-gray-900">
                                                    <p class="text-[10px] font-black uppercase tracking-widest">Next 30m</p>
                                                </div>
                                            </label>
                                            <label class="cursor-pointer group">
                                                <input type="radio" name="late_policy" value="next_hour" x-model="schoolFormData.late_policy" class="hidden peer">
                                                <div class="p-3 text-center border border-gray-200 dark:border-gray-800 rounded-xl peer-checked:bg-[#0B5694] peer-checked:text-white transition-all bg-white dark:bg-gray-900">
                                                    <p class="text-[10px] font-black uppercase tracking-widest">Next Hour</p>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex gap-3 pt-2">
                                    <button type="submit"
                                            class="flex-1 flex items-center justify-center gap-3 px-6 py-4 bg-[#0B5694] dark:bg-blue-600 text-white rounded-2xl font-bold text-xs uppercase tracking-wider shadow-xl shadow-blue-500/10 hover:scale-[1.02] active:scale-95 transition-all">
                                        Save Changes
                                    </button>
                                    <button type="button" @click="cancelSchoolInlineEdit()"
                                            class="px-6 py-4 bg-gray-200 dark:bg-gray-800 text-gray-500 dark:text-gray-400 rounded-2xl font-bold text-xs uppercase tracking-wider hover:bg-gray-300 dark:hover:bg-gray-700 active:scale-95 transition-all">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>

                        {{-- REGISTERED SCHOOLS LIST --}}
                        <div x-show="!editingSchoolInline" class="max-h-[380px] overflow-y-auto custom-scrollbar pr-2">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <template x-for="schoolName in Object.keys(schoolSchedules)" :key="schoolName">
                                    <div class="p-5 bg-gray-50 dark:bg-gray-950 border border-gray-100 dark:border-gray-800 rounded-3xl flex flex-col justify-between gap-4">
                                        <div class="flex items-start justify-between gap-4">
                                            <div class="flex items-center gap-4">
                                                <div class="w-10 h-10 bg-blue-50 dark:bg-blue-900/30 text-[#0B5694] dark:text-blue-400 rounded-xl flex items-center justify-center shrink-0">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                                                </div>
                                                <div class="text-left min-w-0">
                                                    <h4 class="text-sm font-black text-gray-800 dark:text-white uppercase tracking-tight truncate" x-text="schoolName"></h4>
                                                    <p class="text-[9px] text-gray-400 dark:text-gray-500 font-bold uppercase tracking-widest mt-0.5">Default Schedule</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-3 gap-2 border-t border-b border-gray-100 dark:border-gray-800 py-3">
                                            <div class="text-left">
                                                <span class="text-[8px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Shift Hours</span>
                                                <p class="text-[10px] font-black text-gray-800 dark:text-white tracking-widest mt-0.5" x-text="formatTime(schoolSchedules[schoolName].start_time) + ' - ' + formatTime(schoolSchedules[schoolName].end_time)"></p>
                                            </div>
                                            <div class="text-left">
                                                <span class="text-[8px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Daily Credit</span>
                                                <p class="text-[10px] font-black text-gray-800 dark:text-white tracking-widest mt-0.5" x-text="schoolSchedules[schoolName].daily_hours + ' HRS'"></p>
                                            </div>
                                            <div class="text-left">
                                                <span class="text-[8px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Rounding</span>
                                                <p class="text-[10px] font-black text-gray-800 dark:text-white tracking-widest mt-0.5 uppercase" x-text="schoolSchedules[schoolName].rounding_mode"></p>
                                            </div>
                                        </div>

                                        <div class="flex gap-2">
                                            <button @click="editSchoolInline(schoolSchedules[schoolName])"
                                                    class="flex-1 py-2.5 bg-blue-50 dark:bg-blue-900/20 text-[#0B5694] dark:text-blue-400 rounded-xl hover:bg-blue-100 transition-all border border-blue-100 dark:border-blue-900/50 active:scale-95 text-[10px] font-black uppercase tracking-wider text-center">
                                                Edit
                                            </button>
                                            <form :action="`/head-supervisor/schedule/${schoolSchedules[schoolName].hashid}`" method="POST" class="flex-1" onsubmit="return confirm('Withdraw this school schedule? Standard office hours will apply.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="w-full py-2.5 bg-rose-50 dark:bg-rose-900/20 text-rose-500 rounded-xl hover:bg-rose-100 transition-all border border-rose-100 dark:border-rose-900/50 active:scale-95 text-[10px] font-black uppercase tracking-wider text-center">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="Object.keys(schoolSchedules).length === 0">
                                    <div class="col-span-full p-8 text-center bg-gray-50 dark:bg-gray-950 rounded-3xl border border-gray-100 dark:border-gray-800">
                                        <p class="text-xs text-gray-400 dark:text-gray-500 font-bold uppercase tracking-widest">No institutional default schedules registered yet.</p>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- CALENDAR CONTAINER --}}
            <div class="w-full bg-white dark:bg-gray-900 rounded-[25px] sm:rounded-[35px] shadow-sm border border-gray-100 dark:border-gray-800 p-4 sm:p-6 lg:p-10 overflow-hidden flex flex-col">

                {{-- CALENDAR MONTH NAVIGATION HEADER --}}
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 sm:gap-6 mb-6 sm:mb-8">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-4 sm:gap-6">
                        <div>
                            <h3 class="text-xl md:text-2xl font-black text-gray-800 dark:text-gray-100 tracking-tight uppercase" x-text="monthName()"></h3>
                            <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-1 font-black uppercase tracking-widest leading-none">
                                <span x-show="!selectedSchool">Standard office hours applied.</span>
                                <span x-show="selectedSchool" class="text-[#0B5694] dark:text-blue-400" x-text="`Default schedule for ${selectedSchool} is active.`"></span>
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button @click="prevMonth()" class="p-2.5 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl transition-all border border-gray-100 dark:border-gray-700 active:scale-95 min-w-[44px] min-h-[44px] flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="m15 18-6-6 6-6"/></svg>
                            </button>
                            <button @click="nextMonth()" class="p-2.5 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl transition-all border border-gray-100 dark:border-gray-700 active:scale-95 min-w-[44px] min-h-[44px] flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="m9 18 6-6-6-6"/></svg>
                            </button>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        {{-- School Filter --}}
                        <div class="relative w-full sm:w-64">
                            <select x-model="selectedSchool"
                                    class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl px-5 py-3 text-[10px] font-black uppercase tracking-widest text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-[#0B5694] dark:focus:ring-blue-400 focus:outline-none transition-all shadow-sm">
                                <option value="">Global System Default</option>
                                <template x-for="schoolName in Object.keys(schoolSchedules)" :key="schoolName">
                                    <option :value="schoolName" x-text="schoolName"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- DESKTOP CALENDAR GRID (lg+) --}}
                <div class="hidden lg:block">
                    <div class="border border-gray-100 dark:border-gray-850 rounded-[25px] overflow-hidden shadow-inner bg-gray-50/50 dark:bg-gray-950/20">
                        <div class="grid grid-cols-7 border-b border-gray-100 dark:border-gray-850 text-center bg-gray-50 dark:bg-gray-800/40">
                            <template x-for="day in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']">
                                <div class="py-4 text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest" x-text="day"></div>
                            </template>
                        </div>

                        <div class="grid grid-cols-7 divide-x divide-y divide-gray-100 dark:divide-gray-850">
                            <template x-for="blank in blankDays">
                                <div class="min-h-[70px] sm:min-h-[100px] lg:min-h-[120px] bg-gray-100/30 dark:bg-gray-900/10"></div>
                            </template>

                            <template x-for="day in daysInMonth" :key="day.dateStr">
                                <div @click="selectDate(day)"
                                     class="min-h-[70px] sm:min-h-[100px] lg:min-h-[120px] p-2 sm:p-3 lg:p-4 bg-white dark:bg-gray-900 cursor-pointer flex flex-col justify-between hover:bg-blue-50/20 dark:hover:bg-blue-900/5 transition-all group border-t border-l border-gray-100 dark:border-gray-850 first-of-type:border-t-0"
                                     :class="{'opacity-60 bg-gray-50/50 dark:bg-gray-950/20': isPast(day.dateStr)}">
                                    <div class="flex justify-between items-start">
                                        <span class="text-[9px] sm:text-xs font-bold text-gray-400 dark:text-gray-500 group-hover:text-[#0B5694] dark:group-hover:text-blue-400 transition-colors uppercase tracking-[0.1em]"
                                              x-text="getCellLabel(day)"></span>
                                        <span class="text-base sm:text-lg font-black text-gray-850 dark:text-gray-100 leading-none group-hover:scale-110 transition-transform"
                                              x-text="day.day"></span>
                                    </div>

                                    <div class="mt-4 space-y-2 flex-1 flex flex-col justify-end">
                                        <template x-if="selectedSchool && schoolSchedules[selectedSchool] && !day.isWeekend && day.events.length === 0">
                                            <div class="rounded-2xl p-3 text-xs font-bold text-left leading-tight tracking-tight shadow-sm border bg-blue-50 dark:bg-blue-950/30 text-blue-600 dark:text-blue-400 border-blue-100 dark:border-blue-900/50">
                                                <div class="font-black uppercase tracking-widest text-[9px] mb-1 opacity-90">School Default</div>
                                                <div class="truncate font-bold" x-text="formatTime(schoolSchedules[selectedSchool].start_time) + ' - ' + formatTime(schoolSchedules[selectedSchool].end_time)"></div>
                                                <div class="text-[9px] mt-1 opacity-70" x-text="(schoolSchedules[selectedSchool].daily_hours % 1 === 0 ? schoolSchedules[selectedSchool].daily_hours : schoolSchedules[selectedSchool].daily_hours) + ' HRS CREDIT'"></div>
                                            </div>
                                        </template>

                                        <template x-for="(event, index) in day.events" :key="event.id || event.description || event.reason">
                                            <div x-show="index < 2">
                                                <template x-if="event.type !== 'absence' && event.type !== 'absence_request'">
                                                    <div class="rounded-2xl p-3 text-xs font-bold text-left leading-tight tracking-tight shadow-sm border"
                                                         :class="{
                                                             'bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 border-emerald-100 dark:border-emerald-900/50': event.type === 'holiday',
                                                             'bg-purple-50 dark:bg-purple-950/30 text-purple-600 dark:text-purple-400 border-purple-100 dark:border-purple-900/50': event.type === 'suspension',
                                                             'bg-blue-50 dark:bg-blue-950/30 text-blue-600 dark:text-blue-400 border-blue-100 dark:border-blue-900/50': event.type === 'work_day'
                                                         }">
                                                        <div class="font-black uppercase tracking-widest text-[9px] mb-1 opacity-90"
                                                             x-text="event.type === 'holiday' ? 'Holiday' : (event.type === 'suspension' ? 'Suspended' : 'Weekend Work')"></div>
                                                        <div class="truncate font-bold" x-text="event.description"></div>
                                                    </div>
                                                </template>

                                                <template x-if="event.type === 'absence_request'">
                                                    <div class="rounded-2xl p-3 text-xs font-bold text-left leading-tight tracking-tight shadow-sm border"
                                                         :class="{
                                                             'bg-orange-50 dark:bg-orange-950/30 text-orange-600 dark:text-orange-400 border-orange-100 dark:border-orange-900/50': event.status === 'pending',
                                                             'bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 border-emerald-100 dark:border-emerald-900/50': event.status === 'approved',
                                                             'bg-rose-50 dark:bg-rose-950/30 text-rose-600 dark:text-rose-400 border-rose-100 dark:border-rose-900/50': event.status === 'rejected'
                                                         }">
                                                        <div class="font-black uppercase tracking-widest text-[9px] mb-1 opacity-90 flex justify-between items-center">
                                                            <span x-text="(event.request_type === 'early_leave' ? 'Early Leave ' : 'Absence ') + (event.status === 'pending' ? 'Pending' : (event.status === 'approved' ? 'Approved' : 'Rejected'))"></span>
                                                            <template x-if="event.status === 'pending'">
                                                                <div class="w-1.5 h-1.5 rounded-full bg-orange-500 animate-pulse"></div>
                                                            </template>
                                                        </div>
                                                        <div class="truncate font-bold" x-text="event.intern_name + ': ' + event.reason"></div>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>

                                        <template x-if="day.events.length > 2">
                                            <div class="rounded-2xl p-2 text-center text-[10px] font-black uppercase tracking-wider bg-gray-50 dark:bg-gray-800 text-gray-400 dark:text-gray-500 border border-gray-100 dark:border-gray-800/80 mt-1 select-none">
                                                + <span x-text="day.events.length - 2"></span> more requests
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- MOBILE MONTH + AGENDA VIEW (< lg) — Google Calendar-style --}}
                <div class="lg:hidden -mx-4 sm:-mx-6 px-4 sm:px-6">
                    {{-- Month grid (compact, like Google Calendar mobile month view) --}}
                    <div class="bg-white dark:bg-gray-900 rounded-[20px] border border-gray-100 dark:border-gray-800 overflow-hidden shadow-sm mb-5">
                        <div class="grid grid-cols-7 border-b border-gray-50 dark:border-gray-800 text-center bg-gray-50 dark:bg-gray-800/30">
                            <template x-for="day in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']">
                                <div class="py-2.5 text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest" x-text="day"></div>
                            </template>
                        </div>
                        <div class="grid grid-cols-7">
                            <template x-for="blank in blankDays">
                                <div class="bg-gray-50/30 dark:bg-gray-900/20"></div>
                            </template>
                            <template x-for="(day, i) in daysInMonth" :key="day.dateStr">
                                <button @click="mobileSelectedDay = day.dateStr; mobileSelectedIndex = i"
                                        class="relative flex flex-col items-center justify-center py-2.5 transition-all active:scale-90"
                                        :class="mobileSelectedDay === day.dateStr
                                            ? 'bg-[#0B5694]/10 dark:bg-blue-600/20'
                                            : isPast(day.dateStr) ? 'opacity-40' : 'hover:bg-gray-50 dark:hover:bg-gray-800/30'">
                                    <span class="text-sm font-bold leading-none"
                                          :class="mobileSelectedDay === day.dateStr
                                              ? 'text-[#0B5694] dark:text-blue-400'
                                              : day.isWeekend ? 'text-gray-300 dark:text-gray-600' : 'text-gray-700 dark:text-gray-300'"
                                          x-text="day.day"></span>
                                    {{-- Event dot indicators (like Google Calendar) --}}
                                    <div class="flex items-center gap-0.5 mt-1 h-1.5">
                                        <template x-for="(evt, ei) in (day.events || []).slice(0, 3)" :key="ei">
                                            <div class="w-1 h-1 rounded-full"
                                                 :class="{
                                                     'bg-emerald-500': evt.type === 'holiday',
                                                     'bg-purple-500': evt.type === 'suspension',
                                                     'bg-blue-500': evt.type === 'work_day',
                                                     'bg-orange-500': evt.type === 'absence_request' && evt.status === 'pending',
                                                     'bg-emerald-400': evt.type === 'absence_request' && evt.status === 'approved',
                                                     'bg-rose-400': evt.type === 'absence_request' && evt.status === 'rejected'
                                                 }"></div>
                                        </template>
                                        {{-- School schedule dot when filter is active --}}
                                        <template x-if="selectedSchool && schoolSchedules[selectedSchool] && !day.isWeekend && (day.events || []).filter(e => e.type !== 'absence_request').length === 0">
                                            <div class="w-1 h-1 rounded-full bg-blue-300 dark:bg-blue-500/60"></div>
                                        </template>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </div>

                    {{-- Selected date label + add button --}}
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h4 class="text-base font-black text-gray-800 dark:text-gray-100 uppercase tracking-tight"
                                x-text="mobileSelectedDay ? formatDateLong(mobileSelectedDay) : ''"></h4>
                            <p class="text-[10px] text-gray-400 dark:text-gray-500 font-bold uppercase tracking-widest mt-0.5"
                               x-text="mobileSelectedDay ? (selectedSchool ? schoolSchedules[selectedSchool]?.school_name + ' schedule' : 'Standard hours') : ''"></p>
                        </div>
                        <button @click="selectedDateStr = mobileSelectedDay; selectedEvents = getMobileDayEvents(); const hasAbsence = selectedEvents.some(e => e.type === 'absence_request'); const hasEvent = selectedEvents.some(e => e.id !== undefined && e.type !== 'absence_request'); if (hasAbsence) { showAbsenceReviewModal = true } else if (hasEvent) { const dayObj = daysInMonth[mobileSelectedIndex]; if (dayObj) openViewModal(dayObj) } else if (!isPast(mobileSelectedDay)) { const dayObj = daysInMonth[mobileSelectedIndex]; if (dayObj) openCreateModal(dayObj) }"
                                class="p-3 bg-[#0B5694] dark:bg-blue-600 text-white rounded-2xl shadow-lg shadow-blue-500/20 hover:scale-105 active:scale-95 transition-all min-w-[44px] min-h-[44px] flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                        </button>
                    </div>

                    {{-- Agenda list for selected day --}}
                    <div class="space-y-2.5">
                        <template x-if="getMobileDayEvents().length === 0 && !isPast(mobileSelectedDay)">
                            <div @click="selectedDateStr = mobileSelectedDay; const dayObj = daysInMonth[mobileSelectedIndex]; if (dayObj) openCreateModal(dayObj)"
                                 class="flex flex-col items-center justify-center py-10 px-4 bg-gray-50 dark:bg-gray-800/30 rounded-3xl border border-dashed border-gray-200 dark:border-gray-700 cursor-pointer active:scale-[0.99] transition-all">
                                <div class="w-12 h-12 bg-blue-50 dark:bg-blue-900/20 text-[#0B5694] dark:text-blue-400 rounded-2xl flex items-center justify-center mb-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                                </div>
                                <p class="text-sm font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">No events scheduled</p>
                                <p class="text-[10px] text-gray-300 dark:text-gray-600 font-medium mt-1">Tap to add an override</p>
                            </div>
                        </template>

                        <template x-if="getMobileDayEvents().length === 0 && isPast(mobileSelectedDay)">
                            <div class="flex flex-col items-center justify-center py-10 px-4 bg-gray-50 dark:bg-gray-800/30 rounded-3xl">
                                <div class="w-12 h-12 bg-gray-100 dark:bg-gray-800 text-gray-300 dark:text-gray-600 rounded-2xl flex items-center justify-center mb-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                </div>
                                <p class="text-sm font-bold text-gray-300 dark:text-gray-600 uppercase tracking-wider">No records for this date</p>
                            </div>
                        </template>

                        <template x-for="event in getMobileDayEvents()" :key="event.id || event.description || event.reason">
                            <div @click="openMobileEvent(event)"
                                 class="flex items-start gap-3 sm:gap-4 p-4 bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm hover:shadow-md active:scale-[0.99] transition-all cursor-pointer">
                                <div class="w-10 h-10 rounded-2xl flex items-center justify-center shrink-0 mt-0.5"
                                     :class="{
                                         'bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400': event.type === 'holiday',
                                         'bg-purple-50 dark:bg-purple-950/30 text-purple-600 dark:text-purple-400': event.type === 'suspension',
                                         'bg-blue-50 dark:bg-blue-950/30 text-blue-600 dark:text-blue-400': event.type === 'work_day',
                                         'bg-orange-50 dark:bg-orange-950/30 text-orange-600 dark:text-orange-400': event.type === 'absence_request' && event.status === 'pending',
                                         'bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400': event.type === 'absence_request' && event.status === 'approved',
                                         'bg-rose-50 dark:bg-rose-950/30 text-rose-600 dark:text-rose-400': event.type === 'absence_request' && event.status === 'rejected'
                                     }">
                                    <template x-if="event.type === 'holiday' || event.type === 'suspension' || event.type === 'work_day'">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                    </template>
                                    <template x-if="event.type === 'absence_request'">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                                    </template>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-0.5">
                                        <span class="text-[9px] font-black uppercase tracking-widest"
                                              :class="{
                                                  'text-emerald-600 dark:text-emerald-400': event.type === 'holiday',
                                                  'text-purple-600 dark:text-purple-400': event.type === 'suspension',
                                                  'text-blue-600 dark:text-blue-400': event.type === 'work_day',
                                                  'text-orange-600 dark:text-orange-400': event.type === 'absence_request' && event.status === 'pending',
                                                  'text-emerald-600 dark:text-emerald-400': event.type === 'absence_request' && event.status === 'approved',
                                                  'text-rose-600 dark:text-rose-400': event.type === 'absence_request' && event.status === 'rejected'
                                              }">
                                            <template x-if="event.type === 'holiday'">Holiday</template>
                                            <template x-if="event.type === 'suspension'">Suspended</template>
                                            <template x-if="event.type === 'work_day'">Weekend Work</template>
                                            <template x-if="event.type === 'absence_request'">
                                                <span x-text="(event.request_type === 'early_leave' ? 'Early Leave' : 'Absence') + ' - ' + event.status"></span>
                                            </template>
                                        </span>
                                        <template x-if="event.type === 'absence_request' && event.status === 'pending'">
                                            <span class="w-1.5 h-1.5 rounded-full bg-orange-500 animate-pulse shrink-0"></span>
                                        </template>
                                    </div>
                                    <template x-if="event.type !== 'absence_request'">
                                        <p class="text-sm font-bold text-gray-800 dark:text-gray-100 truncate" x-text="event.description"></p>
                                    </template>
                                    <template x-if="event.type === 'absence_request'">
                                        <p class="text-sm font-bold text-gray-800 dark:text-gray-100 truncate" x-text="event.intern_name + ': ' + event.reason"></p>
                                    </template>
                                    <p class="text-[10px] text-gray-400 dark:text-gray-500 font-bold uppercase tracking-widest mt-0.5 truncate">
                                        <template x-if="event.type !== 'absence_request' && event.type !== 'absence'">
                                            <span x-text="event.type === 'holiday' ? 'Non-working day' : (event.type === 'suspension' ? 'Work suspended' : 'Make-up day')"></span>
                                        </template>
                                        <template x-if="event.type === 'absence_request'">
                                            <span x-text="event.intern_name + ' - ' + event.reason"></span>
                                        </template>
                                    </p>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 text-gray-300 dark:text-gray-600 mt-2"><path d="m9 18 6-6-6-6"/></svg>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        {{-- REGISTER SCHOOL SCHEDULE MODAL --}}
        <div x-show="showSchoolModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-950/80 backdrop-blur-md"
             x-cloak>
            
            <div @click.away="showSchoolModal = false"
                 class="bg-white dark:bg-gray-900 w-full max-w-2xl rounded-[40px] shadow-2xl border border-gray-100 dark:border-gray-800 flex flex-col max-h-[90vh] overflow-hidden animate-in zoom-in-95 duration-300">
                
                <header class="p-8 border-b border-gray-100 dark:border-gray-800 flex justify-between items-center bg-gray-50 dark:bg-gray-950 shrink-0">
                    <div>
                        <h3 class="text-xl font-black text-gray-800 dark:text-white uppercase tracking-tight" x-text="isEditingSchool ? 'Refine School Schedule' : 'Register School Schedule'"></h3>
                        <p class="text-xs text-[#0B5694] dark:text-blue-400 font-bold uppercase mt-1 tracking-widest" x-text="isEditingSchool ? 'Modify existing institutional hours.' : 'Assign specific hours to a university.'"></p>
                    </div>
                    <button @click="showSchoolModal = false" class="p-3 bg-gray-200 dark:bg-gray-800/50 hover:bg-gray-300 dark:hover:bg-gray-800 text-gray-500 dark:text-gray-400 rounded-2xl transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </header>

                <div class="p-10 overflow-y-auto flex-1">
                    <form :action="schoolFormAction" method="POST" class="space-y-6">
                        @csrf
                        <input type="hidden" name="_method" value="PUT" :disabled="!isEditingSchool">
                        <input type="hidden" name="type" value="school_default">

                        <div class="space-y-2 text-left">
                            <label class="text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Select University</label>
                            <select name="school_name" required x-model="schoolFormData.school_name"
                                    class="w-full bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-2xl px-5 py-4 text-sm font-bold text-gray-700 dark:text-gray-300 shadow-none outline-none focus:ring-2 focus:ring-[#0B5694] dark:focus:ring-blue-400 transition-all">
                                <option value="" disabled selected>Choose a school...</option>
                                @foreach($universities as $uni)
                                    <option value="{{ $uni }}">{{ $uni }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2 text-left">
                                <label class="text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Internship Start</label>
                                <input type="date" name="start_date" x-model="schoolFormData.start_date"
                                       class="w-full bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-2xl px-5 py-4 text-sm font-bold text-gray-700 dark:text-gray-300 shadow-none outline-none focus:ring-2 focus:ring-[#0B5694] dark:focus:ring-blue-400 transition-all">
                            </div>
                            <div class="space-y-2 text-left">
                                <label class="text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Target Completion</label>
                                <input type="date" name="target_end_date" x-model="schoolFormData.target_end_date"
                                       class="w-full bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-2xl px-5 py-4 text-sm font-bold text-gray-700 dark:text-gray-300 shadow-none outline-none focus:ring-2 focus:ring-[#0B5694] dark:focus:ring-blue-400 transition-all">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2 text-left">
                                <label class="text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Start Time</label>
                                <input type="time" name="start_time" required x-model="schoolFormData.start_time"
                                       class="w-full bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-2xl px-5 py-4 text-sm font-bold text-gray-700 dark:text-gray-300 shadow-none outline-none focus:ring-2 focus:ring-[#0B5694] dark:focus:ring-blue-400 transition-all">
                            </div>
                            <div class="space-y-2 text-left">
                                <label class="text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">End Time</label>
                                <input type="time" name="end_time" required x-model="schoolFormData.end_time"
                                       class="w-full bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-2xl px-5 py-4 text-sm font-bold text-gray-700 dark:text-gray-300 shadow-none outline-none focus:ring-2 focus:ring-[#0B5694] dark:focus:ring-blue-400 transition-all">
                            </div>
                        </div>

                        <div class="space-y-2 text-left">
                            <label class="text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Daily Hour Credit</label>
                            <div class="relative">
                                <input type="number" name="daily_hours" required step="1" x-model="schoolFormData.daily_hours"
                                       class="w-full bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-2xl px-5 py-4 text-sm font-bold text-gray-700 dark:text-gray-300 shadow-none outline-none focus:ring-2 focus:ring-[#0B5694] dark:focus:ring-blue-400 transition-all">
                                <span class="absolute right-5 top-1/2 -translate-y-1/2 text-[10px] font-black text-gray-400 uppercase tracking-widest">Hours / Day</span>
                            </div>
                        </div>

                        <div class="space-y-2 text-left">
                            <label class="text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Hour Calculation Rule</label>
                            <div class="grid grid-cols-3 gap-3">
                                <label class="cursor-pointer group">
                                    <input type="radio" name="rounding_mode" value="none" x-model="schoolFormData.rounding_mode" class="hidden peer">
                                    <div class="p-3 text-center border border-gray-100 dark:border-gray-800 rounded-xl peer-checked:bg-[#0B5694] peer-checked:text-white transition-all">
                                        <p class="text-[10px] font-black uppercase tracking-widest">Standard</p>
                                    </div>
                                </label>
                                <label class="cursor-pointer group">
                                    <input type="radio" name="rounding_mode" value="floor" x-model="schoolFormData.rounding_mode" class="hidden peer">
                                    <div class="p-3 text-center border border-gray-100 dark:border-gray-800 rounded-xl peer-checked:bg-[#0B5694] peer-checked:text-white transition-all">
                                        <p class="text-[10px] font-black uppercase tracking-widest">Floor</p>
                                    </div>
                                </label>
                                <label class="cursor-pointer group">
                                    <input type="radio" name="rounding_mode" value="ceil" x-model="schoolFormData.rounding_mode" class="hidden peer">
                                    <div class="p-3 text-center border border-gray-100 dark:border-gray-800 rounded-xl peer-checked:bg-[#0B5694] peer-checked:text-white transition-all">
                                        <p class="text-[10px] font-black uppercase tracking-widest">Ceil</p>
                                    </div>
                                </label>
                            </div>
                            <p class="text-[10px] text-gray-400 font-medium italic mt-2 px-1">Calculations are rounded to the nearest whole hour. Floor ignores extra minutes; Ceil rounds up.</p>
                        </div>

                        <div class="space-y-2 text-left">
                            <label class="text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Late Clock-In Policy</label>
                            <div class="grid grid-cols-3 gap-3">
                                <label class="cursor-pointer group">
                                    <input type="radio" name="late_policy" value="actual" x-model="schoolFormData.late_policy" class="hidden peer">
                                    <div class="p-3 text-center border border-gray-100 dark:border-gray-800 rounded-xl peer-checked:bg-[#0B5694] peer-checked:text-white transition-all">
                                        <p class="text-[10px] font-black uppercase tracking-widest">Actual Mins</p>
                                    </div>
                                </label>
                                <label class="cursor-pointer group">
                                    <input type="radio" name="late_policy" value="next_half_hour" x-model="schoolFormData.late_policy" class="hidden peer">
                                    <div class="p-3 text-center border border-gray-100 dark:border-gray-800 rounded-xl peer-checked:bg-[#0B5694] peer-checked:text-white transition-all">
                                        <p class="text-[10px] font-black uppercase tracking-widest">Next 30m</p>
                                    </div>
                                </label>
                                <label class="cursor-pointer group">
                                    <input type="radio" name="late_policy" value="next_hour" x-model="schoolFormData.late_policy" class="hidden peer">
                                    <div class="p-3 text-center border border-gray-100 dark:border-gray-800 rounded-xl peer-checked:bg-[#0B5694] peer-checked:text-white transition-all">
                                        <p class="text-[10px] font-black uppercase tracking-widest">Next Hour</p>
                                    </div>
                                </label>
                            </div>
                            <p class="text-[10px] text-gray-400 font-medium italic mt-2 px-1">Defines how to count hours if an intern is late. E.g., 8:15 AM check-in starts counting at 9:00 AM with 'Next Hour'.</p>
                        </div>

                        <button type="submit"
                                class="w-full flex items-center justify-center gap-3 px-6 py-4 bg-[#0B5694] dark:bg-blue-600 text-white rounded-2xl font-bold text-xs uppercase tracking-wider shadow-xl shadow-blue-500/10 hover:scale-[1.02] active:scale-95 transition-all">
                            <span x-text="isEditingSchool ? 'Finalize & Update' : 'Finalize School Schedule'"></span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- ABSENCE REVIEW MODAL --}}
        <div x-show="showAbsenceReviewModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-950/80 backdrop-blur-md"
             x-cloak>
            <div @click.away="showAbsenceReviewModal = false"
                 class="bg-white dark:bg-gray-900 w-full max-w-2xl min-h-[500px] rounded-[40px] shadow-2xl border border-gray-100 dark:border-gray-800 overflow-hidden animate-in zoom-in-95 duration-300 flex flex-col justify-between">
                <header class="p-8 border-b border-gray-100 dark:border-gray-800 flex justify-between items-center bg-gray-50 dark:bg-gray-950 shrink-0">
                    <div>
                        <h3 class="text-xl font-black text-gray-800 dark:text-white uppercase tracking-tight">Review Absence Request</h3>
                        <p class="text-xs text-[#0B5694] dark:text-blue-400 font-bold uppercase mt-1 tracking-widest" x-text="selectedDateStr"></p>
                    </div>
                    <button @click="showAbsenceReviewModal = false" class="p-3 bg-gray-200 dark:bg-gray-800/50 hover:bg-gray-300 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 rounded-2xl transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </header>
                <div class="p-10 flex-1 max-h-[75vh] overflow-y-auto custom-scrollbar">
                    <div class="space-y-6">
                        <template x-for="event in selectedEvents.filter(e => e.type === 'absence_request')" :key="event.id">
                            <div class="rounded-3xl p-6 border text-left bg-white dark:bg-gray-900 border-gray-100 dark:border-gray-800 space-y-4 mb-4 last:mb-0 shadow-sm">
                                {{-- Request Info Header --}}
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-100 dark:border-gray-800/80 pb-3">
                                    <div class="min-w-0">
                                        <p class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest leading-none mb-1.5"
                                           x-text="event.request_type === 'early_leave' ? 'Early Leave Request' : 'Absence Request'"></p>
                                        <h5 class="font-extrabold text-gray-850 dark:text-gray-100 text-sm truncate" x-text="event.intern_name"></h5>
                                    </div>
                                    <span class="text-[9px] font-black uppercase px-3 py-1.5 rounded-full border w-fit"
                                          :class="{
                                              'bg-orange-50 dark:bg-orange-950/20 text-orange-600 dark:text-orange-400 border-orange-100 dark:border-orange-900/30': event.status === 'pending',
                                              'bg-emerald-50 dark:bg-emerald-950/20 text-emerald-600 dark:text-emerald-400 border-emerald-100 dark:border-emerald-900/30': event.status === 'approved',
                                              'bg-rose-50 dark:bg-rose-950/20 text-rose-600 dark:text-rose-400 border-rose-100 dark:border-rose-900/30': event.status === 'rejected'
                                          }"
                                          x-text="event.status"></span>
                                </div>
                                
                                {{-- Reason --}}
                                <div class="space-y-1">
                                    <span class="text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest block">Reason / Details</span>
                                    <p class="text-base text-gray-750 dark:text-gray-205 leading-relaxed font-bold italic" x-text="'&quot;' + event.reason + '&quot;'"></p>
                                </div>

                                {{-- Attachment actions --}}
                                <template x-if="event.has_attachment">
                                    <a :href="`/head-supervisor/schedule/absence/${event.id}/attachment`" target="_blank"
                                       class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 rounded-xl text-[10px] font-black uppercase tracking-widest text-[#0B5694] dark:text-blue-400 hover:bg-blue-100 transition-all">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                        Preview Attachment
                                    </a>
                                </template>

                                {{-- Pending actions form --}}
                                <template x-if="event.status === 'pending'">
                                    <form :action="`/head-supervisor/schedule/absence/${event.id}`" method="POST" class="space-y-4 pt-3 border-t border-gray-100 dark:border-gray-800/80">
                                        @csrf
                                        @method('PATCH')
                                        <div>
                                            <label class="text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1.5 block">Head Supervisor Remarks</label>
                                            <textarea name="supervisor_remarks" rows="2"
                                                      class="w-full bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-850 rounded-2xl p-4 text-xs text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-[#0B5694] transition-all placeholder:text-gray-300 dark:placeholder:text-gray-700 resize-none font-semibold"
                                                      placeholder="Optional remarks for the intern..."></textarea>
                                        </div>
                                        <div class="flex gap-2.5">
                                            <button type="submit" name="status" value="rejected" class="flex-1 py-3.5 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/20 dark:hover:bg-rose-900/30 text-rose-600 dark:text-rose-400 border border-rose-100 dark:border-rose-900/30 rounded-xl font-bold text-xs uppercase tracking-wider transition-all">
                                                Reject
                                            </button>
                                            <button type="submit" name="status" value="approved" class="flex-1 py-3.5 bg-[#0B5694] hover:bg-blue-700 dark:bg-blue-600 dark:hover:bg-blue-700 text-white rounded-xl font-bold text-xs uppercase tracking-wider shadow-md hover:scale-[1.01] active:scale-95 transition-all">
                                                Approve
                                            </button>
                                        </div>
                                    </form>
                                </template>

                                {{-- Processed remarks --}}
                                <template x-if="event.status !== 'pending'">
                                    <div class="pt-3 border-t border-gray-100 dark:border-gray-800/80">
                                        <span class="text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest block mb-1">Review Remarks</span>
                                        <p class="text-xs text-gray-700 dark:text-gray-200 font-bold" x-text="event.remarks || 'No remarks provided.'"></p>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        {{-- VIEW EXCEPTION MODAL --}}
        <div x-show="showViewModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-950/80 backdrop-blur-md"
             x-cloak>
            <div @click.away="showViewModal = false"
                 class="bg-white dark:bg-gray-900 w-full max-w-2xl rounded-[40px] shadow-2xl border border-gray-100 dark:border-gray-800 overflow-hidden animate-in zoom-in-95 duration-300">
                <header class="p-8 border-b border-gray-100 dark:border-gray-800 flex justify-between items-center bg-gray-50 dark:bg-gray-950">
                    <div>
                        <h3 class="text-xl font-black text-gray-800 dark:text-white uppercase tracking-tight">Exception Details</h3>
                        <p class="text-xs text-[#0B5694] dark:text-blue-400 font-bold uppercase mt-1 tracking-widest" x-text="selectedDateStr"></p>
                    </div>
                    <button @click="showViewModal = false" class="p-3 bg-gray-200 dark:bg-gray-800/50 hover:bg-gray-300 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 rounded-2xl transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </header>
                <div class="p-10 max-h-[75vh] overflow-y-auto custom-scrollbar">
                    <template x-if="selectedEvent">
                        <div class="space-y-6 text-left">
                            <div class="rounded-3xl p-6 border"
                                 :class="{
                                     'bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 border-emerald-100 dark:border-emerald-800/40': selectedEvent.type === 'holiday',
                                     'bg-purple-50 dark:bg-purple-950/20 text-purple-700 dark:text-purple-400 border-purple-100 dark:border-gray-800/40': selectedEvent.type === 'suspension',
                                     'bg-blue-50 dark:bg-blue-950/20 text-blue-700 dark:text-blue-400 border-blue-100 dark:border-gray-800/40': selectedEvent.type === 'work_day'
                                 }">
                                <span class="text-[9px] font-black uppercase tracking-[0.2em] px-3 py-1.5 rounded-full border bg-white dark:bg-gray-900 border-inherit"
                                      x-text="selectedEvent.type === 'holiday' ? 'Holiday Exemption' : (selectedEvent.type === 'suspension' ? 'Suspension Override' : 'Weekend Shift Override')"></span>
                                <h5 class="text-xl font-black uppercase tracking-tight mt-6 leading-tight" x-text="selectedEvent.description"></h5>
                            </div>
                            
                            <template x-if="!isPast(selectedDateStr)">
                                <div class="flex flex-col gap-3">
                                    <button @click="openEditModal()"
                                            class="w-full py-4 bg-[#0B5694] dark:bg-blue-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-lg shadow-blue-500/10 hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        Edit Details
                                    </button>
                                    <button @click="openDeleteModal()"
                                            class="w-full py-4 bg-rose-50 dark:bg-rose-900/20 text-rose-600 dark:text-rose-400 border border-rose-100 dark:border-rose-900/35 hover:bg-rose-100 dark:hover:bg-rose-900/30 rounded-2xl font-black text-xs uppercase tracking-widest active:scale-95 transition-all">
                                        Withdraw Exemption
                                    </button>
                                </div>
                            </template>

                            <template x-if="isPast(selectedDateStr)">
                                <div class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-2xl border border-gray-100 dark:border-gray-800 text-center">
                                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest italic">Historical records cannot be modified.</p>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- CREATE EXCEPTION MODAL --}}
        <div x-show="showCreateModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-950/80 backdrop-blur-md"
             x-cloak>
            <div @click.away="showCreateModal = false"
                 class="bg-white dark:bg-gray-900 w-full max-w-2xl rounded-[40px] shadow-2xl border border-gray-100 dark:border-gray-800 overflow-hidden animate-in zoom-in-95 duration-300">
                <header class="p-8 border-b border-gray-100 dark:border-gray-800 flex justify-between items-center bg-gray-50 dark:bg-gray-950">
                    <div>
                        <h3 class="text-xl font-black text-gray-800 dark:text-white uppercase tracking-tight">Calendar Override</h3>
                        <p class="text-xs text-[#0B5694] dark:text-blue-400 font-bold uppercase mt-1 tracking-widest" x-text="selectedDateStr"></p>
                    </div>
                    <button @click="showCreateModal = false" class="p-3 bg-gray-200 dark:bg-gray-800/50 hover:bg-gray-300 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 rounded-2xl transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </header>
                <div class="p-10 max-h-[75vh] overflow-y-auto custom-scrollbar">
                    <form action="{{ route('head-supervisor.schedule.store') }}" method="POST" class="space-y-6">
                        @csrf
                        <input type="hidden" name="date" :value="selectedDateStr">
                        <div class="space-y-2 text-left">
                            <label class="text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Exemption Type</label>
                            <select name="type" x-model="exceptionFormData.type" required
                                    class="w-full bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-2xl px-5 py-4 text-sm font-bold text-gray-700 dark:text-gray-300 shadow-none outline-none focus:ring-2 focus:ring-[#0B5694] dark:focus:ring-blue-400 transition-all">
                                <option value="holiday">Official Holiday</option>
                                <option value="suspension">Work Suspension</option>
                                <option value="work_day">Weekend Work Day</option>
                            </select>
                        </div>
                        <div class="space-y-2 text-left">
                            <label class="text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Description</label>
                            <input type="text" name="description" x-model="exceptionFormData.description" required placeholder="e.g. Typhoon Suspension"
                                   class="w-full bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-2xl px-5 py-4 text-sm font-bold text-gray-700 dark:text-gray-300 shadow-none outline-none focus:ring-2 focus:ring-[#0B5694] dark:focus:ring-blue-400 transition-all placeholder-gray-400 dark:placeholder-gray-600">
                        </div>
                        <button type="submit"
                                class="w-full px-6 py-4 bg-[#0B5694] dark:bg-blue-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl shadow-blue-500/10 hover:scale-[1.02] active:scale-[0.98] transition-all">
                            Log Exemption
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- EDIT EXCEPTION MODAL --}}
        <div x-show="showEditModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-950/80 backdrop-blur-md"
             x-cloak>
            <div @click.away="showEditModal = false"
                 class="bg-white dark:bg-gray-900 w-full max-w-2xl rounded-[40px] shadow-2xl border border-gray-100 dark:border-gray-800 overflow-hidden animate-in zoom-in-95 duration-300">
                <header class="p-8 border-b border-gray-100 dark:border-gray-800 flex justify-between items-center bg-gray-50 dark:bg-gray-950">
                    <div>
                        <h3 class="text-xl font-black text-gray-800 dark:text-white uppercase tracking-tight">Refine Exception</h3>
                        <p class="text-xs text-[#0B5694] dark:text-blue-400 font-bold uppercase mt-1 tracking-widest" x-text="selectedDateStr"></p>
                    </div>
                    <button @click="showEditModal = false" class="p-3 bg-gray-200 dark:bg-gray-800/50 hover:bg-gray-300 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 rounded-2xl transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </header>
                <div class="p-10 max-h-[75vh] overflow-y-auto custom-scrollbar">
                    <form :action="exceptionFormAction" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="date" :value="selectedDateStr">
                        <div class="space-y-2 text-left">
                            <label class="text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Exemption Type</label>
                            <select name="type" x-model="exceptionFormData.type" required
                                    class="w-full bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-2xl px-5 py-4 text-sm font-bold text-gray-700 dark:text-gray-300 shadow-none outline-none focus:ring-2 focus:ring-[#0B5694] dark:focus:ring-blue-400 transition-all">
                                <option value="holiday">Official Holiday</option>
                                <option value="suspension">Work Suspension</option>
                                <option value="work_day">Weekend Work Day</option>
                            </select>
                        </div>
                        <div class="space-y-2 text-left">
                            <label class="text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Description</label>
                            <input type="text" name="description" x-model="exceptionFormData.description" required placeholder="e.g. Typhoon Suspension"
                                   class="w-full bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-2xl px-5 py-4 text-sm font-bold text-gray-700 dark:text-gray-300 shadow-none outline-none focus:ring-2 focus:ring-[#0B5694] dark:focus:ring-blue-400 transition-all">
                        </div>
                        <div class="flex flex-col gap-3">
                            <button type="submit"
                                    class="w-full px-6 py-4 bg-[#0B5694] dark:bg-blue-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl shadow-blue-500/10 hover:scale-[1.02] active:scale-[0.98] transition-all">
                                Update Override
                            </button>
                            <button type="button" @click="showEditModal = false; showViewModal = true"
                                    class="w-full py-4 bg-gray-50 dark:bg-gray-800 text-gray-400 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-gray-100 transition-all">
                                Back to Summary
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- DELETE EXCEPTION MODAL --}}
        <div x-show="showDeleteModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-950/80 backdrop-blur-md"
             x-cloak>
            <div @click.away="showDeleteModal = false"
                 class="bg-white dark:bg-gray-900 w-full max-w-sm rounded-[40px] shadow-2xl border border-gray-200 dark:border-gray-800 overflow-hidden animate-in zoom-in-95 duration-300">
                <div class="p-10 text-center">
                    <div class="w-20 h-20 bg-rose-50 dark:bg-rose-950/30 text-rose-500 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 6h18M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                    </div>
                    <h4 class="text-xl font-black text-gray-850 dark:text-white uppercase tracking-tight mb-2">Withdraw Exemption?</h4>
                    <p class="text-sm text-gray-400 dark:text-gray-500 font-medium italic mb-8">This will restore standard rules for <span class="font-bold text-gray-700 dark:text-gray-300" x-text="selectedDateStr"></span>. Are you sure?</p>
                    
                    <div class="flex flex-col gap-3">
                        <form :action="deleteActionUrl" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full py-4 bg-rose-500 text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-lg shadow-rose-500/20 hover:bg-rose-600 active:scale-95 transition-all">
                                Yes, Withdraw Exemption
                            </button>
                        </form>
                        <button @click="showDeleteModal = false; showViewModal = true" class="w-full py-4 bg-gray-50 dark:bg-gray-800 text-gray-400 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-gray-100 transition-all">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>


    </div>
</x-page-layout>

<script>
    function calendarManager(initialEvents, schoolSchedules, interns) {
        return {
            loading: true,
            currentDate: new Date(),
            daysInMonth: [],
            blankDays: [],
            events: initialEvents || {},
            schoolSchedules: schoolSchedules || {},
            selectedSchool: '',
            mobileSelectedDay: '',
            mobileSelectedIndex: 0,
            
            // Inline edit state
            editingSchoolInline: false,
            configSectionOpen: false,
            activeTab: 'intern',

            // Intern schedule state
            interns: interns || [],
            selectedInternId: '',
            selectedInternDays: [],
            selectedInternHours: 480,

            toggleConfigSection() {
                this.configSectionOpen = !this.configSectionOpen;
            },

            onInternChange() {
                const found = this.interns.find(i => i.id === this.selectedInternId);
                if (found) {
                    this.selectedInternDays = [...found.schedule_assignment];
                    this.selectedInternHours = found.required_hours || 480;
                } else {
                    this.selectedInternDays = [];
                    this.selectedInternHours = 480;
                }
            },
            
            // Modal States
            showSchoolModal: false,
            showViewModal: false,
            showCreateModal: false,
            showEditModal: false,
            showDeleteModal: false,
            showAbsenceReviewModal: false,
            
            // School Form State
            isEditingSchool: false,
            schoolFormAction: '{{ route('head-supervisor.schedule.store') }}',
            schoolFormData: {
                school_name: '',
                start_date: '',
                target_end_date: '',
                start_time: '08:00',
                end_time: '17:00',
                daily_hours: 8,
                rounding_mode: 'none',
                late_policy: 'actual'
            },

            // Exception State
            selectedDateStr: '',
            selectedEvent: null,
            selectedEvents: [],
            exceptionFormAction: '{{ route('head-supervisor.schedule.store') }}',
            exceptionFormData: {
                type: 'holiday',
                description: ''
            },
            
            deleteActionUrl: '',
            


            init() {
                this.generateCalendar();
                setTimeout(() => {
                    this.loading = false;
                }, 800);
                const today = new Date();
                const yyyy = today.getFullYear();
                const mm = String(today.getMonth() + 1).padStart(2, '0');
                const dd = String(today.getDate()).padStart(2, '0');
                this.mobileSelectedDay = `${yyyy}-${mm}-${dd}`;
                this.mobileSelectedIndex = this.daysInMonth.findIndex(d => d.dateStr === this.mobileSelectedDay);
                if (this.mobileSelectedIndex < 0) this.mobileSelectedIndex = 0;
            },

            isPast(dateStr) {
                if (!dateStr) return false;
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                
                // Use T00:00:00 to ensure local midnight parsing
                const date = new Date(dateStr + 'T00:00:00');
                date.setHours(0, 0, 0, 0);
                
                return date < today;
            },

            resetExceptionForm(dayObj = null) {
                this.exceptionFormAction = '{{ route('head-supervisor.schedule.store') }}';
                this.exceptionFormData = {
                    type: dayObj && dayObj.isWeekend ? 'work_day' : 'holiday',
                    description: ''
                };
            },

            openCreateModal(dayObj) {
                if (this.isPast(dayObj.dateStr)) return;
                this.selectedDateStr = dayObj.dateStr;
                this.resetExceptionForm(dayObj);
                this.showCreateModal = true;
            },

            openViewModal(dayObj) {
                this.selectedDateStr = dayObj.dateStr;
                this.selectedEvent = dayObj.events.find(e => e.type !== 'absence') || null;
                if (this.selectedEvent) {
                    this.deleteActionUrl = `/head-supervisor/schedule/${this.selectedEvent.id}`;
                    this.showViewModal = true;
                }
            },

            openEditModal() {
                if (this.selectedEvent && !this.isPast(this.selectedDateStr)) {
                    this.exceptionFormAction = `/head-supervisor/schedule/${this.selectedEvent.id}`;
                    this.exceptionFormData = {
                        type: this.selectedEvent.type,
                        description: this.selectedEvent.description
                    };
                    this.showViewModal = false;
                    this.showEditModal = true;
                }
            },

            openDeleteModal() {
                if (this.selectedEvent && !this.isPast(this.selectedDateStr)) {
                    this.showViewModal = false;
                    this.showDeleteModal = true;
                }
            },

            selectDate(dayObj) {
                this.selectedDateStr = dayObj.dateStr;
                this.selectedEvents = dayObj.events;
                const hasAbsence = dayObj.events.some(e => e.type === 'absence_request');
                const hasEvent = dayObj.events.some(e => e.id !== undefined && e.type !== 'absence_request');

                if (hasAbsence) {
                    this.showAbsenceReviewModal = true;
                } else if (hasEvent) {
                    this.openViewModal(dayObj);
                } else {
                    if (!this.isPast(dayObj.dateStr)) {
                        this.openCreateModal(dayObj);
                    }
                }
            },

            resetSchoolForm() {
                this.isEditingSchool = false;
                this.editingSchoolInline = false;
                this.schoolFormAction = '{{ route('head-supervisor.schedule.store') }}';
                this.schoolFormData = {
                    school_name: '',
                    start_time: '08:00',
                    end_time: '17:00',
                    daily_hours: 8,
                    rounding_mode: 'none',
                    late_policy: 'actual'
                };
            },

            editSchoolInline(school) {
                this.schoolFormAction = `/head-supervisor/schedule/${school.hashid}`;
                this.schoolFormData = {
                    school_name: school.school_name,
                    start_date: school.start_date ? school.start_date.substring(0, 10) : '',
                    target_end_date: school.target_end_date ? school.target_end_date.substring(0, 10) : '',
                    start_time: school.start_time,
                    end_time: school.end_time,
                    daily_hours: school.daily_hours,
                    rounding_mode: school.rounding_mode,
                    late_policy: school.late_policy || 'actual'
                };
                this.editingSchoolInline = true;
            },

            cancelSchoolInlineEdit() {
                this.editingSchoolInline = false;
            },

            prevMonth() {
                const newDate = new Date(this.currentDate);
                newDate.setMonth(newDate.getMonth() - 1);
                this.currentDate = newDate;
                this.generateCalendar();
            },

            nextMonth() {
                const newDate = new Date(this.currentDate);
                newDate.setMonth(newDate.getMonth() + 1);
                this.currentDate = newDate;
                this.generateCalendar();
            },

            monthName() {
                return this.currentDate.toLocaleString('default', { month: 'long', year: 'numeric' });
            },

            formatTime(timeStr) {
                if (!timeStr) return '';
                const [hours, minutes] = timeStr.split(':');
                let h = parseInt(hours);
                const ampm = h >= 12 ? 'PM' : 'AM';
                h = h % 12;
                h = h ? h : 12; // the hour '0' should be '12'
                return `${h}:${minutes} ${ampm}`;
            },

            generateCalendar() {
                const year = this.currentDate.getFullYear();
                const month = this.currentDate.getMonth();
                const firstDay = new Date(year, month, 1).getDay();
                const totalDays = new Date(year, month + 1, 0).getDate();
                const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

                this.blankDays = Array.from({ length: firstDay }, (_, i) => i);
                this.daysInMonth = Array.from({ length: totalDays }, (_, i) => {
                    const dayNum = i + 1;
                    const dateObj = new Date(year, month, dayNum);
                    const yyyy = dateObj.getFullYear();
                    const mm = String(dateObj.getMonth() + 1).padStart(2, '0');
                    const dd = String(dateObj.getDate()).padStart(2, '0');
                    const dateStr = `${yyyy}-${mm}-${dd}`;
                    return {
                        day: dayNum,
                        dateStr: dateStr,
                        isWeekend: dateObj.getDay() === 0 || dateObj.getDay() === 6,
                        dayNameShort: dayNames[dateObj.getDay()],
                        events: this.events[dateStr] || []
                    };
                });
                if (this.mobileSelectedDay && !this.daysInMonth.some(d => d.dateStr === this.mobileSelectedDay)) {
                    this.mobileSelectedDay = this.daysInMonth[0]?.dateStr || '';
                    this.mobileSelectedIndex = 0;
                }
                this.mobileSelectedIndex = Math.max(0, this.daysInMonth.findIndex(d => d.dateStr === this.mobileSelectedDay));
            },

            getCellLabel(day) {
                if (day.events && day.events.length > 0) {
                    const primary = day.events.find(e => e.type === 'holiday' || e.type === 'suspension' || e.type === 'work_day');
                    if (primary) {
                        return primary.type === 'holiday' ? 'Holiday' : (primary.type === 'suspension' ? 'Suspended' : 'Weekend Work');
                    }
                    const absence = day.events.find(e => e.type === 'absence_request');
                    if (absence) {
                        const prefix = absence.request_type === 'early_leave' ? 'Leave ' : 'Absence ';
                        return prefix + (absence.status === 'pending' ? '(P)' : (absence.status === 'approved' ? '(A)' : '(R)'));
                    }
                }
                return day.isWeekend ? 'Weekend' : 'Workday';
            },

            getMobileDayEvents() {
                if (!this.mobileSelectedDay) return [];
                return this.events[this.mobileSelectedDay] || [];
            },

            openMobileEvent(event) {
                this.selectedDateStr = this.mobileSelectedDay;
                const dayObj = this.daysInMonth[this.mobileSelectedIndex];
                if (!dayObj) return;
                if (event.type === 'absence_request') {
                    this.selectedEvents = [event];
                    this.showAbsenceReviewModal = true;
                } else {
                    this.selectedEvent = event;
                    this.deleteActionUrl = event.id ? `/head-supervisor/schedule/${event.id}` : '';
                    this.showViewModal = true;
                }
            },

            formatDateLong(dateStr) {
                if (!dateStr) return '';
                const d = new Date(dateStr + 'T00:00:00');
                return d.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' });
            }
        };
    }
</script>
@endsection
