@extends('layouts.app')

@section('title', 'Task Monitoring')

@section('content')
<x-page-layout 
    portalType="Supervisor" 
    userName="{{ Auth::user()->supervisorDetails->first_name }}" 
    userRole="{{ Auth::user()->supervisorDetails->office->name }}"
>
    <div class="flex flex-col h-full" x-data="taskMonitoringManager(window.location.hash)">
        <x-page-header 
            title="Task Monitoring"
            subtitle="Oversee intern progress and task compliance"
        />

        {{-- SUMMARY CARDS --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            <div class="bg-white dark:bg-gray-900 p-5 md:p-8 rounded-[30px] shadow-sm border border-gray-100 dark:border-gray-800 relative overflow-hidden">
                <p class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-6">Office Hours Rendered</p>
                <h3 class="text-2xl sm:text-3xl md:text-5xl font-black text-gray-900 dark:text-gray-100 tracking-tighter">{{ (int)$totalRendered }}</h3>
                <span class="text-xs text-[#0B5694] dark:text-blue-400 font-black bg-blue-50 dark:bg-blue-900/20 px-3 py-1 rounded-full inline-block mt-4 uppercase tracking-widest border border-blue-100 dark:border-blue-900/30">
                    {{ $completionPercentage }}% Office Progress
                </span>
            </div>

            <div class="bg-white dark:bg-gray-900 p-5 md:p-8 rounded-[30px] shadow-sm border border-gray-100 dark:border-gray-800 relative overflow-hidden">
                <p class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-6">Remaining Hours</p>
                <h3 class="text-2xl sm:text-3xl md:text-5xl font-black text-gray-900 dark:text-gray-100 tracking-tighter">{{ (int)$remainingHours }}</h3>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-5 font-black uppercase tracking-widest">Hours until department completion</p>
            </div>

            <div class="bg-white dark:bg-gray-900 p-5 md:p-8 rounded-[30px] shadow-sm border border-gray-100 dark:border-gray-800 relative overflow-hidden">
                <p class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-6">Office Status</p>
                <h3 class="text-2xl md:text-3xl font-black {{ $remainingHours == 0 ? 'text-green-500 dark:text-emerald-400' : 'text-orange-400' }} uppercase tracking-tight">
                    {{ $remainingHours == 0 ? 'Fully Completed' : 'Active Tracking' }}
                </h3>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-8 font-black uppercase tracking-widest">Monitoring {{ $interns->count() }} Interns</p>
            </div>
        </div>

        {{-- INTERN DIRECTORY HEADER --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <h3 class="font-black text-gray-800 dark:text-gray-100 text-lg md:text-xl uppercase tracking-tight">Intern Workload</h3>
            <button @click="selectedInternId = 'all'; showDrawer = true" class="bg-[#0B5694] dark:bg-blue-600 text-white px-5 py-2.5 rounded-xl font-black text-[10px] uppercase tracking-widest shadow-md hover:bg-[#084577] dark:hover:bg-blue-700 transition-all flex items-center gap-2 active:scale-95">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M12 5v14M5 12h14"/></svg> Assign Task
            </button>
        </div>

        {{-- INTERNS GRID SKELETON --}}
        <template x-if="loading">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
                @for($i = 0; $i < min(count($interns) ?: 3, 3); $i++)
                    <div class="bg-white dark:bg-gray-900 rounded-[30px] border border-gray-100 dark:border-gray-800 p-4 md:p-6 space-y-6">
                        <div class="flex items-start gap-4">
                            <x-skeleton class="w-12 h-12 rounded-full shrink-0" />
                            <div class="flex-1 space-y-2">
                                <x-skeleton class="h-4 w-3/4 rounded-lg" />
                                <x-skeleton class="h-3 w-1/2 rounded-lg" />
                            </div>
                        </div>
                        <div class="flex items-center justify-between p-4 bg-gray-50/50 dark:bg-gray-800/20 rounded-2xl">
                            <div class="space-y-2">
                                <x-skeleton class="h-3 w-16 rounded-lg" />
                                <x-skeleton class="h-4 w-24 rounded-lg" />
                            </div>
                            <x-skeleton class="w-14 h-14 rounded-full shrink-0" />
                        </div>
                        <div class="flex items-center justify-between pt-3 border-t border-gray-50 dark:border-gray-800/60">
                            <x-skeleton class="h-5 w-20 rounded-full" />
                            <div class="flex gap-2">
                                <x-skeleton class="h-8 w-16 rounded-xl" />
                                <x-skeleton class="h-8 w-10 rounded-xl" />
                            </div>
                        </div>
                    </div>
                @endfor
            </div>
        </template>

        {{-- INTERNS GRID --}}
        <div x-show="!loading" x-transition.opacity.duration.500ms class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6 mb-12">
            @forelse($interns as $intern)
                @php
                    $targetHours = $intern->required_hours ?? 480;
                    $pct = $targetHours > 0 ? min(100, max(0, ($intern->hours_rendered / $targetHours) * 100)) : 0;
                @endphp
                <div class="bg-white dark:bg-gray-900 rounded-[30px] border border-gray-100 dark:border-gray-800 p-4 md:p-6 shadow-sm hover:shadow-md hover:border-[#0B5694]/20 transition-all duration-300 flex flex-col justify-between group">
                    
                    {{-- Header: Avatar & Info --}}
                    <div class="flex items-start gap-4">
                        <x-avatar :user="$intern" size="12" border="0" textSize="sm" />
                        <div class="min-w-0 flex-1">
                            <a href="{{ route('supervisor.tasks.intern', $intern->user) }}" class="font-black text-gray-800 dark:text-gray-100 group-hover:text-[#0B5694] dark:group-hover:text-blue-400 transition-colors text-sm uppercase truncate block leading-tight">
                                {{ $intern->first_name }} {{ $intern->last_name }}
                            </a>
                            <p class="text-[10px] text-gray-400 dark:text-gray-500 font-bold uppercase tracking-wider mt-0.5">{{ $intern->student_id }}</p>
                        </div>
                    </div>
 
                    {{-- Body: Progress Ring & Stats --}}
                    <div class="flex items-center justify-between my-6 bg-gray-50/50 dark:bg-gray-800/20 p-4 rounded-2xl border border-gray-50/50 dark:border-gray-800/30">
                        <div class="text-left">
                            <p class="text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Logged Hours</p>
                            <p class="text-base font-black text-gray-800 dark:text-gray-100 tracking-tight mt-0.5">{{ number_format($intern->hours_rendered) }} / {{ $targetHours }} hrs</p>
                        </div>
 
                        {{-- Circular Progress SVG --}}
                        <div class="relative w-14 h-14 flex items-center justify-center shrink-0">
                            <svg class="w-full h-full transform -rotate-90">
                                <circle cx="28" cy="28" r="22" class="text-gray-200 dark:text-gray-800" stroke-width="4" stroke="currentColor" fill="transparent" />
                                <circle cx="28" cy="28" r="22" class="text-[#0B5694] dark:text-blue-500" stroke-width="4" stroke="currentColor" fill="transparent"
                                        stroke-dasharray="138.23"
                                        stroke-dashoffset="{{ 138.23 - (138.23 * $pct) / 100 }}" />
                            </svg>
                            <span class="absolute text-[10px] font-black text-gray-800 dark:text-gray-100 tracking-tighter">{{ round($pct) }}%</span>
                        </div>
                    </div>
 
                    {{-- Footer: Actions & Badges --}}
                    <div class="flex flex-wrap items-center justify-between gap-2 pt-3 border-t border-gray-50 dark:border-gray-800/60">
                        <span class="px-3 py-1 bg-blue-50 dark:bg-blue-900/20 text-[#0B5694] dark:text-blue-400 rounded-full text-[9px] font-black border border-blue-100 dark:border-blue-900/30 uppercase tracking-widest shrink-0">
                            {{ $intern->tasks_count }} Pending
                        </span>

                        <div class="flex gap-2">
                            <button @click="selectedInternId = '{{ $intern->user->getRouteKey() }}'; showDrawer = true" class="px-3 md:px-4 py-2 bg-gray-50 dark:bg-gray-800 hover:bg-blue-50 dark:hover:bg-blue-900/30 text-gray-500 dark:text-gray-400 hover:text-[#0B5694] dark:hover:text-blue-400 border border-gray-100 dark:border-gray-700 hover:border-blue-100 dark:hover:border-blue-900/30 rounded-xl font-black text-[9px] uppercase tracking-widest transition-all active:scale-95 flex items-center gap-1">
                                Assign
                            </button>
                            <a href="{{ route('supervisor.tasks.intern', $intern->user) }}" class="p-2 bg-gray-50 dark:bg-gray-800 hover:bg-[#0B5694] hover:text-white dark:hover:bg-blue-600 border border-gray-100 dark:border-gray-700 rounded-xl transition-all active:scale-95 text-gray-400 inline-flex items-center justify-center" title="Review workload">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    </div>

                </div>
            @empty
                <div class="col-span-full py-20 bg-white dark:bg-gray-900 rounded-[35px] border border-gray-100 dark:border-gray-800 text-center">
                    <x-empty-state title="No interns found" compact="true" />
                </div>
            @endforelse
        </div>

        {{-- SLIDE-OVER ASSIGNMENT DRAWER --}}
        <div x-show="showDrawer" 
             class="fixed inset-0 z-[150] overflow-hidden" 
             aria-labelledby="slide-over-title" 
             role="dialog" 
             aria-modal="true" 
             x-cloak>
            
            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity" 
                 x-show="showDrawer"
                 x-transition:enter="ease-in-out duration-500" 
                 x-transition:enter-start="opacity-0" 
                 x-transition:enter-end="opacity-100" 
                 x-transition:leave="ease-in-out duration-500" 
                 x-transition:leave-start="opacity-100" 
                 x-transition:leave-end="opacity-0" 
                 @click="showDrawer = false"></div>

            <div class="absolute inset-y-0 right-0 pl-10 max-w-full flex">
                <div class="w-screen sm:max-w-lg" 
                     x-show="showDrawer"
                     x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700" 
                     x-transition:enter-start="translate-x-full" 
                     x-transition:enter-end="translate-x-0" 
                     x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700" 
                     x-transition:leave-start="translate-x-0" 
                     x-transition:leave-end="translate-x-full">
                    
                    <div class="h-full flex flex-col bg-white dark:bg-gray-900 shadow-2xl border-l border-gray-100 dark:border-gray-800">
                        {{-- Drawer Header --}}
                        <div class="px-4 sm:px-6 py-4 sm:py-6 bg-gray-50 dark:bg-gray-800/40 border-b border-gray-100 dark:border-gray-800/80 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="bg-blue-50 dark:bg-blue-900/20 text-[#0B5694] dark:text-blue-400 w-10 h-10 flex items-center justify-center rounded-xl border border-blue-100 dark:border-blue-900/30">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>   
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-base font-black text-gray-800 dark:text-gray-100 uppercase tracking-tight" id="slide-over-title">New Assignment</h2>
                                    <p class="text-[10px] text-gray-400 dark:text-gray-500 font-bold uppercase tracking-widest mt-0.5">Dispatch workspace tasks</p>
                                </div>
                            </div>
                            <button @click="showDrawer = false" class="p-2 text-gray-400 hover:text-red-500 dark:hover:text-red-400 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 transition-all active:scale-95 inline-flex items-center justify-center">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            </button>
                        </div>

                        {{-- Drawer Form --}}
                        <form action="{{ route('supervisor.tasks.store') }}" method="POST" enctype="multipart/form-data" class="flex-1 flex flex-col min-h-0">
                            @csrf
                            
                            {{-- Scrollable Content --}}
                            <div class="flex-1 overflow-y-auto custom-scrollbar p-4 sm:p-6 space-y-8 text-left">
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Task Title</label>
                                    <input type="text" name="title" required placeholder="Enter task name..." class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl px-5 py-3.5 text-sm font-medium text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-[#0B5694] dark:focus:ring-blue-500 outline-none transition-all placeholder:text-gray-400 dark:placeholder:text-gray-500">
                                </div>

                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Assign To</label>
                                    <div class="relative">
                                        <select name="intern_id" x-model="selectedInternId" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl px-5 py-3.5 text-sm font-medium text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-[#0B5694] dark:focus:ring-blue-500 outline-none transition-all appearance-none bg-none">
                                            <option value="all">All Eligible Interns</option>
                                            @foreach($eligibleInterns as $eligible)
                                                <option value="{{ $eligible->user->getRouteKey() }}">{{ $eligible->first_name }} {{ $eligible->last_name }}</option>
                                            @endforeach
                                        </select>
                                        <div class="absolute inset-y-0 right-5 flex items-center pointer-events-none text-gray-400">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                                        </div>
                                    </div>
                                    <p class="text-[9px] text-[#0B5694] dark:text-blue-400 font-bold italic ml-1 leading-normal">
                                        * Interns who have received their final certificate are hidden.
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Deadline</label>
                                    <x-datepicker 
                                        name="due_date" 
                                        type="datetime-local"
                                        :required="true"
                                        placeholder="Select deadline..."
                                    />
                                    
                                    <div class="flex items-center gap-2.5 pt-3 ml-1">
                                        <input type="hidden" name="is_file_required" value="0">
                                        <input type="checkbox" name="is_file_required" id="is_file_required_drawer" value="1" checked 
                                               class="w-4.5 h-4.5 rounded border-gray-200 dark:border-gray-700 text-[#0B5694] focus:ring-[#0B5694] dark:bg-gray-800 transition-all">
                                        <label for="is_file_required_drawer" class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest cursor-pointer">Require file upload</label>
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Instructions</label>
                                    <textarea name="description" rows="5" required placeholder="Provide detail instructions here..." class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-[25px] px-6 py-4 text-sm font-medium text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-[#0B5694] dark:focus:ring-blue-500 outline-none resize-none placeholder-gray-400 dark:placeholder-gray-500 transition-all"></textarea>
                                </div>
                            </div>

                            {{-- Footer actions --}}
                            <div class="px-6 py-5 bg-gray-50 dark:bg-gray-800/40 border-t border-gray-100 dark:border-gray-800 flex flex-col sm:flex-row gap-3 sm:gap-4">
                                <button type="submit" class="flex-1 bg-[#0B5694] dark:bg-blue-600 text-white py-3.5 rounded-xl font-black text-xs uppercase tracking-widest shadow-md hover:bg-[#084577] dark:hover:bg-blue-700 transition active:scale-95">Finalize & Dispatch</button>
                                <button type="button" @click="showDrawer = false" class="px-6 bg-white dark:bg-gray-800 text-gray-400 dark:text-gray-500 py-3.5 border border-gray-200 dark:border-gray-700 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-700 transition active:scale-95">Discard Draft</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-page-layout>
@endsection
