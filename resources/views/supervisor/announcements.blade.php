@extends('layouts.app')

@section('title', 'Office Announcements')

@section('content')
<x-page-layout 
    portalType="Supervisor" 
    userName="{{ Auth::user()->supervisorDetails->first_name }}" 
    userRole="{{ Auth::user()->supervisorDetails->office->name }}"
>
    <div class="flex flex-col h-full" x-data="announcementsManager">
        {{-- Header --}}
        <x-page-header 
            title="Announcements"
            subtitle="Broadcast Announcements."
        >
            <x-slot name="actions">
                <button @click="openCreateModal()" 
                        class="flex items-center justify-center gap-3 w-full sm:w-auto px-5 sm:px-8 py-3 sm:py-4 bg-[#0B5694] dark:bg-blue-600 text-white rounded-[22px] font-bold text-xs uppercase tracking-wider shadow-xl shadow-blue-500/20 hover:scale-[1.02] active:scale-[0.98] transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                    Post New Announcement
                </button>
            </x-slot>
        </x-page-header>

        {{-- Skeletons --}}
        <template x-if="loading">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @for($i = 0; $i < min(count($announcements) ?: 3, 3); $i++)
                    <div class="flex flex-col h-full bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-[30px] overflow-hidden space-y-4">
                        <x-skeleton class="h-12 w-full" />
                        <div class="p-6 space-y-4">
                            <x-skeleton class="h-6 w-3/4 rounded-lg" />
                            <div class="space-y-2">
                                <x-skeleton class="h-3 w-full rounded-lg" />
                                <x-skeleton class="h-3 w-full rounded-lg" />
                                <x-skeleton class="h-3 w-1/2 rounded-lg" />
                            </div>
                            <x-skeleton class="h-10 w-full mt-4 rounded-xl" />
                        </div>
                    </div>
                @endfor
            </div>
        </template>

        {{-- ANNOUNCEMENTS GRID --}}
        <div x-show="!loading" x-transition.opacity.duration.500ms class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($announcements as $ann)
                <div class="flex flex-col h-full bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-[30px] overflow-hidden shadow-sm transition-all duration-300 hover:shadow-xl hover:border-blue-100 dark:hover:border-blue-900/30 hover:-translate-y-1 group">
                    <!-- Card Header -->
                    <div class="px-4 sm:px-6 py-3 sm:py-4 bg-gray-50/50 dark:bg-gray-800/40 border-b border-gray-100 dark:border-gray-800/80 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="text-emerald-500 w-5 h-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span class="text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Office Announcement</span>
                        </div>
                        <span class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Date: {{ $ann->created_at->format('M d, Y') }}</span>
                    </div>

                    <!-- Card Body -->
                    <div class="p-4 sm:p-6 flex-1 flex flex-col justify-between">
                        <div class="space-y-4">
                            <h3 class="text-sm sm:text-base md:text-lg font-black text-gray-800 dark:text-gray-100 uppercase tracking-tight leading-snug">
                                {{ $ann->title }}
                            </h3>
                            
                            <div class="text-sm text-gray-500 dark:text-gray-400 font-medium leading-relaxed line-clamp-4 italic max-w-none">
                                {!! strip_tags($ann->content) !!}
                            </div>
                        </div>

                        <!-- Card Footer -->
                        <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-800/50 flex flex-col gap-4">
                            <div class="flex items-center justify-between text-[11px] text-gray-400 dark:text-gray-500">
                                <div class="flex items-center gap-1.5 font-bold uppercase tracking-wider">
                                    <svg class="text-[#0B5694] dark:text-blue-400 w-4 h-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    Uploaded: {{ $ann->created_at->format('M d, Y') }}
                                </div>
                                
                                @if($ann->attachment)
                                    @php
                                        $cleanPath = str_replace('storage/', '', $ann->attachment);
                                    @endphp
                                    <a href="{{ Storage::disk('public')->url($cleanPath) }}" target="_blank" class="p-1 text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300" title="Access Reference Materials">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                                    </a>
                                @endif
                            </div>

                            <div class="flex items-center justify-end gap-2">
                                {{-- View Button --}}
                                <a href="{{ route('supervisor.announcements.show', $ann->getRouteKey()) }}" 
                                   title="View announcement details"
                                   class="p-2 bg-gray-50 dark:bg-gray-800/20 text-gray-400 dark:text-slate-400 hover:text-[#0B5694] dark:hover:text-blue-400 rounded-xl transition-all border border-gray-150 dark:border-gray-800 shadow-sm active:scale-95 inline-flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </a>

                                {{-- Edit Button --}}
                                <button @click="openEditModal({{ json_encode($ann) }}, '{{ $ann->getRouteKey() }}')" 
                                        title="Refine announcement details"
                                        class="p-2 bg-blue-50 dark:bg-blue-900/20 text-blue-400 dark:text-blue-400 hover:text-blue-600 dark:hover:text-blue-300 rounded-xl transition-all border border-blue-100 dark:border-blue-900/30 shadow-sm active:scale-95">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                                </button>

                                {{-- Delete Button --}}
                                <button @click="openDeleteModal('{{ addslashes($ann->title) }}', '/supervisor/announcements/{{ $ann->getRouteKey() }}')" 
                                        type="button"
                                        title="Withdraw announcement"
                                        class="p-2 bg-rose-50 dark:bg-rose-900/20 text-rose-400 dark:text-rose-400 hover:text-rose-600 dark:hover:text-rose-300 rounded-xl transition-all border border-rose-100 dark:border-rose-900/30 shadow-sm active:scale-95">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white dark:bg-gray-900 rounded-[25px] md:rounded-[35px] p-10 md:p-20 shadow-sm border border-gray-100 dark:border-gray-800 text-center opacity-60">
                    <div class="w-20 h-20 bg-gray-50 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="text-gray-300" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 11 18-5v12L3 14v-3z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/></svg>
                    </div>
                    <p class="font-black text-gray-400 uppercase tracking-[0.2em] text-xs">No announcements have been posted to the feed yet.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-6 md:mt-8">
            {{ $announcements->links() }}
        </div>

        {{-- POST/EDIT MODAL --}}
        <div x-show="showModal" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-950/80 backdrop-blur-md"
             x-cloak
             x-init="
                $watch('showModal', value => {
                    if (value) {
                        setTimeout(() => {
                            $('#announcement-editor').summernote({
                                placeholder: 'Compose your announcement message here...',
                                tabsize: 2,
                                height: 400,
                                minHeight: 250,
                                theme: 'lite',
                                toolbar: [
                                    ['style', ['style']],
                                    ['font', ['bold', 'italic', 'underline', 'clear']],
                                    ['list', ['ul', 'ol', 'paragraph']],
                                    ['insert', ['link']],
                                    ['view', ['fullscreen', 'codeview', 'help']]
                                ],
                                callbacks: {
                                    onChange: function(contents, $editable) {
                                        // Update character count for Alpine in real-time
                                        $data.contentLength = contents.length;
                                    }
                                }
                            });
                            
                            if (!isEdit) {
                                $('#announcement-editor').summernote('code', '');
                            }
                        }, 100);
                    } else {
                        $('#announcement-editor').summernote('destroy');
                    }
                })
             ">
            
            <div @click.away="showModal = false" 
                 class="bg-white dark:bg-[#090D16] w-full max-w-5xl mx-2 sm:mx-4 rounded-[40px] shadow-2xl border border-gray-200 dark:border-gray-800 overflow-hidden animate-in zoom-in-95 duration-300 flex flex-col max-h-[90vh]">

                <header class="p-5 sm:p-8 border-b border-gray-200 dark:border-gray-800 flex justify-between items-center bg-gray-50 dark:bg-[#0D121F] shrink-0">
                    <div>
                        <h3 class="text-xl font-black text-gray-800 dark:text-white uppercase tracking-widest" x-text="previewMode ? 'Announcement Preview' : (isEdit ? 'Edit Announcement' : 'Compose Announcement')"></h3>
                    </div>
                    <button @click="showModal = false" class="p-3 bg-gray-200 dark:bg-gray-800/50 hover:bg-gray-300 dark:hover:bg-gray-800 text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-white rounded-2xl transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </header>

                <form :action="isEdit ? '/supervisor/announcements/' + editId : '{{ route('supervisor.announcements.store') }}'" 
                      method="POST" 
                      enctype="multipart/form-data" 
                      class="flex flex-col flex-1 overflow-hidden" 
                      id="announcementForm"
                      onsubmit="$('#announcement-editor').val($('#announcement-editor').summernote('code'))">
                    @csrf
                    {{-- More reliable method handling for Alpine --}}
                    <input type="hidden" name="_method" :value="isEdit ? 'PUT' : 'POST'">

                    {{-- SCROLLABLE BODY --}}
                    <div class="flex-1 overflow-y-auto custom-scrollbar p-5 sm:p-8">
                        {{-- Validation Errors --}}
                        @if ($errors->any())
                            <div class="mb-8 p-4 bg-rose-50 dark:bg-rose-900/20 border border-rose-100 dark:border-rose-900/30 rounded-2xl">
                                <ul class="list-disc list-inside text-xs font-bold text-rose-600 dark:text-rose-400 uppercase tracking-widest space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- EDITOR MODE --}}
                        <div x-show="!previewMode" class="space-y-8 animate-in fade-in duration-300">
                            <div class="space-y-2">
                                <div class="flex justify-between items-center mb-1">
                                    <label class="text-[11px] font-black text-gray-400 dark:text-slate-400 uppercase tracking-[0.1em] ml-1">Announcement Subject</label>
                                    <span class="text-[10px] font-bold tracking-widest uppercase" :class="title.length > maxTitle ? 'text-rose-500' : 'text-gray-400'">
                                        <span x-text="title.length"></span> / <span x-text="maxTitle"></span>
                                    </span>
                                </div>
                                <input type="text" name="title" x-model="title" required placeholder="Enter clear, concise subject line..." 
                                       class="w-full bg-white dark:bg-[#0F172A] border border-gray-200 dark:border-[#1E293B] rounded-lg px-4 py-3 text-sm font-medium text-gray-900 dark:text-white shadow-none outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/50 transition-all placeholder-gray-300 dark:placeholder-[#64748B]">
                            </div>

                            <div class="space-y-2">
                                <div class="flex justify-between items-center mb-1">
                                    <label class="text-[11px] font-black text-gray-400 dark:text-slate-400 uppercase tracking-[0.1em] ml-1">Announcement Content</label>
                                    <span class="text-[10px] font-bold tracking-widest uppercase" :class="contentLength > maxContent ? 'text-rose-500' : 'text-gray-400'">
                                        <span x-text="contentLength"></span> / <span x-text="maxContent"></span>
                                    </span>
                                </div>
                                <div class="rounded-xl overflow-hidden border border-gray-200 dark:border-[#1E293B] bg-white dark:bg-[#0F172A]">
                                    <textarea id="announcement-editor" name="content"></textarea>
                                </div>
                            </div>
                        </div>

                        {{-- PREVIEW MODE (Mimics Intern View) --}}
                        <div x-show="previewMode" class="space-y-8 animate-in zoom-in-95 duration-300" x-cloak>
                            <div class="bg-white dark:bg-gray-900 rounded-[35px] p-4 sm:p-6 md:p-12 shadow-sm border border-gray-100 dark:border-gray-800 relative text-left">
                                {{-- Category & Date --}}
                                <div class="flex flex-col sm:flex-row sm:items-center gap-4 mb-8">
                                    <span class="w-fit px-4 py-1.5 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 rounded-full text-[10px] font-black uppercase tracking-[0.2em] border border-emerald-100 dark:border-emerald-800/50">
                                        Official Office Announcement
                                    </span>
                                    <span class="text-[11px] text-gray-500 dark:text-gray-400 font-bold uppercase tracking-widest">
                                        Just Now • Posted {{ now()->format('F d, Y') }}
                                    </span>
                                </div>

                                {{-- Title --}}
                                <h1 class="text-xl sm:text-2xl md:text-4xl font-black text-gray-800 dark:text-gray-100 tracking-tight mb-8 leading-tight uppercase" x-text="title"></h1>

                                {{-- Text Content --}}
                                <div class="prose prose-blue dark:prose-invert max-w-none text-gray-600 dark:text-gray-300 font-medium leading-relaxed mb-10" x-html="previewContent"></div>

                                {{-- ATTACHMENT PLACEHOLDER --}}
                                <template x-if="fileName !== 'Attach supporting media'">
                                    <div class="mb-12">
                                        <div class="flex items-center justify-between bg-gray-50 dark:bg-gray-800/50 p-6 rounded-2xl border border-gray-100 dark:border-gray-800">
                                            <div class="flex items-center gap-4 overflow-hidden">
                                                <div class="w-12 h-12 bg-white dark:bg-gray-800 rounded-xl flex-shrink-0 flex items-center justify-center shadow-sm border border-gray-100 dark:border-gray-800">
                                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#0B5694" stroke-width="2.5"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
                                                </div>
                                                <div class="truncate">
                                                    <p class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-0.5">Attached Resource</p>
                                                    <p class="text-sm font-black text-gray-700 dark:text-gray-200 truncate pr-4" x-text="fileName"></p>
                                                </div>
                                            </div>
                                            <div class="flex-shrink-0 px-6 py-2 bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500 text-[10px] font-black uppercase tracking-widest rounded-xl cursor-not-allowed">
                                                Review Preview
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <hr class="border-gray-50 dark:border-gray-800 mb-10">

                                {{-- The Creator Info (Supervisor) --}}
                                <div class="flex items-center gap-5 bg-gray-50/50 dark:bg-gray-800/20 p-6 rounded-2xl border border-dashed border-gray-100 dark:border-gray-800">
                                    <x-avatar :user="Auth::user()" size="14" border="0" circular="true" textSize="sm" />
                                    <div>
                                        <p class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1">Authorizing Supervisor</p>
                                        <p class="text-lg font-black text-gray-800 dark:text-gray-100 tracking-tight leading-none mb-1 uppercase">
                                            {{ Auth::user()->supervisorDetails->first_name }} 
                                            {{ Auth::user()->supervisorDetails->last_name }}
                                        </p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 font-bold uppercase tracking-tighter">
                                            Office Administrator • {{ Auth::user()->supervisorDetails->office->name }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- FIXED FOOTER --}}
                    <div class="p-5 sm:p-8 border-t border-gray-200 dark:border-gray-800 flex flex-col sm:flex-row justify-between items-center gap-6 bg-gray-50 dark:bg-[#0D121F] shrink-0">
                        {{-- EDITOR FOOTER ACTIONS --}}
                        <div x-show="!previewMode" class="flex items-center justify-between w-full">
                            <div class="relative group">
                                <input type="file" name="attachment" id="modal-attachment" @change="handleFileChange" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                <div class="flex items-center gap-3 text-gray-400 dark:text-slate-400 group-hover:text-gray-800 dark:group-hover:text-white transition-colors cursor-pointer overflow-hidden">
                                    <div class="p-2 bg-white dark:bg-[#1E293B] rounded-md group-hover:bg-gray-100 dark:group-hover:bg-[#2D3748] transition-all border border-gray-200 dark:border-transparent shrink-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                                    </div>
                                    <span class="text-[11px] font-bold uppercase tracking-widest truncate max-w-[150px] sm:max-w-[200px]" x-text="fileName" :title="fileName"></span>
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                <button type="button" @click="showModal = false" 
                                        class="text-gray-400 dark:text-[#94A3B8] text-[13px] font-semibold uppercase tracking-[0.05em] hover:text-gray-800 dark:hover:text-white transition-colors">Discard Draft</button>
                                <button type="button" @click="enterPreview()"
                                        class="bg-blue-50 dark:bg-[#1E293B] text-blue-600 dark:text-white h-11 px-6 rounded-lg text-[13px] font-bold uppercase tracking-[0.05em] border border-blue-100 dark:border-gray-700 flex items-center justify-center hover:bg-blue-100 dark:hover:bg-gray-800 transition-all">
                                    Preview Announcement
                                </button>
                                {{-- Direct Publish button for those who skip preview --}}
                                <button type="submit" 
                                        @click="const code = $('#announcement-editor').summernote('code'); console.log('Syncing content:', code); $('#announcement-editor').val(code);"
                                        :disabled="title.length > maxTitle || contentLength > maxContent"
                                        class="bg-[#0B5694] dark:bg-blue-600 text-white h-11 px-6 rounded-lg text-[13px] font-black uppercase tracking-[0.1em] shadow-xl hover:bg-[#073D6B] active:scale-[0.98] transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                                    <span x-text="isEdit ? 'Update' : 'Publish'"></span>
                                </button>
                            </div>
                        </div>

                        {{-- PREVIEW FOOTER ACTIONS --}}
                        <div x-show="previewMode" class="flex items-center justify-between w-full" x-cloak>
                            <button type="button" @click="previewMode = false"
                                    class="flex items-center gap-2 text-blue-600 dark:text-blue-400 text-[13px] font-black uppercase tracking-widest hover:opacity-80 transition-all">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="m15 18-6-6 6-6"/></svg>
                                Back to Edit
                            </button>

                            <button type="submit" 
                                    @click="$('#announcement-editor').val($('#announcement-editor').summernote('code'))"
                                    :disabled="title.length > maxTitle || contentLength > maxContent"
                                    class="bg-[#0284C7] text-white h-11 px-10 rounded-lg text-[13px] font-black uppercase tracking-[0.1em] shadow-xl shadow-blue-900/40 flex items-center justify-center hover:bg-[#0369a1] active:scale-[0.98] transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                                <span x-text="isEdit ? 'Finalize & Update' : 'Finalize & Publish'"></span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>


        {{-- DELETE CONFIRMATION MODAL --}}
        <div x-show="showDeleteModal" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[110] flex items-center justify-center p-4 bg-gray-950/80 backdrop-blur-md"
             x-cloak>
            
            <div @click.away="showDeleteModal = false" 
                 class="bg-white dark:bg-[#090D16] w-full max-w-lg rounded-[40px] shadow-2xl border border-gray-200 dark:border-gray-800 overflow-hidden animate-in zoom-in-95 duration-300">
                
                <div class="p-6 sm:p-10 text-center">
                    {{-- Warning Icon --}}
                    <div class="w-20 h-20 bg-rose-50 dark:bg-rose-900/20 rounded-full flex items-center justify-center mx-auto mb-8 border border-rose-100 dark:border-rose-900/30">
                        <svg class="text-rose-500" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                    </div>

                    <h3 class="text-2xl font-black text-gray-800 dark:text-white uppercase tracking-tight mb-4">Withdraw Announcement?</h3>
                    <p class="text-gray-500 dark:text-gray-400 font-medium leading-relaxed mb-8 italic">
                        You are about to withdraw <span class="text-rose-500 font-bold" x-text="'&quot;' + deleteTitle + '&quot;'"></span>. This action will remove it from the intern dashboard permanently and cannot be undone.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-4">
                        <button @click="showDeleteModal = false" 
                                class="flex-1 px-8 py-4 bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-gray-200 dark:hover:bg-gray-700 transition-all">
                            Keep Announcement
                        </button>
                        
                        <form :action="deleteRoute" method="POST" class="flex-1">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="w-full px-8 py-4 bg-rose-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl shadow-rose-900/20 hover:bg-rose-700 hover:scale-[1.02] active:scale-[0.98] transition-all">
                                Finalize Withdrawal
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-page-layout>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
<link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.js"></script>

<style>
    /* Premium Theming for Summernote Lite */
    .note-editor.note-frame { 
        border: 1px solid #e2e8f0 !important; 
        border-radius: 16px !important; 
        overflow: hidden !important; 
        background: #fff !important;
    }
    
    .dark .note-editor.note-frame { 
        border-color: #1e293b !important; 
        background: #0f172a !important; 
    }

    .note-toolbar { 
        background: #f8fafc !important; 
        border-bottom: 1px solid #e2e8f0 !important; 
        padding: 10px 14px !important; 
    }
    
    .dark .note-toolbar { 
        background: #0d121f !important; 
        border-color: #1e293b !important; 
    }

    .note-editable { 
        background: transparent !important; 
        color: #1e293b !important; 
        padding: 24px !important; 
        font-family: 'Outfit', sans-serif !important; 
        line-height: 1.8 !important; 
    }
    
    .dark .note-editable { 
        color: #e2e8f0 !important; 
    }

    /* Adjust button appearance for premium feel without breaking layout */
    .note-btn { 
        background: transparent !important; 
        border: 1px solid transparent !important; 
        color: #475569 !important; 
        transition: all 0.2s !important; 
    }

    .dark .note-btn { 
        color: #94a3b8 !important; 
    }

    .note-btn:hover { 
        background: #f1f5f9 !important; 
        color: #0f172a !important; 
    }

    .dark .note-btn:hover { 
        background: #1e293b !important; 
        color: #fff !important; 
    }

    /* Dropdown Theming */
    .note-dropdown-menu { 
        background: #fff !important; 
        border: 1px solid #e5e7eb !important; 
        border-radius: 12px !important; 
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important; 
    }
    
    .dark .note-dropdown-menu { 
        background: #0d121f !important; 
        border-color: #1e293b !important; 
    }

    .note-dropdown-item:hover { 
        background: #f1f5f9 !important; 
    }

    .dark .note-dropdown-item:hover { 
        background: #1e293b !important; 
    }
</style>
@endpush
