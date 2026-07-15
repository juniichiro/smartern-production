@extends('layouts.app')

@section('title', $intern->first_name . ' ' . $intern->last_name)

@section('content')
<x-page-layout 
    portalType="Head Supervisor" 
    userName="{{ Auth::user()->supervisorDetails->first_name }}" 
    userRole="{{ Auth::user()->supervisorDetails->office->name }}"
>
    <div x-data="headSupervisorInternProfile" class="flex flex-col h-full">
        {{-- Navigation Header --}}
        <header class="flex items-center gap-4 mb-8 lg:mb-10">
            <a href="{{ route('head-supervisor.interns') }}" class="w-10 h-10 bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-xl flex items-center justify-center text-gray-400 dark:text-gray-500 hover:text-[#0B5694] dark:hover:text-blue-400 hover:border-[#0B5694] dark:hover:border-blue-400 transition shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            </a>
            <div>
                <h2 class="text-2xl lg:text-3xl font-bold text-gray-800 dark:text-gray-100 tracking-tight">{{ $intern->first_name }} {{ $intern->last_name }}</h2>
                <p class="text-xs lg:text-sm text-gray-400 dark:text-gray-500 mt-1 font-medium">Detailed progress and activity tracking</p>
            </div>
        </header>

        <div class="space-y-8">
            {{-- Top Row: Identity & Progress --}}
            <div class="grid grid-cols-1 xl:grid-cols-12 gap-8 items-stretch">
                {{-- Intern Header Section --}}
                <div class="xl:col-span-7 bg-white dark:bg-gray-900 rounded-[20px] sm:rounded-[30px] border border-gray-100 dark:border-gray-800 shadow-sm p-4 sm:p-8 lg:p-10 flex flex-col sm:flex-row items-center gap-4 sm:gap-8 transition-all">
                    <x-avatar :user="$intern" size="32" circular="false" border="4" textSize="4xl" />

                    <div class="text-center sm:text-left flex-1 min-w-0">
                        <h3 class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white truncate">{{ $intern->first_name }} {{ $intern->last_name }}</h3>
                        
                        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-x-4 gap-y-1 mt-2 max-w-full">
                            <p class="text-[11px] sm:text-xs text-gray-500 dark:text-gray-400 font-bold flex items-center gap-1.5 min-w-0 max-w-full">
                                <svg class="flex-shrink-0" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                                <span class="truncate" title="{{ $intern->user->email }}">{{ $intern->user->email }}</span>
                            </p>
                            <span class="w-1 h-1 bg-gray-300 dark:bg-gray-700 rounded-full hidden sm:block"></span>
                            <p class="text-[11px] sm:text-xs text-gray-500 dark:text-gray-400 font-bold flex items-center gap-1.5">
                                <svg class="flex-shrink-0" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                                {{ $intern->phone_number ?? 'No Phone' }}
                            </p>
                        </div>

                        <p class="text-[10px] sm:text-xs text-red-600 dark:text-red-400 font-black uppercase tracking-widest mt-2 flex flex-wrap items-center justify-center sm:justify-start gap-x-3 gap-y-1 max-w-full">
                            <span class="truncate max-w-[200px] sm:max-w-xs" title="ICE: {{ $intern->emergency_contact ?? 'Not Provided' }}">ICE: {{ $intern->emergency_contact ?? 'Not Provided' }}</span>
                            @if($intern->emergency_contact_number)
                                <span class="w-1 h-1 bg-red-300 dark:bg-red-800 rounded-full hidden sm:block"></span>
                                <span class="flex items-center gap-1.5 min-w-0">
                                    <svg class="flex-shrink-0" xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                                    <span class="truncate">{{ $intern->emergency_contact_number }}</span>
                                </span>
                            @endif
                        </p>
                        
                        <div class="mt-4 inline-block max-w-full truncate bg-blue-50 dark:bg-blue-950/30 text-[#0B5694] dark:text-blue-400 text-[10px] sm:text-[11px] font-black px-4 py-1.5 rounded-full uppercase tracking-widest border border-blue-100 dark:border-blue-900/50 shadow-sm" title="{{ $intern->office->name ?? 'Unassigned Department' }}">
                            {{ $intern->office->name ?? 'Unassigned Department' }}
                        </div>
                    </div>
                </div>

                {{-- Schedule & Timeline Quick View --}}
                <div class="xl:col-span-5 flex flex-col h-full" x-data="{
                    requiredHours: {{ $intern->required_hours ?? 480 }},
                    scheduleDays: @js($intern->schedule_assignment ?? ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']),
                    startDate: '{{ ($intern->start_date ?? ($schoolSchedule->start_date ?? null)) ? ($intern->start_date ?? $schoolSchedule->start_date)->format('Y-m-d') : '' }}',
                    endDate: '{{ $intern->end_date ? $intern->end_date->format('Y-m-d') : '' }}',
                    renderedHours: {{ $intern->hours_rendered ?? 0 }},
                    dailyHours: {{ $schoolSchedule ? $schoolSchedule->daily_hours ?? 8 : 8 }},
                    holidays: @js($holidaysAndSuspensions ?? []),
                    configModalOpen: false,
                    
                    get targetEndDate() {
                        if (!this.startDate || !this.requiredHours || this.requiredHours <= 0 || !this.scheduleDays.length) {
                            return 'N/A';
                        }
                        
                        let hoursLeft = this.requiredHours;
                        let currentDate = new Date(this.startDate);
                        
                        let safetyCounter = 0;
                        const maxIterations = 365 * 5; 
                        
                        while (hoursLeft > 0 && safetyCounter < maxIterations) {
                            safetyCounter++;
                            
                            const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                            const currentDayName = dayNames[currentDate.getDay()];
                            
                            const yyyy = currentDate.getFullYear();
                            const mm = String(currentDate.getMonth() + 1).padStart(2, '0');
                            const dd = String(currentDate.getDate()).padStart(2, '0');
                            const dateStr = `${yyyy}-${mm}-${dd}`;
                            
                            const isReportingDay = this.scheduleDays.includes(currentDayName);
                            const isNonWorkingDay = this.holidays.includes(dateStr);
                            
                            if (isReportingDay && !isNonWorkingDay) {
                                hoursLeft -= this.dailyHours;
                            }
                            
                            if (hoursLeft > 0) {
                                currentDate.setDate(currentDate.getDate() + 1);
                            }
                        }
                        
                        const options = { year: 'numeric', month: 'short', day: 'numeric' };
                        return currentDate.toLocaleDateString('en-US', options).toUpperCase();
                    },

                    get projectedEndDate() {
                        let hoursLeft = Math.max(0, this.requiredHours - this.renderedHours);
                        if (hoursLeft <= 0) {
                            return 'COMPLETED';
                        }
                        let currentDate = new Date();
                        currentDate.setHours(0, 0, 0, 0);
                        
                        let safetyCounter = 0;
                        const maxIterations = 365 * 5;
                        
                        while (hoursLeft > 0 && safetyCounter < maxIterations) {
                            safetyCounter++;
                            
                            const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                            const currentDayName = dayNames[currentDate.getDay()];
                            
                            const yyyy = currentDate.getFullYear();
                            const mm = String(currentDate.getMonth() + 1).padStart(2, '0');
                            const dd = String(currentDate.getDate()).padStart(2, '0');
                            const dateStr = `${yyyy}-${mm}-${dd}`;
                            
                            const isReportingDay = this.scheduleDays.includes(currentDayName);
                            const isNonWorkingDay = this.holidays.includes(dateStr);
                            
                            if (isReportingDay && !isNonWorkingDay) {
                                hoursLeft -= this.dailyHours;
                            }
                            
                            if (hoursLeft > 0) {
                                currentDate.setDate(currentDate.getDate() + 1);
                            }
                        }
                        
                        const options = { year: 'numeric', month: 'short', day: 'numeric' };
                        return currentDate.toLocaleDateString('en-US', options).toUpperCase();
                    },

                    get isProjectedOverdue() {
                        let referenceEndDate = this.endDate || (this.targetEndDate !== 'N/A' ? this.targetEndDate : null);
                        if (!referenceEndDate) return false;
                        
                        let projDateStr = this.projectedEndDate;
                        if (projDateStr === 'N/A' || projDateStr === 'COMPLETED') return false;
                        
                        let projDate = new Date(projDateStr);
                        let officialEndDate = new Date(referenceEndDate);
                        
                        return projDate.getTime() > officialEndDate.getTime();
                    }
                }">
                    <div class="bg-white dark:bg-gray-900 rounded-[20px] sm:rounded-[30px] border border-gray-100 dark:border-gray-800 shadow-sm p-4 sm:p-8 flex flex-col justify-between h-full transition-all">
                        <div>
                            <p class="text-[10px] sm:text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-4">Current Deployment & Timeline</p>
                            
                            <div class="flex items-center gap-4 min-w-0">
                                <div class="w-12 h-12 bg-blue-50 dark:bg-blue-900/20 rounded-2xl flex items-center justify-center text-[#0B5694] dark:text-blue-400 flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm sm:text-base font-black text-gray-800 dark:text-gray-100 uppercase tracking-tight truncate" title="{{ $schoolSchedule ? 'Schedule for ' . $intern->school : 'Standard Office Hours' }}">
                                        {{ $schoolSchedule ? 'Schedule for ' . $intern->school : 'Standard Office Hours' }}
                                    </p>
                                    <p class="text-[10px] text-gray-400 dark:text-gray-500 font-bold uppercase tracking-widest truncate" title="{{ $schoolSchedule ? $intern->school : 'Institutional Default' }}">{{ $schoolSchedule ? $intern->school : 'Institutional Default' }}</p>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-x-6 gap-y-4 mt-6 pt-6 border-t border-gray-100 dark:border-gray-800">
                                <div>
                                    <span class="text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest block mb-1">Shift Windows</span>
                                    <p class="text-xs sm:text-sm font-black text-gray-700 dark:text-gray-300">
                                        @if($schoolSchedule && isset($schoolSchedule->start_time))
                                            {{ \Carbon\Carbon::parse($schoolSchedule->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($schoolSchedule->end_time)->format('h:i A') }}
                                        @else
                                            08:00 AM - 05:00 PM
                                        @endif
                                    </p>
                                </div>
                                <div>
                                    <span class="text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest block mb-1">Daily Credit</span>
                                    <p class="text-xs sm:text-sm font-black text-gray-800 dark:text-white uppercase">
                                        {{ $schoolSchedule && isset($schoolSchedule->daily_hours) ? (float)$schoolSchedule->daily_hours . ' HRS' : '8.0 HRS' }}
                                    </p>
                                </div>

                                <div>
                                    <span class="text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest block mb-1">Official Start</span>
                                    <p class="text-xs font-black text-gray-800 dark:text-gray-100 uppercase" x-text="startDate ? new Date(startDate).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }).toUpperCase() : 'NOT SET'">
                                        {{ ($intern->start_date ?? ($schoolSchedule->start_date ?? null)) ? ($intern->start_date ?? $schoolSchedule->start_date)->format('M d, Y') : 'NOT SET' }}
                                    </p>
                                </div>
                                <div>
                                    <span class="text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest block mb-1">Expected End</span>
                                    <p class="text-xs font-black text-gray-800 dark:text-gray-100 uppercase" x-text="endDate ? new Date(endDate).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }).toUpperCase() : targetEndDate">
                                        {{ $intern->end_date ? $intern->end_date->format('M d, Y') : 'NOT SET' }}
                                    </p>
                                    <div x-show="isProjectedOverdue" x-cloak class="mt-1">
                                        <span class="inline-flex items-center gap-1 text-[9px] font-black text-rose-500 uppercase tracking-wider animate-pulse">
                                            OVERDUE: Projected <span x-text="projectedEndDate"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-800">
                            <button @click="configModalOpen = true" type="button" class="w-full bg-[#0B5694] hover:bg-[#084577] text-white py-3 rounded-xl text-xs font-bold uppercase tracking-wider transition active:scale-95 shadow-md hover:shadow-lg cursor-pointer flex items-center justify-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                                Configure Weekly Schedule
                            </button>
                        </div>
                    </div>

                    {{-- Schedule Configuration Modal --}}
                    <div x-show="configModalOpen" 
                         class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6 bg-gray-900/60 dark:bg-black/80 backdrop-blur-sm" 
                         x-cloak
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0">
                        
                        <div @click.away="configModalOpen = false" 
                             class="bg-white dark:bg-gray-900 w-full max-w-lg rounded-[30px] sm:rounded-[40px] shadow-2xl overflow-hidden border border-gray-100 dark:border-gray-800">
                            
                            <div class="p-6 sm:p-8">
                                <div class="flex justify-between items-start mb-6">
                                    <div>
                                        <p class="text-[10px] font-black text-[#0B5694] dark:text-blue-400 uppercase tracking-[0.2em] mb-1">Configuration</p>
                                        <h3 class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white tracking-tight">Weekly Schedule & Hours</h3>
                                    </div>
                                    <button @click="configModalOpen = false" type="button" class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                    </button>
                                </div>
                
                                <form action="{{ route('head-supervisor.interns.update-schedule', $intern->user) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    
                                    <div class="space-y-6">
                                        <div class="space-y-2">
                                            <span class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest block">Active Reporting Days</span>
                                            
                                            <div class="grid grid-cols-5 gap-2">
                                                @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'] as $day)
                                                    <label class="flex items-center gap-2 p-2 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-100 dark:border-gray-700 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                                        <input type="checkbox" name="schedule_assignment[]" value="{{ $day }}" 
                                                            x-model="scheduleDays"
                                                            class="rounded text-[#0B5694] focus:ring-[#0B5694]/20 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700">
                                                        <span class="text-[10px] font-bold text-gray-700 dark:text-gray-200">{{ substr($day, 0, 3) }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                        
                                        <div class="space-y-2">
                                            <span class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest block">Official Start Date</span>
                                            <input type="date" name="start_date" x-model="startDate"
                                                class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700 rounded-xl text-xs font-bold text-gray-800 dark:text-gray-100 focus:ring-[#0B5694]/20 focus:border-[#0B5694] transition">
                                        </div>

                                        <div class="space-y-2">
                                            <span class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest block">Required Internship Hours</span>
                                            <input type="number" name="required_hours" x-model.number="requiredHours" min="1" required
                                                class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700 rounded-xl text-xs font-bold text-gray-800 dark:text-gray-100 focus:ring-[#0B5694]/20 focus:border-[#0B5694] transition">
                                        </div>
                                        
                                        <div class="pt-4 flex flex-col sm:flex-row gap-3">
                                            <button @click="configModalOpen = false" type="button" class="w-full sm:w-auto px-6 py-3.5 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200 rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-gray-200 dark:hover:bg-gray-750 transition active:scale-95 text-center">
                                                Cancel
                                            </button>
                                            <button type="submit" class="flex-1 bg-[#0B5694] hover:bg-[#084577] text-white py-3.5 rounded-xl text-xs font-bold uppercase tracking-wider transition active:scale-95 shadow-md shadow-blue-500/10 cursor-pointer text-center">
                                                Save Changes
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Progress Overview Row --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                @php
                    $targetHours = $intern->required_hours ?? 480; 
                    $renderedInt = round($intern->hours_rendered); 
                    $hoursPercentage = $targetHours > 0 ? min(100, round(($renderedInt / $targetHours) * 100)) : 0; 
                    $isCompleted = optional($intern->certificate)->status === 'published';
                @endphp
                
                <div class="bg-white dark:bg-gray-900 rounded-[20px] sm:rounded-[30px] border border-gray-100 dark:border-gray-800 shadow-sm p-4 sm:p-8 lg:p-10 transition-all">
                    <div class="flex justify-between items-start mb-6">
                        <p class="text-[10px] sm:text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Hours Rendered</p>
                        <span class="text-[10px] sm:text-xs font-black {{ $isCompleted ? 'text-emerald-500' : 'text-[#0B5694] dark:text-blue-400' }}">
                            {{ $hoursPercentage }}%
                        </span>
                    </div>
                    <h3 class="text-3xl sm:text-4xl font-black text-gray-900 dark:text-white tracking-tighter mb-4">
                        {{ $renderedInt }} <span class="text-base sm:text-lg text-gray-400 dark:text-gray-500">/ {{ $targetHours }}</span>
                    </h3>
                    <div class="w-full h-3 bg-gray-100 dark:bg-gray-950 rounded-full overflow-hidden shadow-inner">
                        <div class="h-full {{ $isCompleted ? 'bg-emerald-500' : 'bg-[#0B5694] dark:bg-blue-600' }} rounded-full shadow-md transition-all duration-700" 
                             style="width: {{ $hoursPercentage }}%"></div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-900 rounded-[20px] sm:rounded-[30px] border border-gray-100 dark:border-gray-800 shadow-sm p-4 sm:p-8 lg:p-10 transition-all">
                    <div class="flex justify-between items-start mb-6">
                        <p class="text-[10px] sm:text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Task Completion</p>
                        <span class="text-[10px] sm:text-xs font-black text-[#0B5694] dark:text-blue-400">{{ $completedTasks }} / {{ $totalTasks }} Tasks</span>
                    </div>
                    <h3 class="text-3xl sm:text-4xl font-black text-gray-900 dark:text-white tracking-tighter mb-4">{{ round($taskCompletionRate) }}%</h3>
                    <div class="w-full h-3 bg-gray-100 dark:bg-gray-950 rounded-full overflow-hidden shadow-inner">
                        <div class="h-full bg-indigo-500 dark:bg-indigo-600 rounded-full shadow-md transition-all duration-700" style="width: {{ $taskCompletionRate }}%"></div>
                    </div>
                </div>
            </div>

            {{-- Bottom Row: Details Tabs & Action Center --}}
            <div class="grid grid-cols-1 xl:grid-cols-12 gap-8 items-stretch">
                {{-- Details Tabs --}}
                 <div class="xl:col-span-8 bg-white dark:bg-gray-900 rounded-[20px] sm:rounded-[30px] border border-gray-100 dark:border-gray-800 shadow-sm p-4 sm:p-8 lg:p-10 transition-all flex flex-col h-[400px] sm:h-[500px]">
                    <div class="flex items-center gap-4 sm:gap-8 border-b border-gray-100 dark:border-gray-800 mb-8 pb-4 overflow-x-auto no-scrollbar flex-shrink-0">
                        <button @click="activeTab = 'tasks'" :class="activeTab === 'tasks' ? 'text-[#0B5694] dark:text-blue-400 border-b-2 border-[#0B5694] dark:border-blue-400' : 'text-gray-400 dark:text-gray-500 border-transparent hover:text-gray-600 dark:hover:text-gray-300'" class="pb-4 -mb-[17px] text-[10px] sm:text-xs font-black uppercase tracking-widest transition-colors whitespace-nowrap">
                            Tasks ({{ $intern->tasks->count() }})
                        </button>
                        <button @click="activeTab = 'attendance'" :class="activeTab === 'attendance' ? 'text-[#0B5694] dark:text-blue-400 border-b-2 border-[#0B5694] dark:border-blue-400' : 'text-gray-400 dark:text-gray-500 border-transparent hover:text-gray-600 dark:hover:text-gray-300'" class="pb-4 -mb-[17px] text-[10px] sm:text-xs font-black uppercase tracking-widest transition-colors whitespace-nowrap">
                            Attendance ({{ $intern->attendances->count() }})
                        </button>
                        <button @click="activeTab = 'journals'" :class="activeTab === 'journals' ? 'text-[#0B5694] dark:text-blue-400 border-b-2 border-[#0B5694] dark:border-blue-400' : 'text-gray-400 dark:text-gray-500 border-transparent hover:text-gray-600 dark:hover:text-gray-300'" class="pb-4 -mb-[17px] text-[10px] sm:text-xs font-black uppercase tracking-widest transition-colors whitespace-nowrap">
                            Journals ({{ $intern->journals->count() }})
                        </button>
                        @if($intern->extensions->isNotEmpty())
                        <button @click="activeTab = 'extensions'" :class="activeTab === 'extensions' ? 'text-[#0B5694] dark:text-blue-400 border-b-2 border-[#0B5694] dark:border-blue-400' : 'text-gray-400 dark:text-gray-500 border-transparent hover:text-gray-600 dark:hover:text-gray-300'" class="pb-4 -mb-[17px] text-[10px] sm:text-xs font-black uppercase tracking-widest transition-colors whitespace-nowrap">
                            Extensions ({{ $intern->extensions->count() }})
                        </button>
                        @endif
                    </div>

                    <div x-show="!loading" x-transition.opacity.duration.500ms class="flex-1 overflow-y-auto custom-scrollbar pr-2 flex flex-col">
                        {{-- Tasks Content --}}
                        <div x-show="activeTab === 'tasks'" x-cloak class="flex-1 flex flex-col h-full">
                            <div class="flex-1 flex flex-col overflow-hidden border border-gray-100 dark:border-gray-800 rounded-2xl">
                                <table class="min-w-full text-left h-full flex flex-col">
                                    <thead class="bg-gray-50 dark:bg-gray-950 text-[10px] sm:text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest border-b border-gray-100 dark:border-gray-800 sticky top-0 z-10 flex-shrink-0">
                                        <tr class="flex">
                                            <th class="px-4 sm:px-6 py-4 sm:py-5 flex-1">Task Title</th>
                                            <th class="px-4 sm:px-6 py-4 sm:py-5 w-28 sm:w-40">Due Date</th>
                                            <th class="px-4 sm:px-6 py-4 sm:py-5 w-32 sm:w-44 text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-sm divide-y divide-gray-50 dark:divide-gray-800 flex-1 overflow-y-auto">
                                        @forelse($intern->tasks as $task)
                                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors flex">
                                            <td class="px-4 sm:px-6 py-5 sm:py-6 font-bold text-gray-800 dark:text-gray-100 flex-1">{{ $task->title }}</td>
                                            <td class="px-4 sm:px-6 py-5 sm:py-6 font-black text-gray-500 dark:text-gray-400 text-[10px] sm:text-xs tracking-widest uppercase w-28 sm:w-40 flex items-center">
                                                {{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('M d, Y') : 'No Date' }}
                                            </td>
                                            <td class="px-4 sm:px-6 py-5 sm:py-6 w-32 sm:w-44 flex items-center justify-center">
                                                <span class="px-2.5 py-1 rounded-full text-[8px] sm:text-[9px] font-black uppercase tracking-wider border shadow-sm whitespace-nowrap
                                                    {{ $task->status->value === 'completed' ? 'bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 border-emerald-100 dark:border-emerald-900/50' : 'bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-400 border-amber-100 dark:border-amber-900/50' }}">
                                                    {{ $task->status->value === 'completed' ? 'Verified' : $task->status->label() }}
                                                </span>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr class="flex h-full items-center justify-center min-h-[250px]">
                                            <td colspan="3" class="px-4 sm:px-6 py-10 text-center text-gray-400 dark:text-gray-500 italic font-medium flex-1">No tasks assigned.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Attendance Content --}}
                        <div x-show="activeTab === 'attendance'" x-cloak class="flex-1 flex flex-col h-full">
                            <div class="flex-1 flex flex-col overflow-hidden border border-gray-100 dark:border-gray-800 rounded-2xl">
                                <table class="min-w-full text-left h-full flex flex-col">
                                    <thead class="bg-gray-50 dark:bg-gray-950 text-[10px] sm:text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest border-b border-gray-100 dark:border-gray-800 sticky top-0 z-10 flex-shrink-0">
                                        <tr class="flex">
                                            <th class="px-3 sm:px-4 py-4 sm:py-5 w-24 sm:w-36">Date</th>
                                            <th class="px-3 sm:px-4 py-4 sm:py-5 w-20 sm:w-32">Time In</th>
                                            <th class="px-4 sm:px-4 py-4 sm:py-5 w-20 sm:w-32">Time Out</th>
                                            <th class="px-3 sm:px-4 py-4 sm:py-5 w-24 sm:w-32 text-center">Status</th>
                                            <th class="px-3 sm:px-4 py-4 sm:py-5 flex-1">Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-sm divide-y divide-gray-50 dark:divide-gray-800 flex-1 overflow-y-auto">
                                        @forelse($intern->attendances as $attendance)
                                        <tr x-show="attendancePage === Math.ceil({{ $loop->iteration }} / attendancePerPage)" class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors flex">
                                            <td class="px-3 sm:px-4 py-5 sm:py-6 font-bold text-gray-500 dark:text-gray-400 uppercase tracking-tighter w-24 sm:w-36 flex items-center">
                                                {{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('M d, Y') : '--' }}
                                            </td>
                                            <td class="px-3 sm:px-4 py-5 sm:py-6 font-bold text-gray-800 dark:text-gray-100 w-20 sm:w-32 flex items-center">
                                                {{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('h:i A') : '--' }}
                                            </td>
                                            <td class="px-3 sm:px-4 py-5 sm:py-6 font-bold text-gray-800 dark:text-gray-100 w-20 sm:w-32 flex items-center">
                                                {{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('h:i A') : '--' }}
                                            </td>
                                            <td class="px-3 sm:px-4 py-5 sm:py-6 w-24 sm:w-32 flex items-center justify-center">
                                                @php
                                                    $status = $attendance->status;
                                                    if (!$attendance->clock_out && $attendance->clock_in) $status = 'active';

                                                    $statusConfig = match($status) {
                                                        'late' => ['label' => 'Late', 'class' => 'bg-orange-50 dark:bg-orange-950/30 text-orange-500 dark:text-orange-400 border-orange-100 dark:border-orange-900/50'],
                                                        'absent' => ['label' => 'Absent', 'class' => 'bg-rose-50 dark:bg-rose-950/30 text-rose-500 dark:text-rose-400 border-rose-100 dark:border-rose-900/50'],
                                                        'excused' => ['label' => 'Excused', 'class' => 'bg-blue-50 dark:bg-blue-950/30 text-blue-600 dark:text-blue-400 border-blue-100 dark:border-blue-900/50'],
                                                        'active' => ['label' => 'Active', 'class' => 'bg-blue-50 dark:bg-blue-950/30 text-blue-500 dark:text-blue-400 border-blue-100 dark:border-blue-900/50 animate-pulse'],
                                                        default => ['label' => 'On-Time', 'class' => 'bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 border-emerald-100 dark:border-emerald-900/50'],
                                                    };
                                                @endphp
                                                <span class="{{ $statusConfig['class'] }} text-[9px] font-black px-3 py-1.5 rounded-full uppercase tracking-widest border shadow-sm">
                                                    {{ $statusConfig['label'] }}
                                                </span>
                                            </td>
                                            <td class="px-3 sm:px-4 py-5 sm:py-6 flex-1 flex items-center">
                                                <span class="text-[10px] text-gray-400 dark:text-gray-500 font-bold uppercase tracking-tight italic line-clamp-1" title="{{ $attendance->remarks }}">
                                                    {{ $attendance->remarks ?? '---' }}
                                                </span>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr class="flex h-full items-center justify-center min-h-[250px]">
                                            <td colspan="5" class="px-4 sm:px-6 py-10 text-center text-gray-400 dark:text-gray-500 italic font-medium flex-1">No attendance records.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            
                            {{-- Pagination Controls --}}
                            @if($intern->attendances->count() > 10)
                            <div class="mt-4 flex items-center justify-between px-4 py-3 bg-gray-50 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800 rounded-2xl">
                                <div class="flex-1 flex justify-between sm:hidden">
                                    <button @click="if(attendancePage > 1) attendancePage--" 
                                            :disabled="attendancePage === 1"
                                            class="relative inline-flex items-center px-4 py-2 border border-gray-200 dark:border-gray-700 text-[10px] font-black uppercase tracking-widest rounded-xl text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 transition active:scale-95">
                                        Previous
                                    </button>
                                    <button @click="if(attendancePage < Math.ceil({{ $intern->attendances->count() }} / attendancePerPage)) attendancePage++" 
                                            :disabled="attendancePage >= Math.ceil({{ $intern->attendances->count() }} / attendancePerPage)"
                                            class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-200 dark:border-gray-700 text-[10px] font-black uppercase tracking-widest rounded-xl text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 transition active:scale-95">
                                        Next
                                    </button>
                                </div>
                                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                    <div>
                                        <p class="text-[10px] text-gray-500 dark:text-gray-400 font-bold uppercase tracking-tight">
                                            Showing page <span class="font-extrabold text-[#0B5694] dark:text-blue-400" x-text="attendancePage"></span> of <span class="font-extrabold text-gray-800 dark:text-gray-200" x-text="Math.ceil({{ $intern->attendances->count() }} / attendancePerPage)"></span>
                                            (<span class="font-semibold" x-text="{{ $intern->attendances->count() }}"></span> total logs)
                                        </p>
                                    </div>
                                    <div>
                                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                            <button @click="if(attendancePage > 1) attendancePage--" 
                                                    :disabled="attendancePage === 1"
                                                    class="relative inline-flex items-center px-3 py-2 rounded-l-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-[10px] font-bold text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-750 disabled:opacity-40 transition shrink-0 active:scale-95">
                                                <span class="sr-only">Previous</span>
                                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                            
                                            <button @click="if(attendancePage < Math.ceil({{ $intern->attendances->count() }} / attendancePerPage)) attendancePage++" 
                                                    :disabled="attendancePage >= Math.ceil({{ $intern->attendances->count() }} / attendancePerPage)"
                                                    class="relative inline-flex items-center px-3 py-2 rounded-r-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-[10px] font-bold text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-750 disabled:opacity-40 transition shrink-0 active:scale-95">
                                                <span class="sr-only">Next</span>
                                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </nav>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>

                        {{-- Journals Content --}}
                        <div x-show="activeTab === 'journals'" x-cloak class="flex-1 flex flex-col h-full">
                            <div class="flex-1 flex flex-col overflow-hidden border border-gray-100 dark:border-gray-800 rounded-2xl">
                                <table class="min-w-full text-left h-full flex flex-col">
                                    <thead class="bg-gray-50 dark:bg-gray-950 text-[10px] sm:text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest border-b border-gray-100 dark:border-gray-800 sticky top-0 z-10 flex-shrink-0">
                                        <tr class="flex">
                                            <th class="px-4 sm:px-6 py-4 sm:py-5 w-28 sm:w-40">Entry Date</th>
                                            <th class="px-4 sm:px-6 py-4 sm:py-5 flex-1">Journal Title</th>
                                            <th class="px-4 sm:px-6 py-4 sm:py-5 w-24 sm:w-32 text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-sm divide-y divide-gray-50 dark:divide-gray-800 flex-1 overflow-y-auto">
                                        @forelse($intern->journals as $journal)
                                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors flex">
                                            <td class="px-4 sm:px-6 py-5 sm:py-6 font-black text-gray-500 dark:text-gray-400 text-[10px] sm:text-xs tracking-widest uppercase w-28 sm:w-40 flex items-center">
                                                {{ \Carbon\Carbon::parse($journal->entry_date)->format('M d, Y') }}
                                            </td>
                                            <td class="px-4 sm:px-6 py-5 sm:py-6 font-bold text-gray-800 dark:text-gray-100 flex-1 flex items-center">
                                                {{ $journal->title }}
                                            </td>
                                            <td class="px-4 sm:px-6 py-5 sm:py-6 w-24 sm:w-32 text-right flex items-center justify-end">
                                                <button @click="modalOpen = true; selectedJournal = { 
                                                    title: {!! e(json_encode($journal->title)) !!}, 
                                                    date: '{{ \Carbon\Carbon::parse($journal->entry_date)->format('M d, Y') }}', 
                                                    content: {!! e(json_encode($journal->content)) !!} 
                                                }" class="px-4 sm:px-5 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-[9px] sm:text-[10px] font-black text-[#0B5694] dark:text-blue-400 uppercase tracking-widest hover:bg-blue-50 dark:hover:bg-gray-700 transition shadow-sm">
                                                    View
                                                </button>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr class="flex h-full items-center justify-center min-h-[250px]">
                                            <td colspan="3" class="px-4 sm:px-6 py-10 text-center text-gray-400 dark:text-gray-500 italic font-medium flex-1">No journal entries.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Extensions Content --}}
                        @if($intern->extensions->isNotEmpty())
                        <div x-show="activeTab === 'extensions'" x-cloak class="flex-1 flex flex-col h-full">
                            <div class="flex-1 flex flex-col overflow-hidden border border-gray-100 dark:border-gray-800 rounded-2xl">
                                <table class="min-w-full text-left h-full flex flex-col">
                                    <thead class="bg-gray-50 dark:bg-gray-950 text-[10px] sm:text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest border-b border-gray-100 dark:border-gray-800 sticky top-0 z-10 flex-shrink-0">
                                        <tr class="flex">
                                            <th class="px-4 sm:px-6 py-4 sm:py-5 w-28 sm:w-40">Submitted</th>
                                            <th class="px-4 sm:px-6 py-4 sm:py-5 w-32 sm:w-44">New End Date</th>
                                            <th class="px-4 sm:px-6 py-4 sm:py-5 flex-1">Reason / Status</th>
                                            <th class="px-4 sm:px-6 py-4 sm:py-5 w-32 sm:w-44 text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-sm divide-y divide-gray-50 dark:divide-gray-800 flex-1 overflow-y-auto">
                                        @foreach($intern->extensions as $ext)
                                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors flex">
                                            <td class="px-4 sm:px-6 py-5 sm:py-6 font-black text-gray-500 dark:text-gray-400 text-[10px] sm:text-xs tracking-widest uppercase w-28 sm:w-40 flex items-center">
                                                {{ $ext->created_at->format('M d, Y') }}
                                            </td>
                                            <td class="px-4 sm:px-6 py-5 sm:py-6 font-black text-gray-800 dark:text-white text-[10px] sm:text-xs tracking-widest uppercase w-32 sm:w-44 flex items-center">
                                                {{ $ext->new_end_date->format('M d, Y') }}
                                            </td>
                                            <td class="px-4 sm:px-6 py-5 sm:py-6 flex-1 flex flex-col justify-center min-w-0">
                                                <p class="font-bold text-gray-700 dark:text-gray-300 truncate" title="{{ $ext->reason }}">{{ $ext->reason }}</p>
                                                <div class="mt-1 flex items-center gap-2">
                                                    @if($ext->status === 'pending')
                                                        <span class="px-2 py-0.5 rounded-full text-[9px] bg-amber-50 text-amber-600 border border-amber-200 uppercase font-black tracking-wider">Pending</span>
                                                    @elseif($ext->status === 'approved')
                                                        <span class="px-2 py-0.5 rounded-full text-[9px] bg-emerald-50 text-emerald-600 border border-emerald-200 uppercase font-black tracking-wider">Approved</span>
                                                    @else
                                                        <span class="px-2 py-0.5 rounded-full text-[9px] bg-rose-50 text-rose-600 border border-rose-200 uppercase font-black tracking-wider">Rejected</span>
                                                        @if($ext->rejection_note)
                                                            <span class="text-[10px] text-gray-400 italic">({{ $ext->rejection_note }})</span>
                                                        @endif
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-4 sm:px-6 py-5 sm:py-6 w-32 sm:w-44 text-right flex items-center justify-end gap-2">
                                                @if($ext->status === 'pending')
                                                    <form action="{{ route('head-supervisor.interns.resolve-extension', $ext) }}" method="POST" class="inline">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="status" value="approved">
                                                        <button type="submit" class="px-3 py-1.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg text-[10px] font-black uppercase tracking-wider transition active:scale-95 shadow-sm cursor-pointer">
                                                            Approve
                                                        </button>
                                                    </form>
                                                     <button @click="$dispatch('open-reject-modal', { url: '{{ route('head-supervisor.interns.resolve-extension', $ext) }}' })" class="px-3 py-1.5 bg-rose-500 hover:bg-rose-600 text-white rounded-lg text-[10px] font-black uppercase tracking-wider transition active:scale-95 shadow-sm cursor-pointer">
                                                         Reject
                                                     </button>
                                                @else
                                                    <span class="text-xs text-gray-400 italic">Resolved</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Action Center --}}
                <div class="xl:col-span-4">
                    <div class="bg-white dark:bg-gray-900 rounded-[20px] sm:rounded-[30px] border border-gray-100 dark:border-gray-800 shadow-sm p-4 sm:p-8 lg:p-10 h-full flex flex-col">
                        <div class="mb-8">
                            <h4 class="text-xl font-black text-gray-900 dark:text-white uppercase tracking-tight">Action Center</h4>
                            <p class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em] mt-1 italic">Quick Administrative Shortcuts</p>
                        </div>

                        <div class="space-y-4 flex-1 flex flex-col">
                            <a href="{{ route('head-supervisor.certificates.index') }}" class="flex-1 flex flex-col gap-4 p-6 bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700 rounded-[25px] group hover:bg-[#0B5694] dark:hover:bg-blue-600 transition-all duration-300 justify-center">
                                <div class="w-12 h-12 bg-white dark:bg-gray-900 rounded-2xl flex items-center justify-center text-[#0B5694] dark:text-blue-400 group-hover:bg-white/10 group-hover:text-white transition-colors shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                </div>
                                <div>
                                    <p class="text-xs font-black text-gray-800 dark:text-gray-100 uppercase tracking-tight group-hover:text-white transition-colors">Manage Certificates</p>
                                    <p class="text-[9px] text-gray-400 dark:text-gray-500 font-bold uppercase tracking-widest group-hover:text-blue-100 transition-colors mt-1">Endorsement & Publishing</p>
                                </div>
                            </a>

                            <a href="{{ route('head-supervisor.report') }}" class="flex-1 flex flex-col gap-4 p-6 bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700 rounded-[25px] group hover:bg-[#0B5694] dark:hover:bg-blue-600 transition-all duration-300 justify-center">
                                <div class="w-12 h-12 bg-white dark:bg-gray-900 rounded-2xl flex items-center justify-center text-[#0B5694] dark:text-blue-400 group-hover:bg-white/10 group-hover:text-white transition-colors shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"></path><path d="M22 12A10 10 0 0 0 12 2v10z"></path></svg>
                                </div>
                                <div>
                                    <p class="text-xs font-black text-gray-800 dark:text-gray-100 uppercase tracking-tight group-hover:text-white transition-colors">Division Analytics</p>
                                    <p class="text-[9px] text-gray-400 dark:text-gray-500 font-bold uppercase tracking-widest group-hover:text-blue-100 transition-colors mt-1">Review Performance Trends</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    
        {{-- Journal Detail Modal --}}
        <div x-show="modalOpen" 
             class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6 bg-gray-900/60 dark:bg-black/80 backdrop-blur-sm" 
             x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <div @click.away="modalOpen = false" 
                 class="bg-white dark:bg-gray-900 w-full max-w-2xl rounded-[30px] sm:rounded-[40px] shadow-2xl overflow-hidden border border-gray-100 dark:border-gray-800">
                
                <div class="p-6 sm:p-8 lg:p-10">
                    <div class="flex justify-between items-start mb-6 sm:mb-8">
                        <div>
                            <span class="text-[9px] sm:text-[10px] font-black text-[#0B5694] dark:text-blue-400 uppercase tracking-[0.2em] mb-2 block" x-text="selectedJournal.date"></span>
                            <h3 class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white tracking-tight" x-text="selectedJournal.title"></h3>
                        </div>
                        <button @click="modalOpen = false" class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                        </button>
                    </div>
    
                    <div class="bg-gray-50 dark:bg-gray-950 rounded-[20px] sm:rounded-[30px] p-6 sm:p-8 max-h-[50vh] overflow-y-auto border border-gray-100 dark:border-gray-800 shadow-inner">
                        <p class="text-gray-600 dark:text-gray-400 text-sm sm:text-base leading-relaxed italic whitespace-pre-wrap" x-text="selectedJournal.content"></p>
                    </div>
    
                    <div class="mt-8 sm:mt-10 flex justify-end">
                        <button @click="modalOpen = false" class="w-full sm:w-auto px-10 py-4 bg-gray-900 dark:bg-gray-800 text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-gray-800 dark:hover:bg-gray-700 transition shadow-lg active:scale-95">
                            Back to Profile
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Rejection Reason Modal --}}
        <div x-data="{ rejectModalOpen: false, rejectionNote: '', rejectActionUrl: '' }"
             x-show="rejectModalOpen" 
             @open-reject-modal.window="rejectModalOpen = true; rejectActionUrl = $event.detail.url; rejectionNote = '';"
             class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6 bg-gray-900/60 dark:bg-black/80 backdrop-blur-sm" 
             x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <div @click.away="rejectModalOpen = false" 
                 class="bg-white dark:bg-gray-900 w-full max-w-2xl rounded-[30px] sm:rounded-[40px] shadow-2xl overflow-hidden border border-gray-100 dark:border-gray-800">
                
                <div class="p-8 sm:p-12">
                    <div class="flex justify-between items-start mb-8">
                        <div>
                            <span class="text-xs sm:text-sm font-black text-rose-500 uppercase tracking-[0.2em] mb-1.5 block">Resolve Request</span>
                            <h3 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white tracking-tight uppercase">Reject Extension Request</h3>
                        </div>
                        <button @click="rejectModalOpen = false" class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                        </button>
                    </div>
    
                    <form :action="rejectActionUrl" method="POST" class="space-y-8">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="rejected">

                        <div class="space-y-3 text-left">
                            <span class="text-xs sm:text-sm font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest block">Reason for Rejection</span>
                            <textarea name="rejection_note" x-model="rejectionNote" required rows="4" placeholder="Describe the reason why this extension request is being rejected..."
                                class="w-full px-5 py-4 bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700 rounded-2xl text-sm font-semibold text-gray-800 dark:text-gray-100 focus:ring-[#0B5694]/20 focus:border-[#0B5694] transition resize-none"></textarea>
                        </div>
                        
                        <div class="pt-6 flex flex-col sm:flex-row gap-4">
                            <button @click="rejectModalOpen = false" type="button" class="w-full sm:w-auto px-8 py-4 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200 rounded-xl font-bold text-sm uppercase tracking-widest hover:bg-gray-200 dark:hover:bg-gray-750 transition active:scale-95 text-center cursor-pointer">
                                Cancel
                            </button>
                            <button type="submit" class="flex-1 bg-rose-600 hover:bg-rose-700 text-white py-4 px-8 rounded-xl text-sm font-bold uppercase tracking-wider transition active:scale-95 shadow-md shadow-rose-500/10 cursor-pointer text-center">
                                Submit Rejection
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-page-layout>
@endsection
