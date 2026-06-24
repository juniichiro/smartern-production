@extends('layouts.app')

@section('title', 'Certificates')

@section('content')
<x-page-layout 
    portalType="Head Supervisor" 
    userName="{{ Auth::user()->supervisorDetails->first_name }}" 
    userRole="{{ Auth::user()->supervisorDetails->office->name }}"
>
    <div x-data="headSupervisorCertificatesManager" class="flex flex-col h-full">
        {{-- Header --}}
        <x-page-header 
            title="Credential Registry" 
            subtitle="Review, verify, and publish digital completion credentials" 
        />

        {{-- Summary Stats --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 mb-10">
            <x-stat-card 
                title="Pending Review" 
                :value="number_format($stats['pending'] ?? 0)" 
                subtitle="Awaiting verification"
                color="white"
                icon='<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 8v4l3 2"/><circle cx="12" cy="12" r="10"/></svg>'
            >
                <x-slot name="value">
                    <span class="text-orange-500">{{ number_format($stats['pending'] ?? 0) }}</span>
                </x-slot>
            </x-stat-card>

            <x-stat-card 
                title="Published" 
                :value="number_format($stats['published'] ?? 0)" 
                subtitle="Issued to interns"
                color="white"
                icon='<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>'
            >
                <x-slot name="value">
                    <span class="text-emerald-500">{{ number_format($stats['published'] ?? 0) }}</span>
                </x-slot>
            </x-stat-card>
        </div>

        {{-- Certification Management --}}
        <x-table-container title="Fulfillment Dispatch">
            {{-- DESKTOP TABLE (lg+) --}}
            <div class="hidden lg:block border border-gray-100 dark:border-gray-800 rounded-2xl sm:rounded-3xl overflow-hidden shadow-inner">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 dark:bg-gray-800/50 text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest border-b border-gray-100 dark:border-gray-800">
                            <tr>
                                <th class="px-8 py-5">Intern Information</th>
                                <th class="px-8 py-5 text-center">Rendering Metrics</th>
                                <th class="px-8 py-5 text-center">Fulfillment Status</th>
                                <th class="px-8 py-5 text-right">Dispatch Actions</th>
                            </tr>
                        </thead>
                        <tbody x-show="loading" class="divide-y divide-gray-50 dark:divide-gray-800">
                            @for($i = 0; $i < min(count($certificates) ?: 5, 5); $i++)
                                <tr>
                                    <td class="px-8 py-6">
                                        <div class="flex items-center gap-4">
                                            <x-skeleton class="w-10 h-10 rounded-full shrink-0" />
                                            <div class="space-y-2">
                                                <x-skeleton class="h-4 w-32 rounded-lg" />
                                                <x-skeleton class="h-3 w-24 rounded-lg" />
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6 text-center"><x-skeleton class="h-4 w-20 mx-auto rounded-lg" /></td>
                                    <td class="px-8 py-6 text-center"><x-skeleton class="h-6 w-24 mx-auto rounded-full" /></td>
                                    <td class="px-8 py-6 text-right flex justify-end gap-2">
                                        <x-skeleton class="h-10 w-10 rounded-xl" />
                                        <x-skeleton class="h-10 w-20 rounded-xl" />
                                    </td>
                                </tr>
                            @endfor
                        </tbody>

                        <tbody x-show="!loading" x-transition.opacity.duration.500ms class="text-sm divide-y divide-gray-50 dark:divide-gray-800">
                            @forelse($certificates as $cert)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition group">
                                <td class="px-8 py-6 whitespace-nowrap">
                                    <div class="flex items-center gap-4">
                                        <x-avatar :user="$cert->intern" size="10" border="1" circular="true" textSize="xs" />
                                        <div>
                                            <p class="text-sm font-black text-gray-800 dark:text-gray-100 uppercase tracking-tight group-hover:text-[#0B5694] transition-colors">
                                                {{ $cert->intern->first_name ?? 'N/A' }} {{ $cert->intern->last_name ?? '' }}
                                            </p>
                                            <p class="text-[11px] text-gray-400 dark:text-gray-500 font-bold uppercase tracking-widest mt-0.5 leading-none">{{ $cert->intern->user->email ?? 'No email' }}</p>
                                            @if($cert->supervisor_feedback)
                                                <div class="mt-2 flex items-start gap-1.5 p-2.5 bg-gray-50 dark:bg-gray-800/40 rounded-xl max-w-[280px] border border-gray-100 dark:border-gray-800 whitespace-normal">
                                                    <span class="text-[#0B5694] dark:text-blue-400 shrink-0 mt-0.5">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                                                    </span>
                                                    <p class="text-[10px] text-gray-500 dark:text-gray-400 font-medium italic leading-normal line-clamp-2" title="{{ $cert->supervisor_feedback }}">"{{ $cert->supervisor_feedback }}"</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-center whitespace-nowrap">
                                    <span class="font-black text-gray-700 dark:text-gray-300 text-[12px] tracking-widest uppercase leading-none">{{ (int)($cert->completed_hours ?? 0) }} / {{ $cert->intern->required_hours ?? 480 }} HRS</span>
                                </td>
                                <td class="px-8 py-6 text-center whitespace-nowrap">
                                    @php $statusColors = ['pending' => 'bg-amber-50 dark:bg-amber-950/30 text-orange-600 dark:text-orange-400 border-orange-100 dark:border-orange-900/50', 'published' => 'bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 border-emerald-100 dark:border-emerald-900/50']; @endphp
                                    <span class="{{ $statusColors[$cert->status] ?? 'bg-gray-100 dark:bg-gray-800' }} text-[10px] font-black px-4 py-1.5 rounded-full uppercase tracking-widest border shadow-sm">{{ $cert->status ?? $cert->verification_status }}</span>
                                </td>
                                <td class="px-8 py-6 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-3">
                                        @if(!empty($cert->certificate_id))
                                            <a href="{{ route('head-supervisor.certificates.show', ['certificate' => $cert]) }}" class="p-2.5 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 text-gray-400 hover:text-[#0B5694] dark:hover:text-blue-400 rounded-xl transition shadow-sm transform hover:scale-110 active:scale-95 flex items-center justify-center">
                                               <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                            </a>
                                            @if($cert->verification_status === 'pending')
                                                <form action="{{ route('head-supervisor.certificates.publish', ['certificate' => $cert]) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="bg-[#0B5694] dark:bg-blue-600 text-white px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg hover:bg-[#084577] transition-all transform hover:scale-105 active:scale-95">Publish</button>
                                                </form>
                                            @elseif($cert->status === 'published')
                                                <a href="{{ route('head-supervisor.certificates.download', ['certificate' => $cert]) }}" class="bg-gray-800 dark:bg-gray-700 text-white p-2.5 rounded-xl shadow-lg flex items-center justify-center hover:bg-black transition-all transform hover:scale-110 active:scale-95" title="Download Record">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                                </a>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="py-20 text-center">
                                    <x-empty-state title="No certificates identified in records" compact="true" />
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- MOBILE & TABLET CARD LAYOUT (< lg) --}}
            <div class="lg:hidden grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                @forelse($certificates as $cert)
                <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden">
                    <div class="p-4">
                        <div class="flex items-start gap-3 mb-3">
                            <x-avatar :user="$cert->intern" size="10" border="1" circular="true" textSize="xs" class="shrink-0" />
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-black text-gray-800 dark:text-gray-100 uppercase tracking-tight truncate">{{ $cert->intern->first_name ?? 'N/A' }} {{ $cert->intern->last_name ?? '' }}</p>
                                <p class="text-[10px] text-gray-400 dark:text-gray-500 font-bold uppercase tracking-wider truncate">{{ $cert->intern->user->email ?? 'No email' }}</p>
                            </div>
                            @php $statusColors = ['pending' => 'bg-amber-50 dark:bg-amber-950/30 text-orange-600 dark:text-orange-400 border-orange-100 dark:border-orange-900/50', 'published' => 'bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 border-emerald-100 dark:border-emerald-900/50']; @endphp
                            <span class="shrink-0 {{ $statusColors[$cert->status] ?? 'bg-gray-100 dark:bg-gray-800 text-gray-500' }} text-[9px] font-black px-2.5 py-1 rounded-full uppercase tracking-widest border shadow-sm">{{ $cert->status ?? $cert->verification_status }}</span>
                        </div>
                        @if($cert->supervisor_feedback)
                            <div class="mb-3 flex items-start gap-1.5 p-2.5 bg-gray-50 dark:bg-gray-800/40 rounded-xl border border-gray-100 dark:border-gray-800">
                                <span class="text-[#0B5694] dark:text-blue-400 shrink-0 mt-0.5"><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg></span>
                                <p class="text-[10px] text-gray-500 dark:text-gray-400 font-medium italic leading-normal line-clamp-2">"{{ $cert->supervisor_feedback }}"</p>
                            </div>
                        @endif
                        <div class="flex items-center justify-between py-2 border-t border-gray-50 dark:border-gray-800">
                            <span class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Hours</span>
                            <span class="text-xs font-black text-gray-700 dark:text-gray-300">{{ (int)($cert->completed_hours ?? 0) }} / {{ $cert->intern->required_hours ?? 480 }} HRS</span>
                        </div>
                        @if(!empty($cert->certificate_id))
                        <div class="flex gap-2 mt-3 pt-3 border-t border-gray-50 dark:border-gray-800">
                            <a href="{{ route('head-supervisor.certificates.show', ['certificate' => $cert]) }}" class="flex-1 flex items-center justify-center gap-1.5 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-[10px] font-black text-gray-600 dark:text-gray-300 uppercase tracking-wider active:bg-gray-50 dark:active:bg-gray-700 transition active:scale-[0.97] min-h-[44px]">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                Preview
                            </a>
                            @if($cert->verification_status === 'pending')
                                <form action="{{ route('head-supervisor.certificates.publish', ['certificate' => $cert]) }}" method="POST" class="flex-1">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center justify-center gap-1.5 py-2.5 bg-[#0B5694] dark:bg-blue-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-sm active:bg-[#084577] transition active:scale-[0.97] min-h-[44px]">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22h6a2 2 0 0 0 2-2V7l-5-5H6a2 2 0 0 0-2 2v3"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M5 17l-3-3 3-3"/><path d="M2 14h9"/></svg>
                                        Publish
                                    </button>
                                </form>
                            @elseif($cert->status === 'published')
                                <a href="{{ route('head-supervisor.certificates.download', ['certificate' => $cert]) }}" class="flex-1 flex items-center justify-center gap-1.5 py-2.5 bg-gray-800 dark:bg-gray-700 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-sm active:bg-black transition active:scale-[0.97] min-h-[44px]">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                    Download
                                </a>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
                @empty
                <div class="col-span-full py-16 text-center">
                    <x-empty-state title="No certificates identified in records" compact="true" />
                </div>
                @endforelse
            </div>

            @if($certificates->hasPages())
                <div class="mt-8 flex justify-end">
                    {{ $certificates->links('vendor.pagination.tailwind-custom') }}
                </div>
            @endif
        </x-table-container>
    </div>
</x-page-layout>
@endsection
