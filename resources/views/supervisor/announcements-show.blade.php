@extends('layouts.app')

@section('title', 'Announcement - ' . $announcement->title)

@section('content')
<x-page-layout 
    portalType="Supervisor" 
    userName="{{ Auth::user()->supervisorDetails->first_name }}" 
    userRole="{{ Auth::user()->supervisorDetails->office->name }}"
>
    <div class="max-w-4xl mx-auto flex flex-col flex-1" x-data="{
        showModal: false,
        isEdit: true,
        editId: '{{ $announcement->getRouteKey() }}',
        title: '{{ addslashes($announcement->title) }}',
        contentLength: {{ strlen($announcement->content ?? '') }},
        maxTitle: 255,
        maxContent: 10000,
        fileName: '{{ $announcement->attachment ? basename($announcement->attachment) : 'Attach supporting media' }}',
        previewMode: false,
        previewContent: '',
        showDeleteModal: false,
        deleteRoute: '/supervisor/announcements/{{ $announcement->getRouteKey() }}',
        deleteTitle: '{{ addslashes($announcement->title) }}',
        handleFileChange(event) {
            const file = event.target.files[0];
            this.fileName = file ? file.name : 'Attach supporting media';
        },
        syncContent() {
            const code = $('#announcement-editor').summernote('code');
            $('#announcement-editor').val(code);
            this.contentLength = code.length;
        },
        enterPreview() {
            this.syncContent();
            this.previewContent = $('#announcement-editor').val();
            this.previewMode = true;
        }
    }">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
            <a href="{{ route('supervisor.announcements') }}" class="w-fit text-[11px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em] hover:text-[#0B5694] inline-flex items-center gap-2 transition-all hover:-translate-x-1 group">
                <svg class="group-hover:stroke-[3.5]" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="m15 18-6-6 6-6"/></svg>
                Back to Feed
            </a>
            
            <div class="flex items-center gap-2 self-end sm:self-auto">
                {{-- Edit Button --}}
                <button @click="showModal = true; setTimeout(() => { $('#announcement-editor').summernote('code', `{{ $announcement->content }}`); $('#announcement-editor').val(`{{ $announcement->content }}`); }, 200)" 
                        title="Refine announcement details"
                        class="flex items-center gap-2 px-5 py-2.5 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 hover:bg-blue-100 rounded-xl transition-all border border-blue-100 dark:border-blue-900/30 shadow-sm active:scale-95 text-xs font-bold uppercase tracking-wider">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                    Edit
                </button>

                {{-- Delete Button --}}
                <button @click="showDeleteModal = true" 
                        type="button"
                        title="Withdraw announcement"
                        class="flex items-center gap-2 px-5 py-2.5 bg-rose-50 dark:bg-rose-900/20 text-rose-600 dark:text-rose-400 hover:bg-rose-100 rounded-xl transition-all border border-rose-100 dark:border-rose-900/30 shadow-sm active:scale-95 text-xs font-bold uppercase tracking-wider">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                    Withdraw
                </button>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-[35px] p-6 lg:p-12 shadow-sm border border-gray-100 dark:border-gray-800 relative overflow-hidden animate-in fade-in slide-in-from-bottom-4 duration-500">
            {{-- Category & Date --}}
            <div class="flex flex-col sm:flex-row sm:items-center gap-4 mb-8">
                <span class="w-fit px-4 py-1.5 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 rounded-full text-[10px] font-bold uppercase tracking-[0.2em] border border-emerald-100 dark:border-emerald-800/50">
                    Official Office Announcement
                </span>
                <span class="text-[11px] text-gray-500 dark:text-gray-400 font-bold uppercase tracking-wider">
                    {{ $announcement->created_at->format('F d, Y') }} • {{ $announcement->created_at->diffForHumans() }}
                </span>
            </div>

            {{-- Title --}}
            <h1 class="text-2xl md:text-4xl font-extrabold text-gray-800 dark:text-gray-100 tracking-normal mb-8 leading-tight uppercase">
                {{ $announcement->title }}
            </h1>

            {{-- Text Content --}}
            <div class="prose prose-blue dark:prose-invert max-w-none text-gray-600 dark:text-gray-300 font-medium leading-relaxed mb-10">
                @if($announcement->content)
                    {!! $announcement->content !!}
                @else
                    <p class="italic text-gray-400">This announcement has no additional text content.</p>
                @endif
            </div>

            {{-- ATTACHMENT SECTION --}}
            @if($announcement->attachment)
                <div class="mb-12">
                    @php 
                        $extension = pathinfo($announcement->attachment, PATHINFO_EXTENSION);
                        $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                        $fileName = basename($announcement->attachment);
                        $cleanPath = str_replace('storage/', '', $announcement->attachment);
                        $attachmentUrl = Storage::disk('public')->url($cleanPath);
                    @endphp

                    @if($isImage)
                        {{-- Image Display --}}
                        <div class="rounded-3xl overflow-hidden border border-gray-100 dark:border-gray-800 shadow-sm bg-gray-50 dark:bg-gray-800/50">
                            <img src="{{ $attachmentUrl }}" 
                                 class="w-full h-auto object-cover max-h-[700px] hover:scale-[1.01] transition-transform duration-500" 
                                 alt="Announcement Image">
                        </div>
                    @else
                        {{-- File Box --}}
                        <div class="flex flex-col sm:flex-row items-center justify-between bg-gray-50 dark:bg-gray-800/50 p-6 rounded-2xl border border-gray-100 dark:border-gray-800 gap-4 mb-6">
                            <div class="flex items-center gap-4 overflow-hidden w-full">
                                <div class="w-12 h-12 bg-white dark:bg-gray-800 rounded-xl flex-shrink-0 flex items-center justify-center shadow-sm border border-gray-100 dark:border-gray-800">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#0B5694" stroke-width="2.5"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
                                </div>
                                <div class="truncate">
                                    <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-0.5">Attached Resource</p>
                                    <p class="text-sm font-bold text-gray-700 dark:text-gray-200 truncate pr-4" title="{{ $fileName }}">
                                        {{ $fileName }}
                                    </p>
                                </div>
                            </div>
                            <a href="{{ $attachmentUrl }}" 
                               target="_blank"
                               class="w-full sm:w-auto flex-shrink-0 px-6 py-3 bg-[#0B5694] text-white text-[10px] font-bold uppercase tracking-[0.2em] rounded-xl hover:bg-blue-700 transition-all shadow-lg shadow-blue-500/20 text-center">
                                Access Materials
                            </a>
                        </div>

                        @if(strtolower($extension) === 'pdf')
                            <div class="mt-6">
                                <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-3">Document Preview</p>
                                <div class="rounded-3xl overflow-hidden border border-gray-100 dark:border-gray-800 shadow-sm bg-gray-50 dark:bg-gray-800/50 p-1">
                                    <iframe src="{{ $attachmentUrl }}" 
                                            class="w-full h-[600px] rounded-2xl" 
                                            style="border: none;">
                                    </iframe>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            @endif

            <hr class="border-gray-50 dark:border-gray-800 mb-10">

            {{-- Authorizing Info --}}
            <div class="flex items-center gap-5 bg-gray-50/50 dark:bg-gray-800/20 p-6 rounded-2xl border border-dashed border-gray-100 dark:border-gray-800">
                <x-avatar :user="$announcement->user" size="14" border="0" circular="true" textSize="sm" />
                <div>
                    <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Authorizing Supervisor</p>
                    <p class="text-lg font-black text-gray-800 dark:text-gray-100 tracking-tight leading-none mb-1 uppercase">
                        @if($announcement->user->supervisorDetails)
                            {{ $announcement->user->supervisorDetails->first_name }} 
                            {{ $announcement->user->supervisorDetails->last_name }}
                        @else
                            {{ $announcement->user->name }}
                        @endif
                    </p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 font-bold uppercase tracking-tighter">
                        @if($announcement->user->supervisorDetails)
                            Office Administrator • {{ $announcement->user->supervisorDetails->office->name }}
                        @else
                            System Administrator
                        @endif
                    </p>
                </div>
            </div>
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
                                        $data.contentLength = contents.length;
                                    }
                                }
                            });
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
                        <h3 class="text-xl font-black text-gray-800 dark:text-white uppercase tracking-widest" x-text="previewMode ? 'Announcement Preview' : 'Edit Announcement'"></h3>
                    </div>
                    <button @click="showModal = false" class="p-3 bg-gray-200 dark:bg-gray-800/50 hover:bg-gray-300 dark:hover:bg-gray-800 text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-white rounded-2xl transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </header>

                <form :action="'/supervisor/announcements/' + editId" 
                      method="POST" 
                      enctype="multipart/form-data" 
                      class="flex flex-col flex-1 overflow-hidden" 
                      id="announcementForm"
                      onsubmit="$('#announcement-editor').val($('#announcement-editor').summernote('code'))">
                    @csrf
                    <input type="hidden" name="_method" value="PUT">

                    {{-- SCROLLABLE BODY --}}
                    <div class="flex-1 overflow-y-auto custom-scrollbar p-5 sm:p-8">
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

                        {{-- PREVIEW MODE --}}
                        <div x-show="previewMode" class="space-y-8 animate-in zoom-in-95 duration-300" x-cloak>
                            <div class="bg-white dark:bg-gray-900 rounded-[35px] p-4 sm:p-6 md:p-12 shadow-sm border border-gray-100 dark:border-gray-800 relative text-left">
                                <div class="flex flex-col sm:flex-row sm:items-center gap-4 mb-8">
                                    <span class="w-fit px-4 py-1.5 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 rounded-full text-[10px] font-black uppercase tracking-[0.2em] border border-emerald-100 dark:border-emerald-800/50">
                                        Official Office Announcement
                                    </span>
                                    <span class="text-[11px] text-gray-500 dark:text-gray-400 font-bold uppercase tracking-widest">
                                        Just Now • Posted {{ now()->format('F d, Y') }}
                                    </span>
                                </div>

                                <h1 class="text-xl sm:text-2xl md:text-4xl font-black text-gray-800 dark:text-gray-100 tracking-tight mb-8 leading-tight uppercase" x-text="title"></h1>
                                <div class="prose prose-blue dark:prose-invert max-w-none text-gray-600 dark:text-gray-300 font-medium leading-relaxed mb-10" x-html="previewContent"></div>

                                <template x-if="fileName !== 'Attach supporting media'">
                                    <div class="mb-12">
                                        <div class="flex items-center justify-between bg-gray-55 dark:bg-gray-800/55 p-6 rounded-2xl border border-gray-100 dark:border-gray-800">
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
                            </div>
                        </div>
                    </div>

                    {{-- FIXED FOOTER --}}
                    <div class="p-5 sm:p-8 border-t border-gray-200 dark:border-gray-800 flex flex-col sm:flex-row justify-between items-center gap-6 bg-gray-50 dark:bg-[#0D121F] shrink-0">
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
                                <button type="submit" 
                                        :disabled="title.length > maxTitle || contentLength > maxContent"
                                        class="bg-[#0B5694] dark:bg-blue-600 text-white h-11 px-6 rounded-lg text-[13px] font-black uppercase tracking-[0.1em] shadow-xl hover:bg-[#073D6B] active:scale-[0.98] transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                                    Update
                                </button>
                            </div>
                        </div>

                        <div x-show="previewMode" class="flex items-center justify-between w-full" x-cloak>
                            <button type="button" @click="previewMode = false"
                                    class="flex items-center gap-2 text-blue-600 dark:text-blue-400 text-[13px] font-black uppercase tracking-widest hover:opacity-80 transition-all">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="m15 18-6-6 6-6"/></svg>
                                Back to Edit
                            </button>

                            <button type="submit" 
                                    :disabled="title.length > maxTitle || contentLength > maxContent"
                                    class="bg-[#0284C7] text-white h-11 px-10 rounded-lg text-[13px] font-black uppercase tracking-[0.1em] shadow-xl shadow-blue-900/40 flex items-center justify-center hover:bg-[#0369a1] active:scale-[0.98] transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                                Finalize & Update
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
