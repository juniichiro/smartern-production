@extends('layouts.app')

@section('title', 'Office Announcements')

@section('content')
<x-page-layout 
    portalType="Intern" 
    userName="{{ Auth::user()->internDetails->first_name }}" 
    userRole="{{ Auth::user()->internDetails->office->name }}"
>
    <div x-data="{ loading: true }" x-init="setTimeout(() => loading = false, 800)" class="flex flex-col flex-1">
        {{-- Header Section --}}
        <x-page-header 
            title="Announcements" 
            subtitle="Official Broadcasts & Office Updates" 
        />

        {{-- SKELETON LOADING VIEW --}}
        @php
            $skeletonCount = $announcements->isEmpty() ? 1 : min($announcements->count(), 6);
            $isGrid = !$announcements->isEmpty();
        @endphp

        <div x-show="loading" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="{{ $isGrid ? 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3' : 'flex items-center justify-center min-h-[50vh]' }} gap-6">
            @for($i = 0; $i < $skeletonCount; $i++)
                <x-card-container class="{{ $isGrid ? 'h-64' : 'w-full max-w-sm h-64' }}" padding="p-8">
                    <x-skeleton type="text" class="w-1/4 mb-6" />
                    <x-skeleton type="text" class="h-6 w-3/4 mb-4" />
                    <x-skeleton type="text" rows="3" />
                </x-card-container>
            @endfor
        </div>

        {{-- ACTUAL CONTENT VIEW --}}
        <div x-show="!loading" x-cloak x-transition:enter="transition ease-out duration-500 delay-200" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="flex flex-col">
            {{-- ANNOUNCEMENTS GRID --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($announcements as $ann)
            <div class="flex flex-col h-full bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-[30px] overflow-hidden shadow-sm transition-all duration-300 hover:shadow-xl hover:border-[#0B5694]/30 dark:hover:border-blue-900/50 hover:-translate-y-1 group">
                <!-- Card Header -->
                <div class="px-6 py-4 bg-gray-50/50 dark:bg-gray-800/40 border-b border-gray-100 dark:border-gray-800/80 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="text-emerald-500 w-5 h-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 12-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 0 1-2 2z" />
                        </svg>
                        <span class="text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Office Announcement</span>
                    </div>
                    <span class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Date: {{ $ann->created_at->format('M d, Y') }}</span>
                </div>

                <!-- Card Body -->
                <div class="p-6 flex-1 flex flex-col justify-between">
                    <div class="space-y-4">
                        <h3 class="text-base md:text-lg font-black text-gray-800 dark:text-gray-100 uppercase tracking-tight leading-snug group-hover:text-[#0B5694] dark:group-hover:text-blue-400 transition-colors">
                            <a href="{{ route('intern.announcements.show', $ann) }}" class="hover:underline">
                                {{ $ann->title }}
                            </a>
                        </h3>
                        
                        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium leading-relaxed line-clamp-4 italic">
                            {{ Str::limit(strip_tags($ann->content), 180) }}
                        </p>
                    </div>

                    <!-- Card Footer -->
                    <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-800/50 flex items-center justify-between text-[11px] text-gray-400 dark:text-gray-500">
                        <div class="flex items-center gap-1.5 font-bold uppercase tracking-wider">
                            <svg class="text-[#0B5694] dark:text-blue-400 w-4 h-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 02-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z" />
                            </svg>
                            Uploaded: {{ $ann->created_at->format('M d, Y') }}
                        </div>
                        <a href="{{ route('intern.announcements.show', $ann) }}" class="p-1.5 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-lg transition-colors" title="Read Announcement">
                            <svg class="text-rose-500 w-5 h-5 transition-transform group-hover:translate-x-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full flex-1 flex flex-col items-center justify-center min-h-[50vh]">
                <x-empty-state 
                    title="No Announcements" 
                    subtitle="You're all caught up. Active announcements will appear here."
                    compact="false"
                />
            </div>
        @endforelse
    </div>
    
    @if($announcements->hasPages())
        <div class="mt-12 flex justify-end">
            {{ $announcements->links('vendor.pagination.tailwind-custom') }}
        </div>
    @endif
    </div>
</x-page-layout>
@endsection
