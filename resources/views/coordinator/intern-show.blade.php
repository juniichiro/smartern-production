@extends('layouts.app')

@section('title', $intern->first_name . ' ' . $intern->last_name )

@push('styles')
<style>
    @media print {
        aside, .sidebar-wrapper, .notification-sidebar, .x-page-header-actions, button, a[href^="mailto:"] { display: none !important; }
        main { width: 100% !important; margin: 0 !important; padding: 0 !important; }
        .flex-col.lg\:flex-row { display: block !important; }
        body { background: white !important; color: black !important; }
        .bg-white, .dark\:bg-gray-900 { background: white !important; }
        .text-gray-800, .dark\:text-gray-100 { color: black !important; }
    }
    
    .progress-ring__circle {
        transition: stroke-dashoffset 0.8s ease-in-out;
        transform: rotate(-90deg);
        transform-origin: 50% 50%;
    }
</style>
@endpush

@section('content')
<x-page-layout 
    portalType="School Coordinator" 
    userName="{{ Auth::user()->schoolCoordinatorDetails->first_name }}" 
    userRole="School Coordinator"
>
    {{-- Header Section --}}
    <x-page-header 
        title="Student Portfolio" 
        subtitle="Detailed training progress and attendance history" 
    >
        <x-slot name="actions">
            <div class="flex items-center gap-3">
                <button onclick="window.print()" class="flex items-center gap-2 px-6 py-3 bg-[#0B5694] text-white rounded-2xl text-[10px] font-black hover:bg-blue-700 transition-all shadow-lg shadow-blue-900/20 uppercase tracking-widest active:scale-95">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                    Download Report
                </button>
                <button onclick="window.history.back()" class="flex items-center gap-2 px-6 py-3 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl text-[10px] font-black text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 transition-all shadow-sm uppercase tracking-widest active:scale-95">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    Back to List
                </button>
            </div>
        </x-slot>
    </x-page-header>

    {{-- TOP INSIGHTS BANNER (HYBRID OPTION 1) --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-10">
        {{-- Health Status --}}
        <div class="bg-white dark:bg-gray-950 border border-gray-100 dark:border-gray-800 p-6 rounded-[30px] shadow-sm flex items-center gap-5">
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 {{ 
                $insights['healthStatus'] === 'at-risk' ? 'bg-rose-50 dark:bg-rose-900/20 text-rose-500' : 
                ($insights['healthStatus'] === 'excellent' ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-500' : 'bg-blue-50 dark:bg-blue-900/20 text-blue-500') 
            }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21.66V12l8.32-4.99"/><path d="m3.08 9 8.92 5.34 8.92-5.34L12 3.66Z"/><path d="M12 12 3.68 7.01"/></svg>
            </div>
            <div class="min-w-0">
                <p class="text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Training Status</p>
                <p class="text-[13px] font-black text-gray-800 dark:text-gray-100 uppercase tracking-tight truncate">{{ $insights['healthLabel'] }}</p>
            </div>
        </div>

        {{-- Punctuality Rate --}}
        <div class="bg-white dark:bg-gray-950 border border-gray-100 dark:border-gray-800 p-6 rounded-[30px] shadow-sm flex items-center gap-5">
            <div class="w-12 h-12 rounded-2xl bg-orange-50 dark:bg-orange-900/20 text-orange-500 flex items-center justify-center shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div class="min-w-0">
                <p class="text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Punctuality</p>
                <p class="text-[13px] font-black text-gray-800 dark:text-gray-100 uppercase tracking-tight truncate">{{ $insights['punctuality'] }}% On-Time</p>
            </div>
        </div>

        {{-- Estimated Finish --}}
        <div class="bg-white dark:bg-gray-950 border border-gray-100 dark:border-gray-800 p-6 rounded-[30px] shadow-sm flex items-center gap-5">
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-900/20 text-emerald-500 flex items-center justify-center shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <div class="min-w-0">
                <p class="text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Target Finish</p>
                <p class="text-[13px] font-black text-gray-800 dark:text-gray-100 uppercase tracking-tight truncate">~{{ $insights['estDaysRemaining'] }} Days Left</p>
            </div>
        </div>

        {{-- Last Activity --}}
        <div class="bg-white dark:bg-gray-950 border border-gray-100 dark:border-gray-800 p-6 rounded-[30px] shadow-sm flex items-center gap-5">
            <div class="w-12 h-12 rounded-2xl bg-slate-50 dark:bg-slate-900/20 text-slate-500 flex items-center justify-center shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20"/><path d="m4.93 4.93 14.14 14.14"/><path d="M2 12h20"/><path d="m19.07 4.93-14.14 14.14"/></svg>
            </div>
            <div class="min-w-0">
                <p class="text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Last Activity</p>
                <p class="text-[13px] font-black text-gray-800 dark:text-gray-100 uppercase tracking-tight truncate">{{ $insights['lastActivity'] }}</p>
            </div>
        </div>
    </div>

    <div class="flex flex-col lg:flex-row gap-12 items-start">
        
        {{-- LEFT COLUMN: STICKY BIO & STATS --}}
        <aside class="w-full lg:w-[380px] lg:sticky lg:top-10 flex flex-col gap-8 shrink-0">
            
            {{-- Profile Bio Card --}}
            <x-card-container padding="p-8" class="relative overflow-hidden group">
                <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50/50 dark:bg-blue-900/10 rounded-bl-full -z-0 transition-transform group-hover:scale-110"></div>
                
                <div class="relative z-10 text-center md:text-left">
                    <x-avatar :user="$intern" size="32" circular="false" border="4" textSize="4xl" class="mx-auto md:mx-0 shadow-xl" />
                    
                    <div class="mt-8 space-y-2">
                        <h3 class="text-2xl font-black text-gray-900 dark:text-gray-100 tracking-tight uppercase leading-none">
                            {{ $intern->first_name }} {{ $intern->last_name }}
                        </h3>
                        <p class="text-[10px] text-gray-400 dark:text-slate-500 font-black uppercase tracking-[0.2em]">{{ $intern->course }}</p>
                        <div class="flex flex-col gap-1 mt-2">
                            <p class="text-[11px] text-gray-400 dark:text-slate-500 font-black uppercase tracking-[0.3em]">Student ID: {{ $intern->user->display_id }}</p>
                            @if($intern->phone_number)
                                <p class="text-[10px] text-gray-400 dark:text-slate-500 font-bold uppercase tracking-widest">{{ $intern->phone_number }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2 mt-6 justify-center md:justify-start">
                        @php $isCompleted = optional($intern->certificate)->status === 'published'; @endphp
                        @if($isCompleted)
                            <span class="px-3 py-1.5 bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 text-[9px] font-black rounded-full border border-emerald-100 dark:border-emerald-900/50 uppercase tracking-widest">Certified</span>
                        @elseif($renderedHours >= $requiredHours)
                            <span class="px-3 py-1.5 bg-orange-50 dark:bg-orange-950/30 text-orange-600 dark:text-orange-400 text-[9px] font-black rounded-full border border-orange-100 dark:border-orange-900/50 uppercase tracking-widest shadow-sm animate-pulse">Endorsing</span>
                        @else
                            <span class="px-3 py-1.5 bg-blue-50 dark:bg-blue-950/30 text-[#0B5694] dark:text-blue-400 text-[9px] font-black rounded-full border border-blue-100 dark:border-blue-900/50 uppercase tracking-widest">Training Active</span>
                        @endif
                    </div>
                </div>
            </x-card-container>

            {{-- Deployment Info Card --}}
            <x-card-container title="Deployment Details" padding="p-8">
                <div class="space-y-5 mt-2">
                    @if($schoolSchedule && $schoolSchedule->start_date)
                    <div class="p-5 bg-blue-50/50 dark:bg-blue-900/10 rounded-2xl border border-blue-100 dark:border-blue-900/20 shadow-sm">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-8 h-8 bg-white dark:bg-gray-900 rounded-lg flex items-center justify-center text-[#0B5694] dark:text-blue-400 shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            </div>
                            <p class="text-[10px] font-black text-[#0B5694] dark:text-blue-400 uppercase tracking-widest">Program Timeline</p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-[9px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1">Official Start</p>
                                <p class="text-[11px] font-black text-gray-800 dark:text-gray-100 uppercase">{{ $schoolSchedule->start_date->format('M d, Y') }}</p>
                            </div>
                            @if($schoolSchedule->target_end_date)
                            <div>
                                <p class="text-[9px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1">Target End</p>
                                <p class="text-[11px] font-black text-gray-800 dark:text-gray-100 uppercase">{{ $schoolSchedule->target_end_date->format('M d, Y') }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <div class="p-5 bg-blue-50/30 dark:bg-blue-900/5 rounded-2xl border border-blue-100/50 dark:border-blue-900/20">
                        <p class="text-[10px] font-black text-[#0B5694] dark:text-blue-400 uppercase tracking-widest mb-1.5">Department</p>
                        <p class="text-base font-black text-gray-800 dark:text-gray-100 uppercase truncate">{{ $intern->office->name ?? 'N/A' }}</p>
                    </div>
                    
                    <div class="p-5 bg-gray-50 dark:bg-gray-800/50 rounded-2xl border border-gray-100 dark:border-gray-800">
                        <p class="text-[10px] font-black text-gray-400 dark:text-slate-500 uppercase tracking-widest mb-3">Assigned Supervisor</p>
                        @if($supervisor)
                            <div class="flex items-center gap-4">
                                <x-avatar :user="$supervisor->user" size="10" border="0" circular="true" textSize="sm" />
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-black text-gray-800 dark:text-gray-100 uppercase truncate leading-none mb-1">{{ $supervisor->first_name }} {{ $supervisor->last_name }}</p>
                                    <p class="text-[11px] text-gray-400 font-bold truncate mb-3">{{ $supervisor->user->email }}</p>
                                    <a href="mailto:{{ $supervisor->user->email }}" class="inline-flex items-center gap-1.5 text-[10px] font-black text-[#0B5694] dark:text-blue-400 uppercase tracking-widest hover:opacity-70 transition-opacity">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                                        Contact Supervisor
                                    </a>
                                </div>
                            </div>
                        @else
                            <p class="text-sm font-bold text-gray-400 italic">No supervisor assigned</p>
                        @endif
                    </div>
                </div>
            </x-card-container>

            {{-- PRIVATE NOTES SECTION (HYBRID OPTION 1) --}}
            <x-card-container title="My Reminders" padding="p-8">
                <div class="space-y-4" x-data="{ 
                    notes: '', 
                    key: 'coordinator_notes_{{ $intern->id }}',
                    saveSuccess: false,
                    init() {
                        this.notes = localStorage.getItem(this.key) || '';
                    },
                    save() {
                        localStorage.setItem(this.key, this.notes);
                        this.saveSuccess = true;
                        setTimeout(() => this.saveSuccess = false, 2000);
                    }
                }">
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest leading-relaxed italic">Stored locally in your browser. Only visible on this device.</p>
                    <textarea x-model="notes" 
                              placeholder="Type a reminder here..." 
                              class="w-full bg-gray-50 dark:bg-gray-800 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-2xl p-4 text-[13px] text-gray-700 dark:text-gray-300 min-h-[120px] focus:ring-0 focus:border-[#0B5694] transition-all resize-none"></textarea>
                    <button @click="save()" 
                            class="w-full py-3 text-[10px] font-black uppercase tracking-[0.2em] rounded-xl transition-all shadow-sm"
                            :class="saveSuccess ? 'bg-emerald-500 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 hover:bg-[#0B5694] hover:text-white'">
                        <span x-text="saveSuccess ? 'Notes Saved!' : 'Update Notes'"></span>
                    </button>
                </div>
            </x-card-container>
        </aside>

        {{-- RIGHT COLUMN: CONTENT WORKSPACE --}}
        <div x-data="{ activeTab: 'timeline' }" class="flex-1 min-w-0 space-y-12">
            
            {{-- VISUAL PROGRESS GAUGES (HYBRID OPTION 2) --}}
            <section class="grid grid-cols-1 md:grid-cols-3 gap-8">
                {{-- Hours Progress Gauge --}}
                <div class="bg-white dark:bg-gray-950 p-8 rounded-[40px] border border-gray-100 dark:border-gray-800 shadow-sm text-center relative overflow-hidden group">
                    <div class="relative z-10">
                        <div class="flex justify-center mb-6">
                            <div class="relative flex items-center justify-center">
                                @php $hourPercent = min(100, round(($renderedHours / $requiredHours) * 100)); @endphp
                                <svg class="w-32 h-32 transform -rotate-90">
                                    <circle cx="64" cy="64" r="58" stroke="currentColor" stroke-width="8" fill="transparent" class="text-gray-100 dark:text-gray-800" />
                                    <circle cx="64" cy="64" r="58" stroke="currentColor" stroke-width="8" fill="transparent" 
                                            stroke-dasharray="364.4" 
                                            stroke-dashoffset="{{ 364.4 - (364.4 * $hourPercent) / 100 }}"
                                            class="text-[#0B5694] dark:text-blue-500 drop-shadow-[0_0_8px_rgba(11,86,148,0.3)]" />
                                </svg>
                                <div class="absolute inset-0 flex flex-col items-center justify-center">
                                    <span class="text-2xl font-black text-gray-800 dark:text-gray-100 tracking-tighter">{{ $hourPercent }}%</span>
                                </div>
                            </div>
                        </div>
                        <h5 class="text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-[0.3em] mb-1">Hours Rendered</h5>
                        <p class="text-xl font-black text-gray-800 dark:text-gray-100 tracking-tight">{{ round($renderedHours) }} / {{ $requiredHours }}H</p>
                    </div>
                </div>

                {{-- Task Progress Gauge --}}
                <div class="bg-white dark:bg-gray-950 p-8 rounded-[40px] border border-gray-100 dark:border-gray-800 shadow-sm text-center relative overflow-hidden group">
                    <div class="relative z-10">
                        <div class="flex justify-center mb-6">
                            <div class="relative flex items-center justify-center">
                                @php $taskPercent = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0; @endphp
                                <svg class="w-32 h-32 transform -rotate-90">
                                    <circle cx="64" cy="64" r="58" stroke="currentColor" stroke-width="8" fill="transparent" class="text-gray-100 dark:text-gray-800" />
                                    <circle cx="64" cy="64" r="58" stroke="currentColor" stroke-width="8" fill="transparent" 
                                            stroke-dasharray="364.4" 
                                            stroke-dashoffset="{{ 364.4 - (364.4 * $taskPercent) / 100 }}"
                                            class="text-emerald-500 dark:text-emerald-400 drop-shadow-[0_0_8px_rgba(16,185,129,0.3)]" />
                                </svg>
                                <div class="absolute inset-0 flex flex-col items-center justify-center">
                                    <span class="text-2xl font-black text-gray-800 dark:text-gray-100 tracking-tighter">{{ $taskPercent }}%</span>
                                </div>
                            </div>
                        </div>
                        <h5 class="text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-[0.3em] mb-1">Tasks Completed</h5>
                        <p class="text-xl font-black text-gray-800 dark:text-gray-100 tracking-tight">{{ $completedTasks }} / {{ $totalTasks }}</p>
                    </div>
                </div>

                {{-- Attendance Summary Card --}}
                <div class="bg-white dark:bg-gray-950 p-8 rounded-[40px] border border-gray-100 dark:border-gray-800 shadow-sm text-center relative overflow-hidden group flex flex-col justify-center">
                    <div class="space-y-6">
                        <div class="flex justify-center gap-10">
                            <div>
                                <p class="text-4xl font-black text-rose-500 tracking-tighter leading-none">{{ $absents }}</p>
                                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mt-3">Absents</p>
                            </div>
                            <div class="w-[1px] h-12 bg-gray-100 dark:bg-gray-800 self-center"></div>
                            <div>
                                <p class="text-4xl font-black text-orange-500 tracking-tighter leading-none">{{ $lates }}</p>
                                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mt-3">Lates</p>
                            </div>
                        </div>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">{{ ($absents + $lates) > 0 ? 'Requires attention' : 'No attendance issues' }}</p>
                    </div>
                </div>
            </section>

            {{-- TAB NAVIGATION --}}
            <nav class="flex items-center gap-1 p-1 bg-gray-50/50 dark:bg-gray-800/50 rounded-2xl w-fit border border-gray-100 dark:border-gray-800">
                <button @click="activeTab = 'timeline'" 
                        :class="activeTab === 'timeline' ? 'bg-white dark:bg-gray-700 text-[#0B5694] dark:text-blue-400 shadow-sm border-gray-100 dark:border-gray-600' : 'text-gray-400 dark:text-slate-500 border-transparent hover:text-gray-600 dark:hover:text-gray-300'"
                        class="px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest border transition-all">
                    Training Log
                </button>
                <button @click="activeTab = 'journals'" 
                        :class="activeTab === 'journals' ? 'bg-white dark:bg-gray-700 text-[#0B5694] dark:text-blue-400 shadow-sm border-gray-100 dark:border-gray-600' : 'text-gray-400 dark:text-slate-500 border-transparent hover:text-gray-600 dark:hover:text-gray-300'"
                        class="px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest border transition-all">
                    Journals
                </button>
                <button @click="activeTab = 'attendance'" 
                        :class="activeTab === 'attendance' ? 'bg-white dark:bg-gray-700 text-[#0B5694] dark:text-blue-400 shadow-sm border-gray-100 dark:border-gray-600' : 'text-gray-400 dark:text-slate-500 border-transparent hover:text-gray-600 dark:hover:text-gray-300'"
                        class="px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest border transition-all">
                    Attendance
                </button>
                <button @click="activeTab = 'tasks'" 
                        :class="activeTab === 'tasks' ? 'bg-white dark:bg-gray-700 text-[#0B5694] dark:text-blue-400 shadow-sm border-gray-100 dark:border-gray-600' : 'text-gray-400 dark:text-slate-500 border-transparent hover:text-gray-600 dark:hover:text-gray-300'"
                        class="px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest border transition-all">
                    Tasks
                </button>
            </nav>

            {{-- TAB CONTENT: TIMELINE (HYBRID OPTION 2) --}}
            <section x-show="activeTab === 'timeline'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="flex justify-between items-end mb-6 px-2">
                    <h4 class="text-lg font-black text-gray-800 dark:text-gray-100 uppercase tracking-tight">Daily Activity Log</h4>
                </div>

                <x-card-container padding="p-10 md:p-12">
                    @if($timeline->isNotEmpty())
                        <div class="relative space-y-12 before:absolute before:inset-0 before:ml-5 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-blue-500/20 before:via-gray-300/30 before:to-transparent">
                            @foreach($timeline as $event)
                                <div class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group">
                                    {{-- Dot --}}
                                    <div class="flex items-center justify-center w-10 h-10 rounded-full border border-white dark:border-gray-900 bg-white dark:bg-gray-800 shadow-md shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 z-10 transition-transform group-hover:scale-110">
                                        @if($event['type'] === 'journal')
                                            <svg class="text-blue-500" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"/><path d="M8 7h6"/><path d="M8 11h8"/></svg>
                                        @elseif($event['type'] === 'attendance')
                                            <svg class="text-orange-500" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                        @else
                                            <svg class="text-emerald-500" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                        @endif
                                    </div>
                                    {{-- Content --}}
                                    <div class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] p-6 rounded-[30px] bg-gray-50/50 dark:bg-gray-900/50 border border-gray-100/50 dark:border-gray-800/50 shadow-sm transition-all group-hover:bg-white dark:group-hover:bg-gray-800 group-hover:border-blue-100 dark:group-hover:border-blue-900/30 group-hover:shadow-md">
                                        <div class="flex items-center justify-between space-x-2 mb-1">
                                            <time class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">{{ $event['date']->format('M d, Y') }}</time>
                                            <span class="text-[9px] font-black {{ $event['type'] === 'journal' ? 'text-blue-500' : ($event['type'] === 'attendance' ? 'text-orange-500' : 'text-emerald-500') }} uppercase tracking-tighter">{{ $event['date']->format('h:i A') }}</span>
                                        </div>
                                        <div class="text-[13px] font-black text-gray-800 dark:text-gray-100 uppercase tracking-tight">{{ $event['title'] }}</div>
                                        <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-1 font-medium italic">{{ $event['description'] }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-12">
                            <x-empty-state 
                                title="No recent activity found" 
                                subtitle="Student logs for attendance, journals, and tasks will appear here."
                                compact="true" 
                            />
                        </div>
                    @endif
                </x-card-container>
            </section>

            {{-- TAB CONTENT: JOURNALS --}}
            <section x-show="activeTab === 'journals'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" x-data="{ openEntry: null }" style="display: none;">
                <div class="flex justify-between items-end mb-6 px-2">
                    <h4 class="text-lg font-black text-gray-800 dark:text-gray-100 uppercase tracking-tight">Student Journals</h4>
                    <span class="px-3 py-1 bg-gray-100 dark:bg-gray-800 rounded-full text-[10px] font-black text-gray-500 dark:text-slate-400 uppercase tracking-widest">
                        {{ $journals->count() }} Entries
                    </span>
                </div>

                <x-card-container padding="p-0" class="overflow-hidden">
                    <div class="divide-y divide-gray-50 dark:divide-gray-800/50">
                        @forelse($journals as $journal)
                            <div class="transition-all hover:bg-blue-50/20 dark:hover:bg-blue-900/5 group">
                                <div @click="openEntry = (openEntry === {{ $journal->id }} ? null : {{ $journal->id }})" 
                                     class="p-6 md:p-8 flex items-center gap-6 cursor-pointer">
                                    <div class="w-16 h-16 shrink-0 rounded-2xl bg-gray-50 dark:bg-gray-800/50 flex flex-col items-center justify-center border border-gray-100 dark:border-gray-700 transition-colors group-hover:bg-blue-50 dark:group-hover:bg-blue-900/20 group-hover:border-blue-100 dark:group-hover:border-blue-800">
                                        <span class="text-[10px] font-black text-gray-400 dark:text-slate-500 uppercase leading-none mb-1">{{ $journal->created_at->format('M') }}</span>
                                        <span class="text-xl font-black text-gray-800 dark:text-gray-100 leading-none tracking-tight">{{ $journal->created_at->format('d') }}</span>
                                    </div>
                                    <div class="flex-1 min-w-0 text-left">
                                        <h6 class="text-base font-black text-gray-800 dark:text-gray-100 uppercase tracking-tight truncate group-hover:text-[#0B5694] dark:group-hover:text-blue-400 transition-colors">
                                            {{ $journal->title }}
                                        </h6>
                                        <p class="text-xs font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest mt-1">
                                            Submitted {{ $journal->created_at->format('h:i A') }} • Week {{ $journal->created_at->weekOfMonth }}
                                        </p>
                                    </div>
                                    <div class="shrink-0 transition-transform duration-300" :class="openEntry === {{ $journal->id }} ? 'rotate-180' : ''">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="text-gray-300 dark:text-gray-700"><path d="m6 9 6 6 6-6"/></svg>
                                    </div>
                                </div>
                                <div x-show="openEntry === {{ $journal->id }}" x-collapse style="display: none;">
                                    <div class="px-8 pb-10 pt-2">
                                        <div class="p-8 bg-gray-50/50 dark:bg-gray-850/30 rounded-[30px] border border-gray-100 dark:border-gray-800 text-sm text-gray-600 dark:text-gray-300 leading-relaxed font-medium">
                                            {!! nl2br(e($journal->content)) !!}
                                        </div>
                                        <div class="mt-6 flex items-center justify-between px-4">
                                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] italic">Signed by Student</p>
                                            <div class="flex items-center gap-2 text-emerald-500">
                                                <span class="w-2 h-2 rounded-full bg-current shadow-[0_0_8px_rgba(16,185,129,0.4)]"></span>
                                                <span class="text-[10px] font-black uppercase tracking-widest">Entry Finalized</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-20 text-center">
                                <x-empty-state title="No student journals submitted yet." compact="true" />
                            </div>
                        @endforelse
                    </div>
                </x-card-container>
            </section>

            {{-- TAB CONTENT: ATTENDANCE --}}
            <section x-show="activeTab === 'attendance'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
                <div class="flex justify-between items-end mb-6 px-2">
                    <h4 class="text-lg font-black text-gray-800 dark:text-gray-100 uppercase tracking-tight">Attendance Records</h4>
                </div>

                <x-card-container padding="p-0">
                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left">
                            <thead class="bg-gray-50/50 dark:bg-gray-800/50 border-b border-gray-100 dark:border-gray-800">
                                <tr class="text-gray-400 dark:text-slate-500 uppercase text-[9px] font-black tracking-widest">
                                    <th class="py-5 px-8">Date</th>
                                    <th class="py-5 px-4 text-center">Time In/Out</th>
                                    <th class="py-5 px-4 text-center">Hours</th>
                                    <th class="py-5 px-8 text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 dark:divide-gray-850/50 text-sm font-medium">
                                @forelse($attendanceHistory->take(20) as $log)
                                    <tr class="hover:bg-blue-50/20 dark:hover:bg-blue-900/5 transition-colors group">
                                        <td class="py-5 px-8 font-black text-gray-800 dark:text-gray-200 uppercase tracking-tight">
                                            {{ isset($log['date']) ? $log['date']->format('M d, Y') : (isset($log['clock_in']) ? $log['clock_in']->format('M d, Y') : '--') }}
                                        </td>
                                        <td class="py-5 px-4 text-center">
                                            @if(in_array($log['status'], ['absent', 'excused', 'holiday', 'suspension']))
                                                <span class="text-[11px] font-black text-gray-300 dark:text-gray-700 uppercase tracking-widest">---</span>
                                            @else
                                                <div class="flex flex-col items-center">
                                                    <span class="text-[11px] font-bold text-gray-500 dark:text-slate-400">{{ isset($log['clock_in']) && $log['clock_in'] ? $log['clock_in']->format('h:i A') : '--' }}</span>
                                                    <span class="text-[9px] font-black text-gray-300 dark:text-gray-600">â€” {{ isset($log['clock_out']) && $log['clock_out'] ? $log['clock_out']->format('h:i A') : 'ACTIVE' }}</span>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="py-5 px-4 text-center font-black text-[#0B5694] dark:text-blue-400 tracking-tighter text-[13px]">
                                            @php
                                                $displayHours = 0;
                                                if ($log['status'] !== 'excused' && isset($log['clock_out']) && $log['clock_out']) {
                                                    $minutes = $log['clock_in']->diffInMinutes($log['clock_out']);
                                                    $hours = $minutes / 60;
                                                    if ($hours > 5.0) $hours = max(0, $hours - 1.0);
                                                    $displayHours = round($hours, 2);
                                                }
                                            @endphp
                                            {{ (isset($log['clock_out']) && $log['clock_out']) ? number_format($displayHours, 2) : 'PEND' }}
                                        </td>
                                        <td class="py-5 px-8 text-right">
                                            @php
                                                $status = $log['status'];
                                                if (!isset($log['clock_out']) || !$log['clock_out']) {
                                                    if (isset($log['clock_in']) && $log['clock_in']) {
                                                        $status = 'active';
                                                    }
                                                }

                                                $statusConfig = match($status) {
                                                    'late' => ['label' => 'Late', 'class' => 'bg-orange-50 dark:bg-orange-950/30 text-orange-500 border-orange-100 dark:border-orange-900/50'],
                                                    'absent' => ['label' => 'Absent', 'class' => 'bg-rose-50 dark:bg-rose-950/30 text-rose-500 border-rose-100 dark:border-rose-900/50'],
                                                    'excused' => ['label' => 'Excused', 'class' => 'bg-blue-50 dark:bg-blue-950/30 text-blue-600 border-blue-100 dark:border-blue-900/50'],
                                                    'holiday' => ['label' => 'Holiday', 'class' => 'bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 border-emerald-100 dark:border-emerald-900/50'],
                                                    'suspension' => ['label' => 'Suspended', 'class' => 'bg-purple-50 dark:bg-purple-950/30 text-purple-600 border-purple-100 dark:border-purple-900/50 animate-pulse'],
                                                    'active' => ['label' => 'Active', 'class' => 'bg-blue-50 dark:bg-blue-950/30 text-blue-500 border-blue-100 dark:border-blue-900/50 animate-pulse'],
                                                    default => ['label' => 'On-Time', 'class' => 'bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 border-emerald-100 dark:border-emerald-900/50'],
                                                };
                                            @endphp
                                            <span class="px-3 py-1 {{ $statusConfig['class'] }} text-[9px] font-black rounded-full border uppercase tracking-widest">
                                                {{ $statusConfig['label'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="py-12 text-center text-gray-400 italic">No attendance records found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-card-container>
            </section>

            {{-- TAB CONTENT: TASKS --}}
            <section x-show="activeTab === 'tasks'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
                <div class="flex justify-between items-end mb-6 px-2">
                    <h4 class="text-lg font-black text-gray-800 dark:text-gray-100 uppercase tracking-tight">Task List</h4>
                </div>

                <x-card-container padding="p-0">
                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left">
                            <thead class="bg-gray-50/50 dark:bg-gray-800/50 border-b border-gray-100 dark:border-gray-800">
                                <tr class="text-gray-400 dark:text-slate-500 uppercase text-[9px] font-black tracking-widest">
                                    <th class="py-5 px-8">Task Details</th>
                                    <th class="py-5 px-4 text-center">Due Date</th>
                                    <th class="py-5 px-8 text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 dark:divide-gray-850/50 text-sm font-medium">
                                @forelse($intern->tasks as $task)
                                    <tr class="hover:bg-blue-50/20 dark:hover:bg-blue-900/5 transition-colors">
                                        <td class="py-5 px-8">
                                            <p class="font-black text-gray-800 dark:text-gray-200 uppercase tracking-tight text-[13px]">{{ $task->title }}</p>
                                            <p class="text-[10px] text-gray-400 dark:text-slate-500 font-bold uppercase tracking-widest mt-0.5 truncate max-w-xs">{{ $task->description }}</p>
                                        </td>
                                        <td class="py-5 px-4 text-center font-bold text-gray-400 dark:text-slate-500 text-[11px] uppercase tracking-widest">
                                            {{ $task->due_date ? $task->due_date->format('M d, Y') : '---' }}
                                        </td>
                                        <td class="py-5 px-8 text-right">
                                            @php
                                                $statusClass = match($task->status) {
                                                    \App\Enums\TaskStatus::COMPLETED => 'text-emerald-600 bg-emerald-50 dark:bg-emerald-950/20 border-emerald-100 dark:border-emerald-900/50',
                                                    \App\Enums\TaskStatus::REVISION => 'text-rose-500 bg-rose-50 dark:bg-rose-950/20 border-rose-100 dark:border-rose-900/50',
                                                    default => 'text-yellow-600 bg-yellow-50 dark:bg-yellow-950/20 border-yellow-100 dark:border-yellow-900/50'
                                                };
                                            @endphp
                                            <span class="px-3 py-1 rounded-full text-[9px] font-black border uppercase tracking-widest {{ $statusClass }}">
                                                {{ $task->status->label() }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="py-12 text-center text-gray-400 italic">No tasks assigned yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-card-container>
            </section>

        </div>
    </div>
</x-page-layout>
@endsection
