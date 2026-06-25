@extends('layouts.app')

@section('title', 'Task - ' . $task->title)

@section('content')

<div class="flex min-h-screen bg-gray-50 dark:bg-gray-950 font-sans text-[14px]" x-data="{ showPreview: false, fileUrl: '', fileType: '' }">
    {{-- SIDEBAR --}}
    <x-sidebar portalType="Intern" userName="{{ Auth::user()->internDetails->first_name }}" userRole="{{ Auth::user()->internDetails->office->name }}"/>

    {{-- MAIN CONTENT --}}
    <main class="flex-1 ml-0 lg:ml-64 mr-0 lg:mr-80 p-4 md:p-6 lg:p-10 pt-20 lg:pt-10 text-left">
        {{-- Navigation & Status Bar --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-10">
            <a href="{{ route('intern.progress') }}" class="group flex items-center gap-2 text-gray-500 dark:text-gray-400 hover:text-[#0B5694] dark:hover:text-blue-400 font-bold uppercase text-xs tracking-wider transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="group-hover:-translate-x-1 transition-transform"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                Back to Progress
            </a>
            
            @php
                $badgeStyle = match($task->status->value) {
                    'pending'   => 'bg-blue-50 dark:bg-blue-900/20 text-[#0B5694] dark:text-blue-400 border-blue-100 dark:border-blue-800/50',
                    'revision'  => 'bg-yellow-50 dark:bg-yellow-900/20 text-yellow-600 dark:text-yellow-400 border-yellow-100 dark:border-yellow-800/50',
                    'completed' => 'bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 border-green-100 dark:border-green-800/50',
                    default     => 'bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400 border-gray-100 dark:border-gray-800'
                };
            @endphp
            <div class="flex items-center gap-4">
                <span class="text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Current Status</span>
                <span class="px-8 py-2 {{ $badgeStyle }} text-xs font-bold rounded-full border uppercase tracking-[0.2em] shadow-sm">
                    {{ $task->status->label() }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Left Column: Task Details & Submission --}}
            <div class="col-span-1 lg:col-span-2 space-y-8">
                {{-- Task Description --}}
                <div class="bg-white dark:bg-gray-900 rounded-[35px] shadow-sm border border-gray-200 dark:border-gray-800 p-6 lg:p-12">
                    <div class="flex justify-between items-start mb-4">
                        <p class="text-xs font-bold text-[#0B5694] dark:text-blue-400 uppercase tracking-[0.3em]">Task Description</p>
                        <p class="text-[10px] font-bold text-gray-400 dark:text-gray-600 uppercase italic">ID: #TSK-{{ str_pad($task->id, 4, '0', STR_PAD_LEFT) }}</p>
                    </div>
                    <h2 class="text-2xl md:text-4xl font-extrabold text-gray-800 dark:text-gray-100 tracking-normal mb-8">{{ $task->title }}</h2>
                    
                    <div class="prose prose-blue dark:prose-invert max-w-none text-gray-600 dark:text-gray-300 font-medium leading-relaxed">
                        <p class="text-base whitespace-pre-line">{{ $task->description ?? 'No detailed description provided for this task.' }}</p>
                    </div>
                </div>

                {{-- Active Submission Form --}}
                <div class="bg-white dark:bg-gray-900 rounded-[35px] shadow-sm border border-gray-200 dark:border-gray-800 p-6 lg:p-12">
                    <h3 class="text-xl font-extrabold text-gray-800 dark:text-gray-100 tracking-normal mb-8 uppercase">Submit Work</h3>
                    
                    @php
                        $hasSubmission = $task->submissions->isNotEmpty();
                    @endphp

                    @if($task->status->value === 'completed')
                        <div class="bg-green-50 dark:bg-green-900/20 border border-green-100 dark:border-green-800/50 rounded-[25px] p-8 text-center">
                            <div class="w-12 h-12 bg-green-500 text-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg shadow-green-500/20">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            </div>
                            <p class="font-bold text-green-700 dark:text-green-400 uppercase tracking-wider text-sm">Task Verified</p>
                            <p class="text-green-600/70 dark:text-green-500/70 text-xs mt-1 font-bold italic">Excellent work! This task has been closed by your supervisor.</p>
                            @if($task->remarks)
                                <div class="mt-6 pt-6 border-t border-green-100 dark:border-green-800/50 text-left">
                                    <p class="text-[10px] font-bold text-green-600 dark:text-green-500 uppercase tracking-wider mb-2">Final Remarks</p>
                                    <p class="text-sm text-green-700 dark:text-green-400 font-medium leading-relaxed">{{ $task->remarks }}</p>
                                </div>
                            @endif
                        </div>
                    @elseif($task->status->value === 'pending' && $hasSubmission)
                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800/50 rounded-[25px] p-8 text-center mb-6">
                            <p class="font-bold text-[#0B5694] dark:text-blue-400 uppercase tracking-wider text-sm ">Pending Supervisor Review</p>
                        </div>
                        <div class="opacity-40 grayscale pointer-events-none">
                            <div class="bg-gray-100 dark:bg-gray-800 rounded-[25px] p-6 lg:p-10 text-center border-2 border-dashed border-gray-200 dark:border-gray-700">
                                <p class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Submission Locked</p>
                            </div>
                        </div>
                    @else
                        @if($task->status->value === 'revision')
                            <div class="mb-6 p-8 bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800/50 rounded-[25px] space-y-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 bg-amber-500 text-white rounded-full flex items-center justify-center shrink-0 shadow-lg shadow-amber-500/20">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </div>
                                    <p class="text-sm font-bold text-amber-700 dark:text-amber-400 uppercase tracking-wider">Revision Needed</p>
                                </div>
                                @if($task->remarks)
                                    <div class="bg-white/50 dark:bg-gray-900/50 p-6 rounded-2xl border border-amber-200/50 dark:border-amber-900/30">
                                        <p class="text-[10px] font-bold text-amber-600 dark:text-amber-500 uppercase tracking-wider mb-2">Supervisor Feedback</p>
                                        <p class="text-sm text-gray-700 dark:text-gray-300 font-medium leading-relaxed">{{ $task->remarks }}</p>
                                    </div>
                                @endif
                            </div>
                        @endif

                        <form action="{{ route('intern.tasks.submit', $task) }}" method="POST" enctype="multipart/form-data" class="space-y-6" x-data="internTaskSubmission" @submit="submitForm">
                            @csrf
                            <div :class="submitting ? 'opacity-50 pointer-events-none' : ''" class="transition-all duration-300">
                                <textarea name="content" {{ !$task->is_file_required ? 'required' : '' }} placeholder="{{ $task->is_file_required ? 'Include any notes for your supervisor here...' : 'Provide a summary of your work here (Required since no file is being uploaded)...' }}" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl px-6 py-4 text-sm font-medium text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-[#0B5694] dark:focus:ring-blue-500 transition-all placeholder:text-gray-400 dark:placeholder:text-gray-600 outline-none resize-none" rows="4"></textarea>
                                
                                <div class="border-2 border-dashed border-gray-200 dark:border-gray-800 rounded-[25px] p-10 mt-6 text-center hover:border-blue-200 dark:hover:border-blue-900 transition-all cursor-pointer group relative bg-gray-50/30 dark:bg-gray-800/30">
                                    <input type="file" name="submission_file" {{ $task->is_file_required ? 'required' : '' }} class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" 
                                        @change="fileName = $event.target.files[0].name">
                                    <div x-show="!fileName">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#0B5694" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-4 opacity-40 group-hover:opacity-100 transition-opacity"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                                        <p class="text-sm font-bold text-gray-500 dark:text-gray-400 group-hover:text-[#0B5694] dark:group-hover:text-blue-400 transition-colors uppercase tracking-wider">
                                            {{ $task->is_file_required ? 'Select or drag files here' : 'Select or drag files here (Optional)' }}
                                        </p>
                                        <p class="text-[10px] text-gray-400 dark:text-gray-600 mt-1 uppercase font-bold italic">PDF, ZIP, or Images up to 10MB</p>
                                    </div>
                                    <div x-show="fileName" x-cloak class="flex flex-col items-center justify-center gap-2">
                                        <div class="w-12 h-12 bg-green-50 dark:bg-green-900/20 text-green-500 rounded-full flex items-center justify-center mb-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                        </div>
                                        <p class="text-sm font-bold text-green-600 dark:text-green-400 uppercase tracking-wider" x-text="fileName"></p>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" 
                                    class="w-full bg-[#0B5694] text-white py-6 rounded-3xl font-bold text-sm uppercase tracking-wider shadow-2xl dark:shadow-none hover:bg-[#084577] transition-all transform active:scale-[0.98] flex items-center justify-center gap-3 disabled:bg-gray-400 disabled:cursor-not-allowed"
                                    :disabled="submitting">
                                <template x-if="!submitting">
                                    <span>{{ $task->status->value === 'revision' ? 'Resubmit Revision' : 'Submit Task' }}</span>
                                </template>
                                <template x-if="submitting">
                                    <div class="flex items-center gap-3">
                                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span>Uploading...</span>
                                    </div>
                                </template>
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            {{-- Right Column: Side Info --}}
            <div class="space-y-8">
                {{-- Updated Deadline Widget --}}
                <div class="bg-white dark:bg-gray-900 rounded-[35px] shadow-sm border border-gray-200 dark:border-gray-800 p-8 text-center relative overflow-hidden">
                    @php
                        $dueDate = \Carbon\Carbon::parse($task->due_date);
                        $isOverdue = $dueDate->isPast() && $task->status->value !== 'completed';
                    @endphp
                    <p class="text-[10px] font-bold {{ $isOverdue ? 'text-red-500' : 'text-gray-500 dark:text-gray-400' }} uppercase tracking-wider mb-4">
                        {{ $isOverdue ? 'Overdue' : 'Deadline' }}
                    </p>
                    
                    {{-- Date Row --}}
                    <div class="flex items-center justify-center gap-3 mb-4">
                        <span class="text-4xl font-extrabold text-gray-800 dark:text-gray-100 tracking-normal">
                            {{ $dueDate->format('d') }}
                        </span>
                        <div class="text-left">
                            <p class="text-sm font-bold text-gray-800 dark:text-gray-100 uppercase leading-none">{{ $dueDate->format('M') }}</p>
                            <p class="text-[10px] font-bold text-gray-500 dark:text-gray-400">{{ $dueDate->format('Y') }}</p>
                        </div>
                    </div>

                    {{-- Time Row (Added) --}}
                    <div class="pt-4 border-t border-gray-100 dark:border-gray-800 flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        <span class="text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider">
                            {{ $dueDate->format('h:i A') }}
                        </span>
                    </div>
                </div>

                {{-- Assignee Info Card --}}
                <div class="bg-[#0B5694] rounded-[35px] shadow-xl p-8 text-white relative overflow-hidden">
                    <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/10 rounded-full blur-2xl"></div>
                    <p class="text-[10px] font-bold text-blue-200 uppercase tracking-wider mb-6 relative">Assigned By</p>
                    
                    <div class="flex items-center gap-4 relative">
                        <x-avatar :user="$task->supervisor" size="12" border="2" background="bg-white/20" />

                        <div>
                            <p class="font-bold text-base leading-tight">{{ $task->supervisor->supervisorDetails->first_name ?? 'Supervisor' }} {{ $task->supervisor->supervisorDetails->last_name ?? '' }}</p>
                            <p class="text-[10px] text-blue-200 font-bold uppercase mt-0.5">{{ $task->supervisor->supervisorDetails->office->name ?? 'Head Office' }}</p>
                        </div>
                    </div>
                </div>

                {{-- Submission History (Sidebar) --}}
                @if($task->submissions->isNotEmpty())
                <div class="bg-white dark:bg-gray-900 rounded-[35px] shadow-sm border border-gray-200 dark:border-gray-800 p-8">
                    <h3 class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-6">Submission History</h3>
                    <div class="space-y-4">
                        @foreach($task->submissions as $submission)
                        <div class="bg-gray-50 dark:bg-gray-800/50 p-4 rounded-2xl border border-gray-100 dark:border-gray-800 flex flex-col gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-8 h-8 bg-blue-50 dark:bg-blue-900/20 text-[#0B5694] dark:text-blue-400 rounded-lg flex items-center justify-center shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path><polyline points="13 2 13 9 20 9"></polyline></svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-bold text-gray-800 dark:text-gray-100 truncate" title="{{ basename($submission->file_path) }}">{{ basename($submission->file_path) }}</p>
                                    <p class="text-[9px] text-gray-500 dark:text-gray-400 font-bold uppercase">{{ $submission->created_at->format('M d • h:i A') }}</p>
                                </div>
                            </div>
                             @if($submission->file_path)
                                 @php
                                     $ext = strtolower(pathinfo($submission->file_path, PATHINFO_EXTENSION));
                                     $isPreviewable = in_array($ext, ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp']);
                                 @endphp
                                 @if($isPreviewable)
                                     <button @click="showPreview = true; fileUrl = '{{ route('submissions.file', $submission) }}'; fileType = '{{ $ext === 'pdf' ? 'pdf' : 'image' }}'" 
                                             class="w-full text-center py-2 bg-white dark:bg-gray-900 text-[9px] font-bold text-[#0B5694] dark:text-blue-400 rounded-xl hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all uppercase tracking-wider border border-gray-200 dark:border-gray-700 active:scale-95 shadow-sm">
                                         Preview File
                                     </button>
                                 @else
                                     <a href="{{ route('submissions.file', $submission) }}" target="_blank" class="w-full text-center py-2 bg-white dark:bg-gray-900 text-[9px] font-bold text-[#0B5694] dark:text-blue-400 rounded-xl hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all uppercase tracking-wider border border-gray-200 dark:border-gray-700">
                                         Download File
                                     </a>
                                 @endif
                             @else
                                 <div class="w-full text-center py-2 bg-white dark:bg-gray-900 text-[9px] font-bold text-gray-400 rounded-xl uppercase tracking-wider border border-gray-200 dark:border-gray-700">No File</div>
                             @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </main>

    <x-notification-sidebar :notifications="$notifications" />

    {{-- PREVIEW MODAL --}}
    <div x-show="showPreview" x-cloak class="fixed inset-0 z-[150] flex items-center justify-center p-3 sm:p-4 md:p-12 bg-black/90 backdrop-blur-md">
        <div class="relative w-full max-w-6xl h-full flex flex-col" @click.away="showPreview = false">
            <div class="flex justify-end mb-4">
                <button @click="showPreview = false" class="text-white/50 hover:text-white p-2 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
            <div class="flex-1 bg-white dark:bg-gray-900 rounded-[40px] overflow-hidden shadow-2xl">
                <div x-show="fileType === 'pdf'" class="w-full h-full">
                    <iframe :src="fileUrl" class="w-full h-full border-none"></iframe>
                </div>
                <div x-show="fileType === 'image'" class="w-full h-full">
                    <div class="w-full h-full flex items-center justify-center bg-gray-100 dark:bg-gray-800 p-4 md:p-8 overflow-auto">
                        <img :src="fileUrl" class="max-w-full max-h-full rounded-2xl shadow-lg object-contain">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
</style>
@endsection
