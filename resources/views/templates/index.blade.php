@extends('layouts.app')

@section('title', 'Document Templates')

@section('content')
@php
    $user = Auth::user();
    $roleName = $user->role_id->label();

    $details = $user->internDetails ?? $user->supervisorDetails ?? $user->schoolCoordinatorDetails;
    $displayName = $details ? $details->first_name : 'Admin';
    $displayRole = $details ? ($details->office->name ?? $details->school ?? 'SDO Quezon City') : 'System Admin';
@endphp

<x-page-layout 
    portalType="{{ $roleName }}" 
    userName="{{ $displayName }}" 
    userRole="{{ $displayRole }}"
>
    <div class="flex flex-col h-full" x-data="{ loading: true }" x-init="setTimeout(() => loading = false, 800)">
        
        {{-- Header Section --}}
        <x-page-header 
            title="Request Templates"
            subtitle="Download official form templates to submit account, attendance, or security change requests to the administration"
        />

        {{-- Skeletons --}}
        <div x-show="loading" class="grid grid-cols-1 md:grid-cols-2 gap-8">
            @for($i = 0; $i < 2; $i++)
                <x-card-container class="h-[280px]" padding="p-8">
                    <div class="flex items-center gap-4 mb-6">
                        <x-skeleton class="w-12 h-12 rounded-2xl shrink-0" />
                        <div class="space-y-2 w-full">
                            <x-skeleton class="h-5 w-2/3 rounded-lg" />
                            <x-skeleton class="h-3 w-1/3 rounded-lg" />
                        </div>
                    </div>
                    <x-skeleton class="h-4 w-full rounded-lg mb-2" />
                    <x-skeleton class="h-4 w-4/5 rounded-lg mb-6" />
                    <x-skeleton class="h-12 w-full rounded-xl" />
                </x-card-container>
            @endfor
        </div>

        {{-- Grid Content --}}
        <div x-show="!loading" x-cloak x-transition.opacity.duration.500ms class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
            @foreach($templates as $tpl)
                <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-[28px] p-8 shadow-sm hover:shadow-lg transition-all duration-300 flex flex-col justify-between group">
                    <div>
                        {{-- Icon & Header --}}
                        <div class="flex items-center gap-4 mb-6">
                            <div class="p-3 bg-blue-50 dark:bg-blue-900/20 text-[#0B5694] dark:text-blue-400 rounded-2xl group-hover:scale-110 transition-transform duration-300">
                                {!! $tpl['icon'] !!}
                            </div>
                            <div>
                                <h3 class="text-md sm:text-lg font-black text-gray-800 dark:text-gray-100 uppercase tracking-tight leading-tight group-hover:text-[#0B5694] dark:group-hover:text-blue-400 transition-colors">
                                    {{ $tpl['title'] }}
                                </h3>
                                <p class="text-[9px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mt-1">Official Document Template</p>
                            </div>
                        </div>

                        {{-- Description --}}
                        <p class="text-xs text-gray-400 dark:text-gray-500 font-medium leading-relaxed mb-6">
                            {{ $tpl['description'] }}
                        </p>

                        {{-- Required fields preview badge --}}
                        <div class="mb-8">
                            <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-3">Required Form Fields</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach($tpl['fields'] as $f)
                                    <span class="px-3 py-1.5 bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400 rounded-xl text-[9px] font-bold uppercase tracking-wider border border-gray-100 dark:border-gray-700">
                                        {{ $f }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Download CTA Button --}}
                    <a href="{{ route('templates.download', ['type' => $tpl['id']]) }}" 
                       class="w-full bg-[#0B5694] dark:bg-blue-600 hover:bg-[#084577] dark:hover:bg-blue-700 text-white font-bold py-3.5 px-6 rounded-2xl text-xs uppercase tracking-widest transition flex items-center justify-center gap-2 active:scale-95 shadow-md shadow-blue-500/10">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Download PDF Template
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</x-page-layout>
@endsection
