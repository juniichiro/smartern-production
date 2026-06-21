@extends('layouts.app')

@section('title', 'Certificate Endorsement')

@section('content')
<x-page-layout 
    portalType="Supervisor" 
    userName="{{ Auth::user()->supervisorDetails->first_name }}" 
    userRole="{{ Auth::user()->supervisorDetails->office->name }}"
>
    <div class="flex flex-col h-full" x-data="supervisorCertificatesManager({{ session('success') ? 'true' : 'false' }})">
        <x-page-header 
            title="Endorsements"
            subtitle="Verify and endorse interns who have completed their internship hours"
        />

        <div class="bg-white dark:bg-gray-900 rounded-[25px] shadow-sm border border-gray-100 dark:border-gray-800 p-6 md:p-8">
            <h3 class="text-lg md:text-xl font-bold text-gray-800 dark:text-gray-100 mb-8 uppercase">Office Interns</h3>

            <div class="overflow-hidden border border-gray-100 dark:border-gray-800 rounded-2xl">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 dark:bg-gray-800/50 text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest border-b border-gray-100 dark:border-gray-700">
                            <tr>
                                <th class="px-8 py-5">Intern Name</th>
                                <th class="px-8 py-5">Rendered Hours</th>
                                <th class="px-8 py-5">Certificate Status</th>
                                <th class="px-8 py-5 text-right">Actions</th>
                            </tr>
                        </thead>
                        {{-- Skeletons --}}
                        <tbody x-show="loading" class="divide-y divide-gray-50 dark:divide-gray-800">
                            @for($i = 0; $i < min(count($interns) ?: 3, 3); $i++)
                                <tr>
                                    <td class="px-8 py-6">
                                        <div class="space-y-2">
                                            <x-skeleton class="h-4 w-32 rounded-lg" />
                                            <x-skeleton class="h-3 w-24 rounded-lg" />
                                        </div>
                                    </td>
                                    <td class="px-8 py-6"><x-skeleton class="h-4 w-20 rounded-lg" /></td>
                                    <td class="px-8 py-6"><x-skeleton class="h-6 w-24 rounded-full" /></td>
                                    <td class="px-8 py-6 flex justify-end gap-2">
                                        <x-skeleton class="h-10 w-28 rounded-xl" />
                                        <x-skeleton class="h-10 w-24 rounded-xl" />
                                    </td>
                                </tr>
                            @endfor
                        </tbody>

                        <tbody x-show="!loading" x-transition.opacity.duration.500ms class="divide-y divide-gray-50 dark:divide-gray-800">
                            @forelse($interns as $intern)
                                @php 
                                    $cert = $intern->certificate;
                                    $isEligible = ($intern->hours_rendered ?? 0) >= 480; 
                                @endphp
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/20 transition-colors">
                                    <td class="px-8 py-6 whitespace-nowrap">
                                        <p class="font-bold text-gray-800 dark:text-gray-100">{{ $intern->first_name }} {{ $intern->last_name }}</p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500">{{ $intern->user->email ?? 'No Email' }}</p>
                                    </td>
                                    <td class="px-8 py-6 font-black text-gray-600 dark:text-gray-400 whitespace-nowrap">
                                        <span class="{{ $isEligible ? 'text-emerald-500 dark:text-emerald-400' : 'text-[#0B5694] dark:text-blue-400' }}">
                                            {{ (int)($intern->hours_rendered ?? 0) }} / 480
                                        </span>
                                    </td>
                                    <td class="px-8 py-6 whitespace-nowrap">
                                        @if(!$cert)
                                            <span class="text-gray-300 dark:text-gray-600 text-[10px] font-black uppercase tracking-widest">No Record</span>
                                        @else
                                            <span class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border shadow-sm
                                                {{ $cert->status === 'pending' ? 'bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 border-amber-100 dark:border-amber-900/30' : '' }}
                                                {{ $cert->status === 'published' ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 border-emerald-100 dark:border-emerald-900/30' : '' }}">
                                                {{ $cert->status }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-8 py-6 text-right whitespace-nowrap">
                                        @if($isEligible)
                                            <div class="flex items-center justify-end gap-2">
                                                <a href="{{ route('supervisor.certificates.preview', ['certificate' => $intern->user_id]) }}" 
                                                   class="bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 px-4 py-2.5 rounded-xl text-xs font-bold hover:bg-gray-50 dark:hover:bg-gray-700 transition uppercase tracking-widest"
                                                   title="Preview Draft">
                                                    Preview Draft
                                                </a>
                                                @if(!$cert)
                                                    <form action="{{ route('supervisor.certificates.endorse', ['certificate' => $intern->user_id]) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" 
                                                                class="bg-[#0B5694] dark:bg-blue-600 text-white px-5 py-2.5 rounded-xl text-xs font-bold hover:bg-[#084577] dark:hover:bg-blue-700 transition shadow-lg shadow-blue-500/20 uppercase tracking-widest active:scale-95">
                                                            Endorse
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="text-[10px] font-black text-emerald-500 dark:text-emerald-400 uppercase tracking-widest flex items-center justify-end gap-2 px-2">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                                        In Queue
                                                    </span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-[10px] font-black text-gray-300 dark:text-gray-600 uppercase tracking-widest">
                                                {{ 480 - (int)($intern->hours_rendered ?? 0) }} Hours Left
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-8 py-12 text-center text-gray-400 dark:text-gray-500 italic">No interns currently assigned to your office.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-page-layout>
@endsection