@extends('layouts.app')

@section('title', 'Progress Tracking')

@section('content')
<x-page-layout 
    portalType="Intern" 
    userName="{{ Auth::user()->internDetails->first_name }}" 
    userRole="{{ Auth::user()->internDetails->office->name }}"
>
    <div x-data="{ 
        showJournalModal: false, 
        showViewModal: false,
        loading: true,
        isEdit: false,
        journalId: null,
        journalType: 'daily',
        entryDate: '{{ now()->format('Y-m-d') }}',
        title: '',
        notes: '',
        viewTitle: '',
        viewContent: '',
        initialTaskIds: [],
        previewLoading: false,
        allTasks: [],
        selectedTaskIds: [],
        previewAttendance: [],
        
        get includedTasks() {
            return this.allTasks.filter(t => this.selectedTaskIds.includes(t.id));
        },
        
        get availableTasks() {
            return this.allTasks.filter(t => !this.selectedTaskIds.includes(t.id));
        },

        fetchPreview() {
            if (!this.entryDate) return;
            this.previewLoading = true;
            this.allTasks = [];
            
            let url = `{{ route('intern.journal.preview') }}?type=${this.journalType}&date=${this.entryDate}`;
            if (this.isEdit && this.journalId) {
                url += `&journal_id=${this.journalId}`;
            }

            fetch(url)
                .then(res => res.json())
                .then(data => {
                    if (!this.isEdit) {
                        this.title = data.suggested_title;
                    }
                    this.allTasks = data.tasks;
                    this.selectedTaskIds = data.selected_ids;
                    this.previewAttendance = data.attendance;
                    this.previewLoading = false;
                })
                .catch(err => {
                    console.error('Error fetching preview:', err);
                    this.previewLoading = false;
                });
        },
        removeTask(taskId) {
            this.selectedTaskIds = this.selectedTaskIds.filter(id => id !== taskId);
        },
        addTask(taskId) {
            if (!this.selectedTaskIds.includes(taskId)) {
                this.selectedTaskIds.push(taskId);
            }
        }
    }" x-init="setTimeout(() => loading = false, 800); $watch('showJournalModal', value => { if (value) fetchPreview(); }); $watch('journalType', () => fetchPreview()); $watch('entryDate', () => fetchPreview())" class="flex flex-col flex-1">
        <div x-data="tabManager('tasks', true)" class="flex flex-col flex-1">
        
        {{-- Header --}}
        <x-page-header 
            title="Training Progress" 
            subtitle="Internship Journey, Task Fulfillment & Daily Journals" 
        >
            <x-slot:actions>
                {{-- TAB NAVIGATION --}}
                <nav class="flex items-center gap-1 p-1 bg-gray-50/50 dark:bg-gray-800/50 rounded-2xl w-fit border border-gray-100 dark:border-gray-800 shrink-0">
                    <button @click="setTab('tasks')" 
                            :class="activeTab === 'tasks' ? 'bg-white dark:bg-gray-700 text-[#0B5694] dark:text-blue-400 shadow-sm border-gray-100 dark:border-gray-600' : 'text-gray-400 dark:text-slate-500 border-transparent hover:text-gray-600 dark:hover:text-gray-300'" 
                            class="px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest border transition-all whitespace-nowrap active:scale-95">
                        Tasks
                    </button>
                    <button @click="setTab('journals')" 
                            :class="activeTab === 'journals' ? 'bg-white dark:bg-gray-700 text-[#0B5694] dark:text-blue-400 shadow-sm border-gray-100 dark:border-gray-600' : 'text-gray-400 dark:text-slate-500 border-transparent hover:text-gray-600 dark:hover:text-gray-300'" 
                            class="px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest border transition-all whitespace-nowrap active:scale-95">
                        Journals
                    </button>
                </nav>
            </x-slot:actions>
        </x-page-header>

        {{-- SKELETON LOADING VIEW --}}
        @php
            $maxItems = max($tasks->count(), $journals->count());
            $listSkeletonCount = $maxItems === 0 ? 1 : min($maxItems, 5);
        @endphp

        <div x-show="loading" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="flex flex-col flex-1">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 mb-10">
                <x-skeleton type="metric" class="md:col-span-6 h-40" />
                <x-skeleton type="metric" class="md:col-span-3 h-40" />
                <x-skeleton type="metric" class="md:col-span-3 h-40" />
            </div>
            <div class="h-14 w-64 bg-gray-100 dark:bg-gray-800 rounded-2xl animate-shimmer mb-10"></div>
            <x-card-container padding="p-8">
                <x-skeleton type="list" :rows="$listSkeletonCount" />
            </x-card-container>
        </div>

        {{-- ACTUAL CONTENT VIEW --}}
        <div x-show="!loading" x-cloak x-transition:enter="transition ease-out duration-500 delay-200" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="flex flex-col flex-1">
            @if($timelineNotSet)
                <div class="flex-1 flex flex-col items-center justify-center text-center py-20 bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-[35px] min-h-[400px]">
                    <div class="w-36 h-36 bg-amber-50 dark:bg-amber-900/20 rounded-full flex items-center justify-center text-amber-500 mb-10 border-4 border-amber-100 dark:border-amber-800 shadow-inner">
                        <svg xmlns="http://www.w3.org/2000/svg" width="72" height="72" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" x2="16" y1="2" x2="16" y2="6"/><line x1="8" x2="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    </div>
                    <h3 class="text-4xl font-extrabold text-gray-800 dark:text-gray-100 uppercase tracking-normal">Schedule Pending</h3>
                    <p class="text-[11px] text-gray-400 dark:text-gray-500 max-w-sm mt-6 font-bold uppercase tracking-[0.2em] italic leading-relaxed text-center px-6">
                        Your internship schedule has not started just yet. Please contact your coordinator.
                    </p>
                </div>
            @else
                {{-- STATUS ALERTS --}}
        @if(session('success'))
            <div class="mb-8 p-5 bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 rounded-[22px] font-bold uppercase text-[10px] tracking-[0.2em] border border-emerald-100 dark:border-emerald-900/50 shadow-sm flex items-center gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- BENTO GRID METRICS PANEL --}}
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 mb-10">
            {{-- Card 1: Hours Rendered & Linear Progress --}}
            <x-card-container class="md:col-span-6 flex flex-col justify-between" padding="p-6 md:p-8">
                <div class="text-left w-full">
                    <p class="text-[9px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider leading-none mb-3">Total Progress</p>
                    <div class="flex justify-between items-baseline mb-3">
                        <h3 class="text-3xl font-extrabold text-gray-850 dark:text-gray-100 tracking-normal">
                            {{ round($rendered) }} <span class="text-sm font-bold text-gray-400 dark:text-gray-500">/ {{ $totalRequired }} HRS</span>
                        </h3>
                        <span class="text-xs font-bold text-[#0B5694] dark:text-blue-400">{{ $percentage }}%</span>
                    </div>
                    
                    {{-- Linear Progress Bar --}}
                    <div x-ignore class="w-full bg-gray-100 dark:bg-gray-850 h-3.5 rounded-full overflow-hidden border border-gray-200/50 dark:border-gray-700/50 shadow-inner">
                        <div class="h-full rounded-full" 
                             style="width: {{ $percentage }}% !important; background: linear-gradient(to right, #0B5694, #60a5fa) !important;"></div>
                    </div>
                </div>
                <p class="text-[10px] text-gray-400 dark:text-gray-500 font-bold uppercase tracking-wider mt-4 text-left leading-none">
                    Fulfillment status towards graduation
                </p>
            </x-card-container>

            {{-- Card 2: Time Remaining --}}
            <x-card-container class="md:col-span-3 flex flex-col justify-between" padding="p-6 md:p-8">
                <div class="text-left">
                    <p class="text-[9px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider leading-none mb-3">Time Remaining</p>
                    <h3 class="text-4xl font-extrabold text-gray-855 dark:text-gray-100 tracking-normal mb-2">
                        {{ round($remaining) }} <span class="text-sm font-bold text-gray-400 dark:text-gray-500">HRS</span>
                    </h3>
                </div>
                <div class="text-left mt-auto">
                    <span class="px-2.5 py-1 bg-blue-50 dark:bg-blue-900/20 text-[#0B5694] dark:text-blue-400 border border-blue-100 dark:border-blue-900/30 rounded-lg text-[9px] font-bold uppercase tracking-wider">
                        In Progress
                    </span>
                </div>
            </x-card-container>

            {{-- Card 3: Journal Registry --}}
            <x-card-container class="md:col-span-3 flex flex-col justify-between" padding="p-6 md:p-8">
                <div class="text-left">
                    <p class="text-[9px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider leading-none mb-3">Journal Status</p>
                    <div class="flex items-center gap-2">
                        <div class="w-2.5 h-2.5 rounded-full {{ $hasSubmittedToday ? 'bg-emerald-500 animate-pulse' : 'bg-orange-500' }}"></div>
                        <h4 class="text-base font-extrabold text-gray-855 dark:text-gray-100 uppercase tracking-normal">
                            {{ $hasSubmittedToday ? 'Logged Today' : 'Awaiting Entry' }}
                        </h4>
                    </div>
                </div>
                <div class="text-left mt-auto">
                    @if($hasSubmittedToday)
                        <span class="px-2.5 py-1 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/30 rounded-lg text-[9px] font-bold uppercase tracking-wider">
                            Quota Met
                        </span>
                    @else
                        <button @click="isEdit = false; journalId = null; journalType = 'daily'; entryDate = '{{ now()->format('Y-m-d') }}'; title = ''; notes = ''; initialTaskIds = null; showJournalModal = true" class="text-[10px] font-bold text-[#0B5694] dark:text-blue-400 uppercase tracking-wider hover:underline hover:text-blue-700 transition active:scale-95 leading-none mt-1">
                            Write Entry &rarr;
                        </button>
                    @endif
                </div>
            </x-card-container>
        </div>


        {{-- ACCOMPLISHMENTS TAB --}}
        <div x-show="activeTab === 'tasks'" class="animate-in fade-in duration-500">
            <x-table-container title="Assigned Task Registry" subtitle="Reviewing latest workplace responsibilities">
                <div class="border border-gray-100 dark:border-gray-800 rounded-3xl overflow-hidden shadow-inner">
                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left">
                            <thead class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-100 dark:border-gray-800 text-gray-400 dark:text-gray-500 uppercase text-[10px] font-bold tracking-wider">
                                <tr>
                                    <th class="py-6 px-8">Assignment Details</th>
                                    <th class="py-6 px-8 text-center">Fulfillment Status</th>
                                    <th class="py-6 px-8 text-right">Review</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                                @forelse($tasks as $task)
                                @php
                                    $dueDate = \Carbon\Carbon::parse($task->due_date);
                                    $isOverdue = $dueDate->isPast() && $task->status->value !== 'completed';
                                    $isDueToday = $dueDate->isToday() && $task->status->value !== 'completed';
                                @endphp
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/20 transition-colors group">
                                    <td class="py-8 px-8">
                                        <div class="flex items-center gap-3">
                                            <p class="font-bold text-gray-850 dark:text-gray-100 text-sm tracking-tight group-hover:text-[#0B5694] dark:group-hover:text-blue-400 transition-colors leading-tight">{{ $task->title }}</p>
                                            @if($isOverdue)
                                                <span class="px-2.5 py-0.5 bg-rose-50 dark:bg-rose-950/30 text-rose-500 border border-rose-100 dark:border-rose-900/50 rounded-full text-[9px] font-bold uppercase tracking-wider animate-pulse shrink-0">Overdue</span>
                                            @elseif($isDueToday)
                                                <span class="px-2.5 py-0.5 bg-orange-50 dark:bg-orange-950/30 text-orange-500 border border-orange-100 dark:border-orange-900/50 rounded-full text-[9px] font-bold uppercase tracking-wider animate-pulse shrink-0">Due Today</span>
                                            @endif
                                        </div>
                                        <p class="text-[10px] font-bold text-gray-300 dark:text-gray-600 uppercase tracking-wider mt-2">Due: {{ $dueDate->format('M d, Y') }}</p>
                                    </td>
                                    <td class="py-8 px-8 text-center">
                                         <span class="px-5 py-1.5 {{ $task->status->value === 'completed' ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 border-emerald-100 dark:border-emerald-800/50' : 'bg-blue-50 dark:bg-blue-900/20 text-[#0B5694] dark:text-blue-400 border-blue-100 dark:border-blue-800/50' }} text-[10px] font-bold rounded-full border uppercase tracking-wider shadow-sm">
                                             {{ $task->status->label() }}
                                         </span>
                                    </td>
                                    <td class="py-8 px-8 text-right">
                                        <a href="{{ route('intern.tasks.show', $task) }}" class="p-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-gray-400 hover:text-[#0B5694] dark:hover:text-blue-400 hover:shadow-md transition-all inline-flex items-center justify-center transform hover:scale-110 active:scale-95 shadow-sm">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="py-24">
                                        <x-empty-state title="No workplace assignments identified" compact="true" />
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </x-table-container>
        </div>

        {{-- JOURNALS TAB --}}
        <div x-show="activeTab === 'journals'" class="space-y-10 animate-in fade-in duration-500" x-cloak>
            
            {{-- History Card --}}
            <x-card-container title="Historical Record" subtitle="Reviewing daily shift documentation" padding="p-0">
                <x-slot name="headerAction">
                    @if(!$hasSubmittedToday)
                        <button @click="isEdit = false; journalId = null; journalType = 'daily'; entryDate = '{{ now()->format('Y-m-d') }}'; title = ''; notes = ''; initialTaskIds = null; showJournalModal = true" class="bg-[#0B5694] dark:bg-blue-600 text-white px-5 py-2.5 rounded-xl font-bold text-[10px] uppercase tracking-wider shadow-md hover:bg-[#084577] dark:hover:bg-blue-700 transition-all flex items-center justify-center gap-2 active:scale-95 shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                            Log Entry
                        </button>
                    @else
                        <span class="text-[9px] font-bold text-emerald-500 uppercase tracking-wider bg-emerald-50 dark:bg-emerald-950/30 px-3 py-1.5 rounded-xl border border-emerald-100/50 dark:border-emerald-900/30 shadow-sm">Logged Today</span>
                    @endif
                </x-slot>

                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left">
                        <thead class="bg-blue-50/40 dark:bg-slate-800/40 border-b border-gray-100 dark:border-gray-800 text-gray-500 dark:text-gray-400 uppercase text-[10px] font-bold tracking-wider">
                            <tr>
                                <th class="py-6 px-10">Timestamp</th>
                                <th class="py-6 px-10">Subject / Title</th>
                                <th class="py-6 px-10 text-right">Verification</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                            @forelse($journals as $journal)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/20 transition-colors group">
                                <td class="py-8 px-10 font-bold text-gray-850 dark:text-gray-100 uppercase tracking-normal whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($journal->entry_date)->format('M d, Y') }}
                                </td>
                                <td class="py-8 px-10">
                                    <button type="button" 
                                        data-title="{{ $journal->title }}"
                                        data-content="{{ $journal->content }}"
                                        @click="
                                            viewTitle = $el.getAttribute('data-title');
                                            viewContent = $el.getAttribute('data-content');
                                            showViewModal = true;
                                        "
                                        class="font-bold text-[#0B5694] dark:text-blue-400 text-sm tracking-tight hover:underline text-left">
                                        {{ $journal->title }}
                                    </button>
                                </td>
                                <td class="py-8 px-10 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <button type="button" 
                                            data-id="{{ $journal->getRouteKey() }}"
                                            data-type="{{ $journal->journal_type }}"
                                            data-date="{{ $journal->entry_date }}"
                                            data-title="{{ $journal->title }}"
                                            data-notes="{{ $journal->notes }}"
                                            data-tasks="{{ $journal->task_ids ? json_encode($journal->task_ids) : '[]' }}"
                                            @click="
                                                isEdit = true;
                                                journalId = $el.getAttribute('data-id');
                                                journalType = $el.getAttribute('data-type');
                                                entryDate = $el.getAttribute('data-date');
                                                title = $el.getAttribute('data-title');
                                                notes = $el.getAttribute('data-notes');
                                                initialTaskIds = JSON.parse($el.getAttribute('data-tasks'));
                                                showJournalModal = true;
                                            "
                                            class="px-3.5 py-2 bg-gray-50 dark:bg-gray-800 hover:bg-[#0B5694] dark:hover:bg-blue-600 hover:text-white dark:hover:text-white border border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400 rounded-xl text-[10px] font-black uppercase tracking-wider transition-all active:scale-95 shadow-sm">
                                            Edit notes
                                        </button>
                                        <span class="text-[9px] font-bold text-gray-400 dark:text-gray-550 uppercase tracking-wider border border-gray-100/50 dark:border-gray-850 px-3 py-2 rounded-xl">
                                            {{ \Carbon\Carbon::parse($journal->created_at)->format('h:i A') }}
                                        </span>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="py-20">
                                    <x-empty-state title="Journal registry empty" compact="true" />
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card-container>
        </div>
    </div>
    </div>

    {{-- JOURNAL READ-ONLY VIEWER MODAL --}}
    <div x-show="showViewModal" 
         class="fixed inset-0 z-[150] flex items-center justify-center p-4" 
         x-cloak>
        
        <div x-show="showViewModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/60 backdrop-blur-sm" 
             @click="showViewModal = false"></div>
        
        <div x-show="showViewModal"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-white dark:bg-gray-900 rounded-[35px] w-full max-w-2xl shadow-2xl border border-gray-200 dark:border-gray-800 flex flex-col max-h-[90vh] overflow-hidden">
            
            <div class="flex justify-between items-start p-8 sm:p-10 pb-6 border-b border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900">
                <div>
                    <h3 class="text-xl font-extrabold text-gray-900 dark:text-gray-100 uppercase tracking-normal">Journal Log Entry</h3>
                    <p class="text-xs text-gray-400 dark:text-gray-500 font-bold uppercase tracking-wider mt-1" x-text="viewTitle"></p>
                </div>
                <button @click="showViewModal = false" class="text-gray-400 hover:text-gray-650 dark:hover:text-gray-300 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto custom-scrollbar p-8 sm:p-10 py-8 bg-white dark:bg-gray-950">
                <div class="bg-gray-50/50 dark:bg-gray-800/20 p-8 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-inner">
                    <div class="font-mono text-sm leading-relaxed text-gray-800 dark:text-gray-100 whitespace-pre-wrap" x-text="viewContent"></div>
                </div>
            </div>

            <div class="p-8 sm:p-10 py-6 border-t border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 flex justify-end">
                <button type="button" @click="showViewModal = false" class="sm:w-32 bg-[#0B5694] dark:bg-blue-600 text-white py-3.5 rounded-xl font-bold text-[10px] uppercase tracking-wider shadow-md hover:bg-[#084577] dark:hover:bg-blue-700 transition-all active:scale-95">Close Entry</button>
            </div>
        </div>
    </div>

            @endif

    {{-- JOURNAL ENTRY MODAL --}}
    <div x-show="showJournalModal" 
         class="fixed inset-0 z-[150] flex items-center justify-center p-4" 
         x-cloak>
        
        {{-- Backdrop: Smooth Fade Only --}}
        <div x-show="showJournalModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/60 backdrop-blur-sm" 
             @click="showJournalModal = false"></div>
        
        <div x-show="showJournalModal"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-white dark:bg-gray-900 rounded-[35px] w-full max-w-2xl shadow-2xl border border-gray-200 dark:border-gray-800 flex flex-col max-h-[90vh] overflow-hidden z-10">
            
            {{-- Fixed Header --}}
            <div class="flex justify-between items-start p-8 sm:p-10 pb-6 border-b border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900">
                <div>
                    <h3 class="text-xl font-extrabold text-gray-900 dark:text-gray-100 uppercase tracking-normal" x-text="isEdit ? 'Edit Journal Entry' : 'Submit Journal Entry'">Submit Journal Entry</h3>
                    <p class="text-xs text-gray-400 dark:text-gray-500 font-bold uppercase tracking-wider mt-1" x-text="isEdit ? 'Update your journal title and notes' : 'Log automatic activities and add personal notes'">Log automatic activities and add personal notes</p>
                </div>
                <button @click="showJournalModal = false" class="text-gray-400 hover:text-gray-650 dark:hover:text-gray-300 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            <form :action="isEdit ? '/intern/progress/journal/' + journalId : '{{ route('intern.journal.store') }}'" method="POST" class="flex flex-col flex-1 overflow-hidden">
                @csrf
                <template x-if="isEdit">
                    <input type="hidden" name="_method" value="PUT">
                </template>
                <input type="hidden" name="journal_type" :value="journalType">
                <input type="hidden" name="entry_date" :value="entryDate">
                <input type="hidden" name="included_tasks" :value="JSON.stringify(selectedTaskIds)">

                {{-- Scrollable Content --}}
                <div class="flex-1 overflow-y-auto custom-scrollbar p-8 sm:p-10 py-6 space-y-6">
                    {{-- Type Selection Toggle --}}
                    <div class="space-y-2">
                        <label class="text-[9px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider ml-1">Journal Type</label>
                        <nav class="flex items-center gap-1 p-1 bg-gray-50/50 dark:bg-gray-800/50 rounded-2xl w-full border border-gray-100 dark:border-gray-800 shrink-0" :class="isEdit ? 'opacity-60 pointer-events-none' : ''">
                            <button type="button" @click="if(!isEdit) journalType = 'daily'"
                                :class="journalType === 'daily' ? 'bg-white dark:bg-gray-700 text-[#0B5694] dark:text-blue-400 shadow-sm border-gray-100 dark:border-gray-600' : 'text-gray-400 dark:text-slate-500 border-transparent hover:text-gray-600 dark:hover:text-gray-300'"
                                class="flex-1 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest border transition-all whitespace-nowrap active:scale-95">
                                Daily Log
                            </button>
                            <button type="button" @click="if(!isEdit) journalType = 'weekly'"
                                :class="journalType === 'weekly' ? 'bg-white dark:bg-gray-700 text-[#0B5694] dark:text-blue-400 shadow-sm border-gray-100 dark:border-gray-600' : 'text-gray-400 dark:text-slate-500 border-transparent hover:text-gray-600 dark:hover:text-gray-300'"
                                class="flex-1 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest border transition-all whitespace-nowrap active:scale-95">
                                Weekly Log
                            </button>
                        </nav>
                    </div>

                    {{-- Date Input --}}
                    <div class="space-y-2">
                        <label class="text-[9px] font-bold text-gray-400 dark:text-gray-550 uppercase tracking-wider ml-1"
                               x-text="journalType === 'daily' ? 'Select Date' : 'Select Date in Week'"></label>
                        <x-datepicker 
                            name="entry_date" 
                            x-model="entryDate" 
                            placeholder="Select Date"
                            :required="true"
                            disabled="isEdit"
                        />
                    </div>

                    {{-- Entry Heading --}}
                    <div class="space-y-2">
                        <label class="text-[9px] font-bold text-gray-400 dark:text-gray-550 uppercase tracking-wider ml-1">Entry Heading</label>
                        <input type="text" name="title" x-model="title" required
                            placeholder="Enter a descriptive title..."
                            class="w-full bg-gray-50 dark:bg-gray-800 border-none rounded-2xl px-5 py-4 text-sm font-semibold text-gray-850 dark:text-gray-100 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/30 shadow-inner">
                    </div>

                    {{-- Automated Log Preview --}}
                    <div class="space-y-2 bg-gray-50 dark:bg-gray-800 p-5 rounded-3xl border border-gray-250 dark:border-gray-700">
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-[9px] font-bold text-[#0B5694] dark:text-blue-400 uppercase tracking-wider">Automated Summary Preview</span>
                            <div x-show="previewLoading" class="w-3.5 h-3.5 border-2 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
                        </div>

                        <div x-show="!previewLoading" class="space-y-4 text-xs">
                            {{-- Tasks list --}}
                            <div>
                                <p class="font-bold text-[9px] text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Tasks Included in Summary:</p>
                                <template x-if="includedTasks.length === 0">
                                    <p class="text-gray-650 dark:text-gray-300 font-medium italic">No tasks included in summary.</p>
                                </template>
                                <div class="space-y-2">
                                    <template x-for="task in includedTasks" :key="task.id">
                                        <div class="flex justify-between items-center py-1 border-b border-gray-200 dark:border-gray-700 group">
                                            <div class="flex items-center gap-2">
                                                <button type="button" @click="removeTask(task.id)" class="p-1 text-gray-400 hover:text-red-500 transition-all active:scale-90" title="Remove from summary">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                                </button>
                                                <span class="text-gray-700 dark:text-gray-300 font-medium" x-text="task.title"></span>
                                            </div>
                                            <span class="text-[9px] font-bold px-2.5 py-0.5 rounded-full bg-blue-50 dark:bg-blue-950 text-[#0B5694] dark:text-blue-400 border border-blue-100 dark:border-blue-900" x-text="task.status"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            {{-- Available to add --}}
                            <template x-if="availableTasks.length > 0">
                                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                    <p class="font-bold text-[9px] text-gray-400 dark:text-gray-550 uppercase tracking-wider mb-2">Available to add back:</p>
                                    <div class="flex flex-wrap gap-2">
                                        <template x-for="task in availableTasks" :key="task.id">
                                            <button type="button" @click="addTask(task.id)" 
                                                class="flex items-center gap-1.5 px-3 py-1.5 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-full text-[10px] font-bold text-gray-600 dark:text-gray-300 hover:border-[#0B5694] dark:hover:border-blue-500 hover:text-[#0B5694] dark:hover:text-blue-400 transition-all active:scale-95 shadow-sm">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                                                <span x-text="task.title"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            {{-- Attendance list --}}
                            <div>
                                <p class="font-bold text-[9px] text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Attendance Remarks:</p>
                                <template x-if="previewAttendance.length === 0">
                                    <p class="text-gray-650 dark:text-gray-300 font-medium">No attendance records for this period.</p>
                                </template>
                                <div class="space-y-2 max-h-40 overflow-y-auto custom-scrollbar">
                                    <template x-for="att in previewAttendance" :key="att.date">
                                        <div class="py-2 border-b border-gray-200 dark:border-gray-700">
                                            <div class="flex justify-between items-center mb-0.5">
                                                <span class="text-gray-800 dark:text-gray-200 font-bold" x-text="att.date"></span>
                                                <span class="text-[9px] font-bold px-2.5 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900" x-text="att.status"></span>
                                            </div>
                                            <div class="text-[10px] text-gray-600 dark:text-gray-450">
                                                Time: <span x-text="att.clock_in + ' - ' + att.clock_out"></span>
                                                <template x-if="att.remarks">
                                                    <span> | Remarks: <span class="font-semibold text-gray-800 dark:text-gray-200" x-text="att.remarks"></span></span>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <div x-show="previewLoading" class="py-8 text-center text-xs text-gray-600 dark:text-gray-400 font-medium">
                            Gathering activities for this period...
                        </div>
                    </div>

                    {{-- Personal Notes --}}
                    <div class="space-y-2">
                        <label class="text-[9px] font-bold text-gray-400 dark:text-gray-550 uppercase tracking-wider ml-1">Personal Log & Notes (Optional)</label>
                        <textarea name="notes" x-model="notes" rows="4"
                            placeholder="Provide any additional notes, challenges faced, or details you want to log..."
                            class="w-full bg-gray-50 dark:bg-gray-800 border-none rounded-2xl px-6 py-5 text-sm font-medium text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/30 shadow-inner resize-none"></textarea>
                    </div>
                </div>

                {{-- Fixed Footer --}}
                <div class="flex flex-col sm:flex-row justify-end gap-3 p-8 sm:p-10 py-6 border-t border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900">
                    <button type="button" @click="showJournalModal = false" class="sm:w-32 bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400 py-3.5 rounded-xl font-bold text-[10px] uppercase tracking-wider hover:bg-gray-100 dark:hover:bg-gray-700 transition active:scale-95">Cancel</button>
                    <button type="submit" class="sm:w-48 bg-[#0B5694] dark:bg-blue-600 text-white py-3.5 rounded-xl font-bold text-[10px] uppercase tracking-wider hover:bg-[#084577] dark:hover:bg-blue-700 transition shadow-md active:scale-95">Finalize Log</button>
                </div>
            </form>
        </div>
    </div>
    </div>
</x-page-layout>
@endsection
