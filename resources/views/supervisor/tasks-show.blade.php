@extends('layouts.app')

@section('title', 'Review Task - ' . $task->title)

@section('content')
<div class="flex min-h-screen bg-gray-50 dark:bg-gray-950 font-sans text-[14px]" 
     x-data="supervisorTaskPreview">
     
    <x-sidebar portalType="Supervisor" userName="{{ Auth::user()->supervisorDetails->first_name }}" userRole="{{ Auth::user()->supervisorDetails->office->name }}" />

    <main class="ml-0 lg:ml-64 flex-1 p-3 sm:p-4 md:p-6 lg:p-10 pt-20 lg:pt-10 text-left transition-all duration-300">
        <header class="mb-10">
            <a href="{{ route('supervisor.tasks.intern', $task->internDetails->user) }}" class="text-gray-400 dark:text-gray-500 hover:text-[#0B5694] dark:hover:text-blue-400 font-black uppercase text-xs tracking-widest flex items-center gap-2 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                Back to Workload
            </a>
        </header>

        <template x-if="loading">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-8">
                    <x-skeleton class="h-64 w-full rounded-[35px]" />
                    <x-skeleton class="h-64 w-full rounded-[35px]" />
                </div>
                <div class="space-y-6">
                    <x-skeleton class="h-96 w-full rounded-[35px]" />
                    <x-skeleton class="h-32 w-full rounded-[35px]" />
                </div>
            </div>
        </template>

        <div x-show="!loading" x-transition.opacity.duration.500ms class="grid grid-cols-1 md:grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-8">
                
                {{-- SECTION 1: TASK INSTRUCTIONS --}}
                <div class="bg-white dark:bg-gray-900 rounded-[35px] shadow-sm border border-gray-100 dark:border-gray-800 p-4 sm:p-6 md:p-12 relative overflow-hidden">
                    <div class="absolute top-0 right-0 px-8 py-3 bg-gray-50 dark:bg-gray-800 rounded-bl-[20px] border-l border-b border-gray-100 dark:border-gray-700 hidden sm:block">
                        <p class="text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Task Brief</p>
                    </div>
                    
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-black text-gray-800 dark:text-gray-100 tracking-tight mb-6 mt-4 sm:mt-0 uppercase">{{ $task->title }}</h2>
                    <div class="prose prose-blue dark:prose-invert text-gray-600 dark:text-gray-400 font-medium leading-relaxed">
                        <p class="whitespace-pre-line text-sm md:text-base">{{ $task->description }}</p>
                    </div>
                    
                    <div class="mt-8 pt-8 border-t border-gray-50 dark:border-gray-800">
                        <p class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1">Assigned Deadline</p>
                        <p class="font-bold text-gray-700 dark:text-gray-300">{{ \Carbon\Carbon::parse($task->due_date)->format('F d, Y') }}</p>
                    </div>
                </div>

                {{-- SECTION 2: INTERN SUBMISSION --}}
                <div class="bg-white dark:bg-gray-900 rounded-[35px] shadow-sm border border-gray-100 dark:border-gray-800 p-4 sm:p-6 md:p-12">
                    <div class="flex items-center gap-3 mb-8">
                        <div class="w-2 h-8 bg-[#0B5694] dark:bg-blue-600 rounded-full"></div>
                        <h3 class="text-lg md:text-xl font-black text-gray-800 dark:text-gray-100 uppercase tracking-tight">Submission Details</h3>
                    </div>

                    <h3 class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-4">Intern's Comments</h3>
                    <div class="bg-blue-50/30 dark:bg-blue-900/10 rounded-[25px] p-4 sm:p-6 md:p-8 border border-blue-100/50 dark:border-blue-900/20 mb-8">
                        <p class="text-gray-700 dark:text-gray-400 leading-relaxed italic text-sm md:text-base">
                            "{{ $task->submissions->first()->content ?? 'The intern did not provide specific notes for this submission.' }}"
                        </p>
                    </div>

                    @if($task->submissions->isNotEmpty() && $task->submissions->first()->file_path)
                        <h3 class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-4">Delivered File</h3>
                        @php 
                            $latestFile = $task->submissions->first()->file_path;
                            $ext = strtolower(pathinfo($latestFile, PATHINFO_EXTENSION));
                            $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                            $isPDF = ($ext === 'pdf');
                        @endphp

                        <div class="flex flex-col sm:flex-row items-center justify-between bg-white dark:bg-gray-800 p-6 rounded-[25px] border-2 border-gray-100 dark:border-gray-700 shadow-sm gap-3 sm:gap-4">
                            <div class="flex items-center gap-4 w-full sm:w-auto">
                                <div class="w-12 h-12 bg-gray-50 dark:bg-gray-700 text-gray-400 dark:text-gray-500 rounded-2xl flex items-center justify-center flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path><polyline points="13 2 13 9 20 9"></polyline></svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="font-black text-gray-800 dark:text-gray-100 truncate">{{ basename($latestFile) }}</p>
                                    <p class="text-[10px] text-gray-400 dark:text-gray-500 font-bold uppercase">{{ $task->submissions->first()->created_at->format('M d • h:i A') }}</p>
                                </div>
                            </div>
                            
                            <div class="flex gap-2 w-full sm:w-auto">
                                @if($isImage || $isPDF)
                                    <button @click="showPreview = true; fileUrl = '{{ asset($latestFile) }}'; fileType = '{{ $isPDF ? 'pdf' : 'image' }}'" 
                                        class="flex-1 sm:flex-none px-4 md:px-6 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-700 transition-all active:scale-95 shadow-sm">
                                        Preview
                                    </button>
                                @endif
                                <a href="{{ asset($latestFile) }}" target="_blank" class="flex-1 sm:flex-none px-4 md:px-6 py-3 bg-[#0B5694] dark:bg-blue-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-blue-800 dark:hover:bg-blue-700 transition-all shadow-lg dark:shadow-none active:scale-95 text-center">Download</a>
                            </div>
                        </div>
                    @elseif($task->is_file_required)
                        <h3 class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-4">Delivered File</h3>
                        <div class="p-10 text-center border-2 border-dashed border-gray-100 dark:border-gray-800 rounded-[25px]">
                            <p class="text-gray-400 dark:text-gray-500 italic font-medium">No work has been uploaded for this task yet.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- SIDEBAR ACTIONS --}}
            <div class="space-y-4 md:space-y-6">
                <div class="bg-white dark:bg-gray-900 rounded-[35px] shadow-xl dark:shadow-none border border-gray-100 dark:border-gray-800 p-5 md:p-8">
                    <h4 class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-8 text-center">Status Control</h4>
                    <form action="{{ route('supervisor.tasks.update-status', $task) }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PATCH')
                        
                        <div class="space-y-3">
                            <label class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Supervisor Remarks</label>
                            <textarea name="remarks" rows="4" placeholder="Enter feedback or instructions for the intern..." 
                                      class="w-full bg-gray-50 dark:bg-gray-800 border {{ $errors->has('remarks') ? 'border-red-500' : 'border-gray-100 dark:border-gray-800' }} rounded-2xl p-4 text-xs font-medium text-gray-700 dark:text-gray-300 outline-none focus:ring-2 focus:ring-[#0B5694] dark:focus:ring-blue-500 transition-all resize-none placeholder:text-gray-400 dark:placeholder:text-gray-600">{{ old('remarks', $task->remarks) }}</textarea>
                            @error('remarks')
                                <p class="text-[10px] text-red-500 font-bold ml-1 uppercase tracking-tight">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-3">
                            <x-datepicker 
                                name="new_due_date" 
                                label="Update Due Date (Optional)" 
                                placeholder="Extend deadline if needed"
                                type="datetime-local"
                                value="{{ $task->due_date ? $task->due_date->toDateTimeString() : '' }}"
                            />
                            <p class="text-[9px] text-gray-400 dark:text-gray-600 font-medium ml-1">Leave blank to keep the original deadline.</p>
                        </div>

                        <div class="space-y-4">
                            <button type="submit" name="status" value="completed" class="w-full flex items-center justify-center gap-3 bg-emerald-500 dark:bg-emerald-600 text-white py-4 sm:py-5 rounded-[20px] font-black text-xs uppercase tracking-widest hover:bg-emerald-600 dark:hover:bg-emerald-700 transition-all shadow-lg dark:shadow-none active:scale-95">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                Approve Work
                            </button>
                            <button type="submit" name="status" value="revision" class="w-full flex items-center justify-center gap-3 bg-amber-500 dark:bg-amber-600 text-white py-4 sm:py-5 rounded-[20px] font-black text-xs uppercase tracking-widest hover:bg-amber-600 dark:hover:bg-amber-700 transition-all shadow-lg dark:shadow-none active:scale-95">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                Request Changes
                            </button>
                        </div>
                    </form>
                </div>
                
                <div class="bg-[#0B5694] dark:bg-blue-900/40 rounded-[35px] p-5 md:p-8 text-white border dark:border-blue-900/50">
                    <p class="text-[10px] font-black text-blue-200 dark:text-blue-400 uppercase tracking-widest mb-4">Intern Assigned</p>
                    <div class="flex items-center gap-4">
                        <x-avatar :user="$task->internDetails" size="10" border="1" background="bg-white/10" textSize="xs" />
                        <div>
                            <p class="text-sm font-black leading-tight">{{ $task->internDetails->first_name }} {{ $task->internDetails->last_name }}</p>
                            <p class="text-[10px] font-bold text-blue-200 dark:text-blue-400 mt-0.5 uppercase tracking-tighter">{{ $task->internDetails->student_id }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </main>

    {{-- PREVIEW MODAL --}}
    <div x-show="showPreview" x-cloak class="fixed inset-0 z-[150] flex items-center justify-center p-3 sm:p-4 md:p-12 bg-black/90 backdrop-blur-md">
        <div class="relative w-full max-w-6xl h-full flex flex-col" @click.away="showPreview = false">
            <div class="flex justify-end mb-4">
                <button @click="showPreview = false" class="text-white/50 hover:text-white p-2 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
            <div class="flex-1 bg-white dark:bg-gray-900 rounded-[40px] overflow-hidden shadow-2xl">
                <template x-if="fileType === 'pdf'">
                    <iframe :src="fileUrl" class="w-full h-full border-none"></iframe>
                </template>
                <template x-if="fileType === 'image'">
                    <div class="w-full h-full flex items-center justify-center bg-gray-100 dark:bg-gray-800 p-4 md:p-8 overflow-auto">
                        <img :src="fileUrl" class="max-w-full max-h-full rounded-2xl shadow-lg object-contain">
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
</style>
@endsection