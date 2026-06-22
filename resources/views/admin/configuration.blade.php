@extends('layouts.app')

@section('title', 'System Configuration')

@section('content')
<x-page-layout 
    portalType="Admin" 
    userName="{{ Auth::user()->email }}" 
    userRole="Admin"
>
    <div x-data="configurationManager()" class="flex flex-col flex-1">
        {{-- Header --}}
        <x-page-header 
            title="System Configuration" 
            subtitle="Backups, Maintenance & Global Settings" 
        />

        {{-- SKELETON LOADING VIEW --}}
        @php
            $backupSkeletonCount = $backups->isEmpty() ? 1 : min($backups->count(), 5);
        @endphp

        <div x-show="loading" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="flex flex-col flex-1">
            <x-card-container title="Backup Management" padding="p-6 lg:p-10">
                <x-skeleton type="text" class="w-1/2 lg:w-1/3 mb-6 lg:mb-10" />
                <x-skeleton type="list" :rows="min($backupSkeletonCount, 3)" />
            </x-card-container>
        </div>

        {{-- ACTUAL CONTENT VIEW --}}
        <div x-show="!loading" x-cloak x-transition:enter="transition ease-out duration-500 delay-200" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="flex flex-col flex-1">
        {{-- Backup Management Card --}}
        <x-card-container 
            title="Backup Management"
            headerActionText="Maintenance Overview"
        >
            <x-slot name="headerAction">
                <div class="flex flex-col sm:items-end gap-2 w-full lg:w-auto">
                    <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider bg-gray-50 dark:bg-gray-800 px-4 py-2 rounded-full border border-gray-100 dark:border-gray-800">
                        Next Scheduled Backup: <span class="text-[#0B5694] dark:text-blue-400 ml-1">Daily at 12:00 AM</span>
                    </p>
                </div>
            </x-slot>

            <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 mb-10">
                <button type="button" 
                        @click="showUploadModal = true"
                        class="w-full sm:w-auto bg-white dark:bg-gray-800 text-[#0B5694] dark:text-blue-400 px-6 sm:px-8 min-h-[44px] rounded-2xl font-bold text-xs uppercase tracking-wider shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition-all hover:scale-[1.02] active:scale-[0.98] flex items-center justify-center gap-3 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    Upload Snapshot
                </button>
                <form action="{{ route('admin.backup.trigger') }}" method="POST" x-data="loadingState" @submit="loading = true" class="w-full sm:w-auto">
                    @csrf
                    <button type="submit" 
                            :disabled="loading"
                            class="w-full bg-[#0B5694] dark:bg-blue-600 text-white px-6 sm:px-8 min-h-[44px] rounded-2xl font-bold text-xs uppercase tracking-wider shadow-xl shadow-blue-500/20 hover:shadow-blue-500/40 transition-all hover:scale-[1.02] active:scale-[0.98] flex items-center justify-center gap-3 disabled:opacity-70 disabled:cursor-not-allowed cursor-pointer">
                        <template x-if="!loading">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        </template>
                        <template x-if="loading">
                            <svg class="animate-spin" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                        </template>
                        <span x-text="loading ? 'Creating Backup...' : 'Trigger Manual Backup'"></span>
                    </button>
                </form>
            </div>

            {{-- Desktop Backups Table --}}
            <div class="hidden md:block border border-gray-100 dark:border-gray-800 rounded-3xl custom-scrollbar">
                <div class="overflow-x-auto">
                    <table class="w-full text-left min-w-[600px]">
                        <thead class="bg-gray-50 dark:bg-gray-800 text-[9px] lg:text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider border-b border-gray-100 dark:border-gray-800">
                            <tr>
                                <th class="px-6 lg:px-8 py-5">Date & Time</th>
                                <th class="px-6 lg:px-8 py-5">Type</th>
                                <th class="px-6 lg:px-8 py-5">Size</th>
                                <th class="px-6 lg:px-8 py-5 text-center">Status</th>
                                <th class="px-6 lg:px-8 py-5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                            @forelse($backups as $backup)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors group">
                                    <td class="px-4 sm:px-6 lg:px-8 py-6">
                                        <div class="flex flex-col text-left">
                                            <span class="font-bold text-gray-800 dark:text-gray-200 text-xs lg:text-sm uppercase tracking-wider">{{ $backup->created_at->format('M d, Y') }}</span>
                                            <span class="text-[9px] lg:text-[10px] text-gray-400 dark:text-gray-500 font-bold uppercase tracking-wider mt-0.5">{{ $backup->created_at->format('h:i A') }}</span>
                                            @if($backup->sha256_hash)
                                                <span class="text-[7px] sm:text-[8px] text-gray-400 dark:text-gray-600 font-mono tracking-tighter mt-1 block select-all" title="SHA-256 Integrity Hash">SHA-256: {{ substr($backup->sha256_hash, 0, 10) }}...</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 sm:px-6 lg:px-8 py-6">
                                        <span class="px-2 sm:px-3 lg:px-4 py-1.5 rounded-full text-[8px] sm:text-[9px] font-bold uppercase tracking-wider {{ $backup->backup_type === 'auto' ? 'bg-purple-50 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400' : 'bg-blue-50 text-[#0B5694] dark:bg-blue-900/30 dark:text-blue-400' }} border border-transparent dark:border-current">
                                            {{ $backup->backup_type }}
                                        </span>
                                    </td>
                                    <td class="px-4 sm:px-6 lg:px-8 py-6">
                                        <span class="font-bold text-gray-700 dark:text-gray-300 text-[11px] sm:text-xs tracking-wider uppercase">{{ $backup->file_size ?? 'N/A' }}</span>
                                    </td>
                                    <td class="px-4 sm:px-6 lg:px-8 py-6">
                                        <div class="flex items-center justify-center gap-2">
                                            <span class="w-2 h-2 rounded-full {{ $backup->status === 'success' ? 'bg-emerald-500' : ($backup->status === 'failed' ? 'bg-rose-500' : 'bg-orange-400 animate-pulse') }}"></span>
                                            <span class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider {{ $backup->status === 'success' ? 'text-emerald-600 dark:text-emerald-400' : ($backup->status === 'failed' ? 'text-rose-600 dark:text-rose-400' : 'text-orange-500 dark:text-orange-400') }}">
                                                {{ $backup->status }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-4 sm:px-6 lg:px-8 py-6">
                                        <div class="flex justify-end items-center gap-4">
                                            @if($backup->status === 'success')
                                                <form id="rollback-form-{{ $backup->getRouteKey() }}" action="{{ route('admin.backup.restore', $backup) }}" method="POST">
                                                    @csrf
                                                    <button type="button" 
                                                            @click="confirmRollback('{{ $backup->getRouteKey() }}')"
                                                            class="p-2.5 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 text-gray-400 hover:text-orange-500 rounded-xl transition shadow-sm transform hover:scale-110 cursor-pointer" 
                                                            title="Rollback to this Snapshot">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                                                    </button>
                                                </form>

                                                <a href="{{ route('admin.backup.download', $backup) }}" 
                                                   class="p-2.5 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 text-gray-400 hover:text-[#0B5694] dark:hover:text-blue-400 rounded-xl transition shadow-sm transform hover:scale-110 cursor-pointer"
                                                   title="Download Snapshot">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                                </a>
                                            @endif
                                            <form id="delete-form-{{ $backup->getRouteKey() }}" action="{{ route('admin.backup.destroy', $backup) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" 
                                                        @click="confirmDelete('{{ $backup->getRouteKey() }}')"
                                                        class="p-2.5 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 text-gray-400 hover:text-rose-500 rounded-xl transition shadow-sm transform hover:scale-110 cursor-pointer" 
                                                        title="Delete Record">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-12">
                                        <x-empty-state title="No backup history found" compact="true" />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Mobile Backup Cards --}}
            <div class="md:hidden space-y-4">
                @forelse($backups as $backup)
                    <div class="bg-gray-50 dark:bg-gray-800/50 p-5 rounded-3xl border border-gray-100 dark:border-gray-700">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <span class="font-bold text-gray-800 dark:text-gray-200 text-sm uppercase tracking-tight">{{ $backup->created_at->format('M d, Y') }}</span>
                                <span class="text-[10px] text-gray-400 dark:text-gray-500 font-bold uppercase tracking-wider block mt-0.5">{{ $backup->created_at->format('h:i A') }}</span>
                            </div>
                            <span class="px-3 py-1.5 rounded-full text-[9px] font-bold uppercase tracking-wider {{ $backup->backup_type === 'auto' ? 'bg-purple-50 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400' : 'bg-blue-50 text-[#0B5694] dark:bg-blue-900/30 dark:text-blue-400' }} border border-transparent dark:border-current shrink-0">
                                {{ $backup->backup_type }}
                            </span>
                        </div>

                        @if($backup->sha256_hash)
                            <div class="mb-3 px-3 py-2 bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800">
                                <span class="text-[8px] text-gray-400 dark:text-gray-600 font-mono tracking-tighter select-all" title="SHA-256 Integrity Hash">SHA-256: {{ substr($backup->sha256_hash, 0, 16) }}...</span>
                            </div>
                        @endif

                        <div class="flex items-center justify-between mb-5">
                            <div>
                                <span class="text-[10px] text-gray-400 dark:text-gray-500 font-bold uppercase tracking-wider">Size</span>
                                <p class="font-bold text-gray-700 dark:text-gray-300 text-xs tracking-wider uppercase mt-0.5">{{ $backup->file_size ?? 'N/A' }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full {{ $backup->status === 'success' ? 'bg-emerald-500' : ($backup->status === 'failed' ? 'bg-rose-500' : 'bg-orange-400 animate-pulse') }}"></span>
                                <span class="text-[10px] font-bold uppercase tracking-wider {{ $backup->status === 'success' ? 'text-emerald-600 dark:text-emerald-400' : ($backup->status === 'failed' ? 'text-rose-600 dark:text-rose-400' : 'text-orange-500 dark:text-orange-400') }}">
                                    {{ $backup->status }}
                                </span>
                            </div>
                        </div>

                        <div class="flex gap-3 pt-4 border-t border-gray-200/50 dark:border-gray-700/50">
                            @if($backup->status === 'success')
                                <form id="rollback-form-mobile-{{ $backup->getRouteKey() }}" action="{{ route('admin.backup.restore', $backup) }}" method="POST" class="flex-1">
                                    @csrf
                                    <button type="button" 
                                            @click="confirmRollback('{{ $backup->getRouteKey() }}')"
                                            class="w-full bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 text-orange-500 min-h-[44px] rounded-xl text-[10px] font-bold uppercase tracking-wider transition active:scale-95 flex items-center justify-center gap-2 shadow-sm hover:bg-orange-50 dark:hover:bg-orange-950/20 cursor-pointer">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                                        Rollback
                                    </button>
                                </form>

                                <a href="{{ route('admin.backup.download', $backup) }}" 
                                   class="flex-1 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 text-[#0B5694] dark:text-blue-400 min-h-[44px] rounded-xl text-[10px] font-bold uppercase tracking-wider transition active:scale-95 flex items-center justify-center gap-2 shadow-sm hover:bg-blue-50 dark:hover:bg-blue-950/20 cursor-pointer">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                    Download
                                </a>
                            @endif
                            <form id="delete-form-mobile-{{ $backup->getRouteKey() }}" action="{{ route('admin.backup.destroy', $backup) }}" method="POST" class="flex-1">
                                @csrf
                                @method('DELETE')
                                <button type="button" 
                                        @click="confirmDelete('{{ $backup->getRouteKey() }}')"
                                        class="w-full bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 text-rose-500 min-h-[44px] rounded-xl text-[10px] font-bold uppercase tracking-wider transition active:scale-95 flex items-center justify-center gap-2 shadow-sm hover:bg-rose-50 dark:hover:bg-rose-950/20 cursor-pointer">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <x-empty-state title="No backup history found" compact="true" />
                @endforelse
            </div>

            {{-- Pagination --}}
            <div class="mt-8">
                {{ $backups->links('vendor.pagination.tailwind-custom') }}
            </div>
        </x-table-container>
    </div>

    {{-- MODALS --}}
    <div x-show="showUploadModal || showRollbackModal || showDeleteModal || isUploading || isRestoring" 
         class="fixed inset-0 z-[110] flex items-center justify-center p-4 sm:p-6" 
         x-cloak>
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="if(!isUploading && !isRestoring) { showUploadModal = false; showRollbackModal = false; showDeleteModal = false; }"></div>
        
        {{-- Upload Overlay --}}
        <div x-show="isUploading" class="relative bg-white dark:bg-gray-950 rounded-[35px] w-full max-w-sm p-12 text-center shadow-2xl border border-gray-100 dark:border-gray-800">
            <div class="w-24 h-24 rounded-full border-4 border-gray-100 dark:border-gray-800 border-t-[#0B5694] dark:border-t-blue-500 animate-spin mx-auto"></div>
            <h3 class="mt-10 text-2xl font-black text-gray-800 dark:text-gray-100 uppercase tracking-tight">Uploading</h3>
            <p class="mt-2 text-gray-500 dark:text-gray-400 font-bold uppercase tracking-wider text-[10px] italic">Saving backup snapshot...</p>
        </div>

        {{-- Restore Overlay --}}
        <div x-show="isRestoring" class="relative bg-white dark:bg-gray-950 rounded-[35px] w-full max-w-sm p-12 text-center shadow-2xl border border-gray-100 dark:border-gray-800">
            <div class="w-24 h-24 rounded-full border-4 border-gray-100 dark:border-gray-800 border-t-orange-500 animate-spin mx-auto"></div>
            <h3 class="mt-10 text-2xl font-black text-gray-800 dark:text-gray-100 uppercase tracking-tight">Restoring</h3>
            <p class="mt-2 text-gray-500 dark:text-gray-400 font-bold uppercase tracking-wider text-[10px] italic">Do not close this window...</p>
        </div>

        {{-- Upload Modal --}}
        <div x-show="showUploadModal" class="relative bg-white dark:bg-gray-900 rounded-[35px] w-full max-w-md p-6 sm:p-10 shadow-2xl border border-gray-100 dark:border-gray-800"
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            <form action="{{ route('admin.backup.upload') }}" method="POST" enctype="multipart/form-data" @submit="isUploading = true; showUploadModal = false">
                @csrf
                <div class="w-12 sm:w-16 h-12 sm:h-16 bg-blue-50 dark:bg-blue-900/20 rounded-2xl flex items-center justify-center mb-6 sm:mb-8 mx-auto text-[#0B5694] dark:text-blue-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                </div>
                <h3 class="text-xl sm:text-2xl font-extrabold text-gray-800 dark:text-gray-100 text-center uppercase tracking-normal">Upload Snapshot</h3>
                <p class="mt-4 text-gray-400 dark:text-gray-500 text-center leading-relaxed text-xs font-bold uppercase tracking-wider italic">Select a SmarTern backup .zip file</p>

                <div class="mt-10 group relative">
                    <input type="file" name="backup_zip" accept=".zip" required
                           class="block w-full text-xs text-gray-500 dark:text-gray-400
                                  file:mr-4 file:py-3 file:px-6
                                  file:rounded-2xl file:border-0
                                  file:text-[10px] file:font-black
                                  file:bg-[#0B5694] file:text-white
                                  file:uppercase file:tracking-wider
                                  hover:file:bg-[#084577]
                                  cursor-pointer border border-dashed border-gray-200 dark:border-gray-700 rounded-3xl p-4 transition-all hover:border-[#0B5694]" />
                </div>
                <p class="mt-4 text-[9px] text-gray-400 font-bold uppercase tracking-wider text-center">Limit: 50MB</p>

                <div class="mt-10 flex flex-col gap-4">
                    <button type="submit" class="w-full bg-[#0B5694] dark:bg-blue-600 text-white min-h-[44px] rounded-2xl font-bold text-xs uppercase tracking-wider shadow-lg shadow-blue-500/20 transition-all hover:scale-[1.02] active:scale-[0.98] cursor-pointer">
                        Confirm Upload
                    </button>
                    <button type="button" @click="showUploadModal = false" class="w-full bg-gray-50 dark:bg-gray-800 text-gray-400 min-h-[44px] rounded-2xl font-bold text-xs uppercase tracking-wider hover:bg-gray-100 transition-all cursor-pointer">
                        Cancel
                    </button>
                </div>
            </form>
        </div>

        {{-- Rollback Modal --}}
        <div x-show="showRollbackModal" class="relative bg-white dark:bg-gray-900 rounded-[35px] w-full max-w-md p-6 sm:p-10 shadow-2xl border border-gray-100 dark:border-gray-800"
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            <div class="w-12 sm:w-16 h-12 sm:h-16 bg-orange-50 dark:bg-orange-950/20 rounded-2xl flex items-center justify-center mb-6 sm:mb-8 mx-auto">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#f97316" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
            </div>
            <h3 class="text-xl sm:text-2xl font-extrabold text-gray-800 dark:text-gray-100 text-center uppercase tracking-normal">Confirm Rollback</h3>
            <div class="mt-6 bg-orange-50 dark:bg-orange-950/20 p-5 rounded-2xl border border-orange-100 dark:border-orange-900/30 text-left">
                <p class="text-xs text-orange-700 dark:text-orange-400 font-bold uppercase tracking-wider leading-relaxed">
                    This will overwrite the current system state. All active sessions will be terminated.
                </p>
            </div>

            <div class="mt-10 flex flex-col gap-4">
                <button @click="triggerRestore" class="w-full bg-orange-500 hover:bg-orange-600 text-white min-h-[44px] rounded-2xl font-bold text-xs uppercase tracking-wider shadow-lg shadow-orange-500/20 transition-all active:scale-[0.98] cursor-pointer">
                    Yes, Rollback System
                </button>
                <button @click="showRollbackModal = false" class="w-full bg-gray-50 dark:bg-gray-800 text-gray-400 min-h-[44px] rounded-2xl font-bold text-xs uppercase tracking-wider hover:bg-gray-100 transition-all cursor-pointer">
                    Cancel
                </button>
            </div>
        </div>

        {{-- Delete Modal --}}
        <div x-show="showDeleteModal" class="relative bg-white dark:bg-gray-900 rounded-[35px] w-full max-w-md p-6 sm:p-10 shadow-2xl border border-gray-100 dark:border-gray-800"
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            <div class="w-12 sm:w-16 h-12 sm:h-16 bg-rose-50 dark:bg-rose-950/20 rounded-2xl flex items-center justify-center mb-6 sm:mb-8 mx-auto text-rose-500">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
            </div>
            <h3 class="text-xl sm:text-2xl font-extrabold text-gray-800 dark:text-gray-100 text-center uppercase tracking-normal">Delete Backup</h3>
            <p class="mt-4 text-gray-400 dark:text-gray-500 text-center text-xs font-bold uppercase tracking-wider italic">This action is irreversible.</p>

            <div class="mt-10 flex flex-col gap-4">
                <button @click="triggerDelete" class="w-full bg-rose-500 hover:bg-rose-600 text-white min-h-[44px] rounded-2xl font-bold text-xs uppercase tracking-wider shadow-lg shadow-rose-500/20 transition-all active:scale-[0.98] cursor-pointer">
                    Delete Permanently
                </button>
                <button @click="showDeleteModal = false" class="w-full bg-gray-50 dark:bg-gray-800 text-gray-400 min-h-[44px] rounded-2xl font-bold text-xs uppercase tracking-wider hover:bg-gray-100 transition-all cursor-pointer">
                    Cancel
                </button>
            </div>
        </div>
    </div>
    </div>
</x-page-layout>
@endsection
