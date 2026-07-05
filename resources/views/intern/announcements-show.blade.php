@extends('layouts.app')

@section('title', 'Announcement - ' . $announcement->title)

@section('content')
<x-page-layout 
    portalType="Intern" 
    userName="{{ Auth::user()->internDetails->first_name ?? Auth::user()->name ?? 'Intern' }}" 
    userRole="{{ Auth::user()->internDetails->office->name ?? 'Unassigned Office' }}"
>
    <div class="max-w-4xl mx-auto flex flex-col flex-1">
        <a href="{{ route('intern.announcements') }}" class="w-fit text-[11px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em] hover:text-[#0B5694] mb-8 inline-flex items-center gap-2 transition-all hover:-translate-x-1 group">
            <svg class="group-hover:stroke-[3.5]" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="m15 18-6-6 6-6"/></svg>
            Back to Feed
        </a>

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
                        {{-- File Download Box --}}
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

            {{-- The Creator Info --}}
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
    </div>
</x-page-layout>
@endsection
