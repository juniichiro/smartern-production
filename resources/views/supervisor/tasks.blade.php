@extends('layouts.app')

@section('title', 'Tasks - ' . $intern->first_name)

@section('content')
<x-page-layout 
    portalType="Supervisor" 
    userName="{{ Auth::user()->supervisorDetails->first_name }}" 
    userRole="{{ Auth::user()->supervisorDetails->office->name }}"
>
    <div x-data="supervisorTaskManager" class="flex flex-col flex-1">
        <div class="flex-1 flex flex-col" x-data="supervisorInternTasks">
            
            {{-- Profile Card --}}
            <x-card-container class="mb-10" padding="p-6 md:p-10">
                <div class="flex flex-col md:flex-row gap-8 items-center md:items-start text-center md:text-left">
                    <x-avatar :user="$intern" size="32" circular="false" border="4" textSize="4xl" />
                    
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-4 mb-2 justify-center md:justify-start">
                            <a href="{{ route('supervisor.tasks') }}" class="text-[#0B5694] dark:text-blue-400 hover:underline font-bold text-xs uppercase tracking-widest flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                                Back to Monitoring
                            </a>
                        </div>
                        <h2 class="text-2xl md:text-4xl font-black text-gray-800 dark:text-gray-100 tracking-tight uppercase leading-tight">
                            Workload: <span class="text-[#0B5694] dark:text-blue-400">{{ $intern->first_name }} {{ $intern->last_name }}</span>
                        </h2>
                        <p class="text-sm md:text-base text-gray-400 dark:text-gray-500 mt-2 font-medium italic tracking-tight">Reviewing assigned tasks for {{ $intern->office->name }} • {{ $intern->student_id }}</p>
                        
                        <div class="flex gap-2 mt-4 justify-center md:justify-start">
                            @if($intern->certificate && $intern->certificate->status === 'published')
                                <span class="px-4 py-1.5 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 text-[10px] font-black rounded-full border border-emerald-100 dark:border-emerald-900/30 uppercase tracking-widest">Completed</span>
                            @else
                                <span class="px-4 py-1.5 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 text-[10px] font-black rounded-full border border-blue-100 dark:border-blue-900/30 uppercase tracking-widest">Active</span>
                            @endif
                        </div>
                    </div>
                </div>
            </x-card-container>

            {{-- SEARCH BAR --}}
            <div class="flex flex-col md:flex-row gap-4 mb-8 items-stretch md:items-center">
                <div class="relative flex-1 group">
                    <span class="absolute inset-y-0 left-5 flex items-center text-gray-400 dark:text-gray-500">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    </span>
                    <input type="text" x-model="searchQuery" @input.debounce.250ms="searchQuery = $event.target.value" placeholder="Search by task title..." class="w-full bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-[22px] pl-14 pr-6 py-4 text-sm font-bold text-gray-700 dark:text-gray-100 shadow-sm focus:ring-4 focus:ring-blue-100 dark:focus:ring-blue-900/30 focus:border-[#0B5694] dark:focus:border-blue-600 transition-all placeholder-gray-400 dark:placeholder-gray-500">
                </div>
            </div>

            @php
                $inProgressTasks = $tasks->filter(fn($t) => $t->status->value === 'pending' && $t->submissions->count() === 0);
                $needsReviewTasks = $tasks->filter(fn($t) => $t->status->value === 'revision' || ($t->status->value === 'pending' && $t->submissions->count() > 0));
                $completedTasks = $tasks->filter(fn($t) => $t->status->value === 'completed');
            @endphp

            {{-- KANBAN TASK BOARD --}}
            <x-table-container>
                <x-slot name="headerAction">
                    <div class="flex flex-col sm:flex-row items-center gap-6 w-full lg:w-auto">
                        <h3 class="text-lg md:text-xl font-black text-gray-800 dark:text-gray-100 tracking-tight uppercase">Assigned Tasks ({{ $tasks->count() }})</h3>
                        @if(!$intern->certificate || $intern->certificate->status !== 'published')
                            @can('create', App\Models\Task::class)
                                <button @click="showAssignModal = true" class="w-full sm:w-auto bg-[#0B5694] dark:bg-blue-600 text-white px-5 py-2.5 rounded-xl font-bold text-[10px] uppercase tracking-wider shadow-md hover:bg-[#084577] dark:hover:bg-blue-700 transition-all flex items-center justify-center gap-2 active:scale-95">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M12 5v14M5 12h14"/></svg> Post New Task
                                </button>
                            @endcan
                        @endif
                    </div>
                </x-slot>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Column: In Progress -->
                    <div class="flex flex-col space-y-6 bg-gray-50/40 dark:bg-gray-800/10 p-5 rounded-[30px] border border-gray-100/50 dark:border-gray-800/50 min-h-[500px]">
                        <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800">
                            <div class="flex items-center gap-2.5">
                                <span class="w-3 h-3 rounded-full bg-orange-400 dark:bg-orange-500 shadow-sm shadow-orange-400/50"></span>
                                <h4 class="text-[11px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-400">In Progress</h4>
                            </div>
                            <span class="px-2.5 py-0.5 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 text-gray-500 dark:text-gray-400 text-[10px] font-black rounded-full shadow-sm">{{ $inProgressTasks->count() }}</span>
                        </div>
                        
                        <div class="flex flex-col gap-4 max-h-[420px] overflow-y-auto custom-scrollbar pr-1">
                            {{-- Skeleton --}}
                            <template x-if="loading">
                                <div class="space-y-4">
                                    @for($i = 0; $i < min(count($inProgressTasks) ?: 2, 2); $i++)
                                        <x-skeleton class="h-32 w-full rounded-[25px]" />
                                    @endfor
                                </div>
                            </template>

                            <div x-show="!loading" x-transition.opacity.duration.500ms class="space-y-4">
                                @forelse($inProgressTasks as $task)
                                    @include('supervisor.components.task-kanban-card', ['task' => $task])
                                @empty
                                    <div class="flex flex-col items-center justify-center py-20 px-6 text-center flex-1">
                                        <div class="w-12 h-12 rounded-2xl bg-orange-50/50 dark:bg-orange-950/10 flex items-center justify-center mb-3 text-orange-400 dark:text-orange-500">
                                            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        </div>
                                        <p class="text-[10px] font-black uppercase tracking-widest text-gray-300 dark:text-gray-600">No active work</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Column: Needs Review -->
                    <div class="flex flex-col space-y-6 bg-gray-50/40 dark:bg-gray-800/10 p-5 rounded-[30px] border border-gray-100/50 dark:border-gray-800/50 min-h-[500px]">
                        <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800">
                            <div class="flex items-center gap-2.5">
                                <span class="w-3 h-3 rounded-full bg-[#0B5694] dark:bg-blue-500 shadow-sm shadow-blue-500/50 animate-pulse"></span>
                                <h4 class="text-[11px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-400">Needs Review</h4>
                            </div>
                            <span class="px-2.5 py-0.5 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 text-gray-500 dark:text-gray-400 text-[10px] font-black rounded-full shadow-sm">{{ $needsReviewTasks->count() }}</span>
                        </div>
                        
                        <div class="flex flex-col gap-4 max-h-[420px] overflow-y-auto custom-scrollbar pr-1">
                            {{-- Skeleton --}}
                            <template x-if="loading">
                                <div class="space-y-4">
                                    @for($i = 0; $i < min(count($needsReviewTasks) ?: 2, 2); $i++)
                                        <x-skeleton class="h-32 w-full rounded-[25px]" />
                                    @endfor
                                </div>
                            </template>

                            <div x-show="!loading" x-transition.opacity.duration.500ms class="space-y-4">
                                @forelse($needsReviewTasks as $task)
                                    @include('supervisor.components.task-kanban-card', ['task' => $task])
                                @empty
                                    <div class="flex flex-col items-center justify-center py-20 px-6 text-center flex-1">
                                        <div class="w-12 h-12 rounded-2xl bg-blue-50/50 dark:bg-blue-950/10 flex items-center justify-center mb-3 text-blue-400 dark:text-blue-500">
                                            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </div>
                                        <p class="text-[10px] font-black uppercase tracking-widest text-gray-300 dark:text-gray-600">All caught up</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Column: Completed -->
                    <div class="flex flex-col space-y-6 bg-gray-50/40 dark:bg-gray-800/10 p-5 rounded-[30px] border border-gray-100/50 dark:border-gray-800/50 min-h-[500px]">
                        <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800">
                            <div class="flex items-center gap-2.5">
                                <span class="w-3 h-3 rounded-full bg-emerald-500 dark:bg-emerald-600 shadow-sm shadow-emerald-500/50"></span>
                                <h4 class="text-[11px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-400">Completed</h4>
                            </div>
                            <span class="px-2.5 py-0.5 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 text-gray-500 dark:text-gray-400 text-[10px] font-black rounded-full shadow-sm">{{ $completedTasks->count() }}</span>
                        </div>
                        
                        <div class="flex flex-col gap-4 max-h-[420px] overflow-y-auto custom-scrollbar pr-1">
                            {{-- Skeleton --}}
                            <template x-if="loading">
                                <div class="space-y-4">
                                    @for($i = 0; $i < min(count($completedTasks) ?: 2, 2); $i++)
                                        <x-skeleton class="h-32 w-full rounded-[25px]" />
                                    @endfor
                                </div>
                            </template>

                            <div x-show="!loading" x-transition.opacity.duration.500ms class="space-y-4">
                                @forelse($completedTasks as $task)
                                    @include('supervisor.components.task-kanban-card', ['task' => $task])
                                @empty
                                    <div class="flex flex-col items-center justify-center py-20 px-6 text-center flex-1">
                                        <div class="w-12 h-12 rounded-2xl bg-emerald-50/50 dark:bg-emerald-950/10 flex items-center justify-center mb-3 text-emerald-400 dark:text-emerald-500">
                                            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        </div>
                                        <p class="text-[10px] font-black uppercase tracking-widest text-gray-300 dark:text-gray-600">No verified tasks</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </x-table-container>
        </div>

        {{-- MODALS --}}
        <div x-show="showAssignModal || showEditModal || showDeleteModal" 
             class="fixed inset-0 z-[110] flex items-center justify-center p-4 sm:p-6" 
             x-cloak>
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="showAssignModal = false; showEditModal = false; showDeleteModal = false"></div>
        
        {{-- Quick Assign Modal --}}
        <div x-show="showAssignModal" class="relative bg-white dark:bg-gray-900 rounded-[35px] w-full max-w-xl p-8 sm:p-10 shadow-2xl overflow-y-auto max-h-[90vh] border border-gray-100 dark:border-gray-800 custom-scrollbar"
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            <h3 class="text-2xl font-black text-gray-800 dark:text-gray-100 uppercase tracking-tight mb-8">Quick Assign</h3>
            <form action="{{ route('supervisor.tasks.store') }}" method="POST" class="space-y-8 text-left">
                @csrf
                <input type="hidden" name="intern_id" value="{{ $intern->user_id }}">
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Task Title</label>
                    <input type="text" name="title" required placeholder="Enter task name..." class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl px-6 py-4 text-sm font-medium text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-[#0B5694] dark:focus:ring-blue-500 outline-none transition-all">
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Instructions</label>
                    <textarea name="description" rows="4" required placeholder="Details here..." class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl px-6 py-4 text-sm font-medium text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-[#0B5694] dark:focus:ring-blue-500 outline-none resize-none transition-all"></textarea>
                </div>
                <div class="space-y-2">
                    <x-datepicker 
                       name="due_date" 
                       label="Deadline"
                       placeholder="Select deadline..."
                       type="datetime-local"
                       :required="true"
                    />
                </div>
                <div class="flex items-center gap-3 mt-4 ml-1">
                    <input type="hidden" name="is_file_required" value="0">
                    <input type="checkbox" name="is_file_required" id="is_file_required_assign" value="1" checked 
                           class="w-5 h-5 rounded-lg border-gray-200 dark:border-gray-700 text-[#0B5694] focus:ring-[#0B5694] dark:bg-gray-800 transition-all">
                    <label for="is_file_required_assign" class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest cursor-pointer">Require file upload</label>
                </div>
                <div class="flex flex-col sm:flex-row gap-4 pt-4">
                    <button type="submit" class="flex-1 bg-[#0B5694] dark:bg-blue-600 text-white py-4 rounded-2xl font-bold text-xs uppercase tracking-wider shadow-lg hover:bg-[#084577] transition active:scale-95">Dispatch Task</button>
                    <button type="button" @click="showAssignModal = false" class="sm:w-32 bg-gray-50 dark:bg-gray-800 text-gray-400 dark:text-gray-500 py-4 rounded-2xl font-bold text-xs uppercase tracking-wider hover:bg-gray-100 dark:hover:bg-gray-700 transition">Cancel</button>
                </div>
            </form>
        </div>

        {{-- Edit Task Modal --}}
        <div x-show="showEditModal" class="relative bg-white dark:bg-gray-900 rounded-[35px] w-full max-w-xl p-8 sm:p-10 shadow-2xl overflow-y-auto max-h-[90vh] border border-gray-100 dark:border-gray-800 custom-scrollbar"
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            <h3 class="text-2xl font-black text-gray-800 dark:text-gray-100 uppercase tracking-tight mb-8">Edit Task</h3>
            <form :action="`/supervisor/tasks/update/${editData.id}`" method="POST" class="space-y-8 text-left">
                @csrf
                @method('PUT')
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Task Title</label>
                    <input type="text" name="title" x-model="editData.title" required class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl px-6 py-4 text-sm font-medium text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-[#0B5694] dark:focus:ring-blue-500 outline-none transition-all">
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Instructions</label>
                    <textarea name="description" rows="4" x-model="editData.description" required class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl px-6 py-4 text-sm font-medium text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-[#0B5694] dark:focus:ring-blue-500 outline-none resize-none transition-all"></textarea>
                </div>
                <div class="space-y-2">
                    <x-datepicker 
                       name="due_date" 
                       label="Deadline"
                       type="datetime-local"
                       x-model="editData.due_date"
                       :required="true"
                    />
                </div>
                <div class="flex items-center gap-3 mt-4 ml-1">
                    <input type="hidden" name="is_file_required" value="0">
                    <input type="checkbox" name="is_file_required" id="is_file_required_edit" value="1" x-model="editData.is_file_required" 
                           class="w-5 h-5 rounded-lg border-gray-200 dark:border-gray-700 text-[#0B5694] focus:ring-[#0B5694] dark:bg-gray-800 transition-all">
                    <label for="is_file_required_edit" class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest cursor-pointer">Require file upload</label>
                </div>
                <div class="flex flex-col sm:flex-row gap-4 pt-4">
                    <button type="submit" class="flex-1 bg-[#0B5694] dark:bg-blue-600 text-white py-4 rounded-2xl font-bold text-xs uppercase tracking-wider shadow-lg hover:bg-[#084577] transition active:scale-95">Save Changes</button>
                    <button type="button" @click="showEditModal = false" class="sm:w-32 bg-gray-50 dark:bg-gray-800 text-gray-400 dark:text-gray-500 py-4 rounded-2xl font-bold text-xs uppercase tracking-wider hover:bg-gray-100 dark:hover:bg-gray-700 transition">Cancel</button>
                </div>
            </form>
        </div>

        {{-- Delete Task Modal --}}
        <div x-show="showDeleteModal" class="relative bg-white dark:bg-gray-900 rounded-[35px] w-full max-w-md p-10 shadow-2xl border border-gray-100 dark:border-gray-800"
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            <div class="text-center">
                <div class="w-20 h-20 bg-red-50 dark:bg-red-900/20 text-red-500 rounded-[30px] flex items-center justify-center mx-auto mb-6 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                </div>
                <h3 class="text-2xl font-black text-gray-800 dark:text-gray-100 uppercase tracking-tight mb-2">Withdraw Task?</h3>
                <p class="text-xs text-gray-400 dark:text-gray-500 font-medium italic mb-8">This action is irreversible. The intern will no longer see this task.</p>
                
                <div class="flex flex-col gap-4">
                    <form :action="`/supervisor/tasks/delete/${taskToDelete}`" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white py-4 rounded-2xl font-bold text-xs uppercase tracking-wider shadow-lg shadow-red-500/20 transition active:scale-95">Yes, Withdraw Task</button>
                    </form>
                    <button type="button" @click="showDeleteModal = false" class="w-full bg-gray-50 dark:bg-gray-800 text-gray-400 dark:text-gray-500 py-4 rounded-2xl font-bold text-xs uppercase tracking-wider hover:bg-gray-100 dark:hover:bg-gray-700 transition">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</div>
</x-page-layout>
@endsection
