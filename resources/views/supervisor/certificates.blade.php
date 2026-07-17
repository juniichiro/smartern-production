@extends('layouts.app')

@section('title', 'Certificate Endorsement')

@section('content')
<x-page-layout 
    portalType="Supervisor" 
    userName="{{ Auth::user()->supervisorDetails->first_name }}" 
    userRole="{{ Auth::user()->supervisorDetails->office->name }}"
>
    <div class="flex flex-col h-full" x-data="supervisorCertificatesManager()">
        <x-page-header 
            title="Endorsements"
            subtitle="Verify and endorse interns who have completed their internship hours"
        />

        <div class="bg-white dark:bg-gray-900 rounded-[25px] shadow-sm border border-gray-100 dark:border-gray-800 p-4 md:p-8">
            <h3 class="text-base sm:text-lg md:text-xl font-bold text-gray-800 dark:text-gray-100 mb-8 uppercase">Office Interns</h3>

            {{-- Desktop: Table (lg+) --}}
            <div class="hidden lg:block overflow-hidden border border-gray-100 dark:border-gray-800 rounded-2xl">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 dark:bg-gray-800/50 text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest border-b border-gray-100 dark:border-gray-700">
                            <tr>
                                <th class="px-4 md:px-8 py-3 md:py-5">Intern Name</th>
                                <th class="px-4 md:px-8 py-3 md:py-5">Rendered Hours</th>
                                <th class="px-4 md:px-8 py-3 md:py-5">Certificate Status</th>
                                <th class="px-4 md:px-8 py-3 md:py-5 text-right">Actions</th>
                            </tr>
                        </thead>
                        {{-- Skeletons --}}
                        <tbody x-show="loading" class="divide-y divide-gray-50 dark:divide-gray-800">
                            @for($i = 0; $i < min(count($interns) ?: 3, 3); $i++)
                                <tr>
                                    <td class="px-4 md:px-8 py-4 md:py-6">
                                        <div class="space-y-2">
                                            <x-skeleton class="h-4 w-32 rounded-lg" />
                                            <x-skeleton class="h-3 w-24 rounded-lg" />
                                        </div>
                                    </td>
                                    <td class="px-4 md:px-8 py-4 md:py-6"><x-skeleton class="h-4 w-20 rounded-lg" /></td>
                                    <td class="px-4 md:px-8 py-4 md:py-6"><x-skeleton class="h-6 w-24 rounded-full" /></td>
                                    <td class="px-4 md:px-8 py-4 md:py-6 flex justify-end gap-2">
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
                                    $targetHours = $intern->required_hours ?? 480;
                                    $isEligible = ($intern->hours_rendered ?? 0) >= $targetHours; 
                                @endphp
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/20 transition-colors">
                                    <td class="px-4 md:px-8 py-4 md:py-6 whitespace-nowrap">
                                        <p class="font-bold text-gray-800 dark:text-gray-100">{{ $intern->first_name }} {{ $intern->last_name }}</p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500">{{ $intern->user->email ?? 'No Email' }}</p>
                                    </td>
                                    <td class="px-4 md:px-8 py-4 md:py-6 font-black text-gray-600 dark:text-gray-400 whitespace-nowrap">
                                        <span class="{{ $isEligible ? 'text-emerald-500 dark:text-emerald-400' : 'text-[#0B5694] dark:text-blue-400' }}">
                                            {{ (int)($intern->hours_rendered ?? 0) }} / {{ $targetHours }}
                                        </span>
                                    </td>
                                    <td class="px-4 md:px-8 py-4 md:py-6 whitespace-nowrap">
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
                                    <td class="px-4 md:px-8 py-4 md:py-6 text-right whitespace-nowrap">
                                        @if($isEligible)
                                            <div class="flex items-center justify-end gap-2">
                                                <a href="{{ route('supervisor.certificates.preview', ['certificate' => $intern->user_id]) }}" 
                                                   class="bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 px-3 md:px-4 py-2 md:py-2.5 rounded-xl text-xs font-bold hover:bg-gray-50 dark:hover:bg-gray-700 transition uppercase tracking-widest"
                                                   title="Preview Draft">
                                                    Preview Draft
                                                </a>
                                                @if(!$cert)
                                                    <button type="button" 
                                                            @click="openFeedbackModal('{{ $intern->first_name }} {{ $intern->last_name }}', '{{ route('supervisor.certificates.endorse', ['certificate' => $intern->user_id]) }}')"
                                                            class="bg-[#0B5694] dark:bg-blue-600 text-white px-4 md:px-5 py-2 md:py-2.5 rounded-xl text-xs font-bold hover:bg-[#084577] dark:hover:bg-blue-700 transition shadow-lg shadow-blue-500/20 uppercase tracking-widest active:scale-95">
                                                        Endorse
                                                    </button>
                                                @else
                                                    @if($cert->status === 'published')
                                                        <span class="text-[10px] font-black text-blue-500 dark:text-blue-400 uppercase tracking-widest flex items-center justify-end gap-2 px-2">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                                            Published
                                                        </span>
                                                    @else
                                                        <span class="text-[10px] font-black text-amber-500 dark:text-amber-400 uppercase tracking-widest flex items-center justify-end gap-2 px-2">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                                            Processing
                                                        </span>
                                                    @endif
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-[10px] font-black text-gray-300 dark:text-gray-600 uppercase tracking-widest">
                                                {{ $targetHours - (int)($intern->hours_rendered ?? 0) }} Hours Left
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

            {{-- Mobile & Tablet: Card Layout (< lg) --}}
            <div class="lg:hidden space-y-3">
                {{-- Skeleton Cards --}}
                <div x-show="loading" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @for($i = 0; $i < min(count($interns) ?: 4, 4); $i++)
                        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 p-4 space-y-3">
                            <div class="flex items-start gap-3">
                                <x-skeleton class="w-10 h-10 rounded-full shrink-0" />
                                <div class="flex-1 space-y-2">
                                    <x-skeleton class="h-4 w-3/4 rounded-lg" />
                                    <x-skeleton class="h-3 w-1/2 rounded-lg" />
                                </div>
                            </div>
                            <x-skeleton class="h-4 w-2/3 rounded-lg" />
                            <div class="flex gap-2 pt-1">
                                <x-skeleton class="h-10 flex-1 rounded-xl" />
                                <x-skeleton class="h-10 flex-1 rounded-xl" />
                            </div>
                        </div>
                    @endfor
                </div>

                {{-- Actual Cards --}}
                <div x-show="!loading" x-transition.opacity.duration.500ms class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @forelse($interns as $intern)
                        @php 
                            $cert = $intern->certificate;
                            $targetHours = $intern->required_hours ?? 480;
                            $isEligible = ($intern->hours_rendered ?? 0) >= $targetHours; 
                        @endphp
                        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 p-4 shadow-sm flex flex-col gap-3">
                            <div class="flex items-start gap-3">
                                <x-avatar :user="$intern" size="10" border="1" circular="true" textSize="xs" />
                                <div class="min-w-0 flex-1">
                                    <p class="font-black text-gray-800 dark:text-gray-100 text-sm uppercase truncate leading-tight">{{ $intern->first_name }} {{ $intern->last_name }}</p>
                                    <p class="text-[10px] text-gray-400 dark:text-gray-500 font-bold uppercase tracking-wider truncate mt-0.5">{{ $intern->user->email ?? 'No Email' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center justify-between py-2 border-t border-gray-50 dark:border-gray-800">
                                <span class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Hours</span>
                                <span class="font-black text-sm {{ $isEligible ? 'text-emerald-500 dark:text-emerald-400' : 'text-[#0B5694] dark:text-blue-400' }}">
                                    {{ (int)($intern->hours_rendered ?? 0) }} / {{ $targetHours }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                @if(!$cert)
                                    <span class="text-[10px] text-gray-300 dark:text-gray-600 font-black uppercase tracking-widest">No Record</span>
                                @else
                                    <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border shadow-sm
                                        {{ $cert->status === 'pending' ? 'bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 border-amber-100 dark:border-amber-900/30' : '' }}
                                        {{ $cert->status === 'published' ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 border-emerald-100 dark:border-emerald-900/30' : '' }}">
                                        {{ $cert->status }}
                                    </span>
                                @endif
                            </div>
                            @if($isEligible)
                                <div class="flex gap-2 pt-1 border-t border-gray-50 dark:border-gray-800">
                                    <a href="{{ route('supervisor.certificates.preview', ['certificate' => $intern->user_id]) }}" 
                                       class="flex-1 flex items-center justify-center gap-1.5 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-700 transition active:scale-[0.97] min-h-[44px]">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                        Preview
                                    </a>
                                    @if(!$cert)
                                        <button type="button" 
                                                @click="openFeedbackModal('{{ $intern->first_name }} {{ $intern->last_name }}', '{{ route('supervisor.certificates.endorse', ['certificate' => $intern->user_id]) }}')"
                                                class="flex-1 flex items-center justify-center gap-1.5 py-2.5 bg-[#0B5694] dark:bg-blue-600 text-white rounded-xl text-[9px] font-black uppercase tracking-widest shadow-sm hover:bg-[#084577] dark:hover:bg-blue-700 transition active:scale-[0.97] min-h-[44px]">
                                            Endorse
                                        </button>
                                    @else
                                        @if($cert->status === 'published')
                                            <span class="flex-1 flex items-center justify-center gap-1.5 py-2.5 text-[10px] font-black text-blue-500 dark:text-blue-400 uppercase tracking-widest">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                                                Published
                                            </span>
                                        @else
                                            <span class="flex-1 flex items-center justify-center gap-1.5 py-2.5 text-[10px] font-black text-amber-500 dark:text-amber-400 uppercase tracking-widest">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                                Processing
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            @else
                                <div class="pt-1 border-t border-gray-50 dark:border-gray-800">
                                    <span class="text-[10px] font-black text-gray-300 dark:text-gray-600 uppercase tracking-widest block text-center py-2.5">
                                        {{ $targetHours - (int)($intern->hours_rendered ?? 0) }} Hours Left
                                    </span>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="col-span-full py-12 text-center text-gray-400 dark:text-gray-500 italic bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800">
                            No interns currently assigned to your office.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Blind Supervisor Feedback Modal --}}
        <div x-show="showFeedbackModal" 
             class="fixed inset-0 z-[100] flex items-center justify-center p-4 overflow-y-auto"
             x-cloak>
            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-gray-900/60 dark:bg-black/70 backdrop-blur-md transition-opacity" 
                 @click="closeFeedbackModal"
                 x-show="showFeedbackModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"></div>

            {{-- Modal Content Card --}}
            <div class="relative bg-white dark:bg-gray-900 rounded-[28px] border border-gray-100 dark:border-gray-800 shadow-2xl p-5 sm:p-8 max-w-lg w-full z-10 text-left transform transition-all"
                 x-show="showFeedbackModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                
                {{-- Modal Header --}}
                <div class="flex items-start gap-4 mb-6">
                    <div class="p-2 sm:p-3 bg-blue-50 dark:bg-blue-900/20 text-[#0B5694] dark:text-blue-400 rounded-2xl">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100 Outfit font-extrabold tracking-tight">Supervisor Feedback</h3>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1 uppercase tracking-wider font-bold">Confidential Evaluation</p>
                    </div>
                </div>

                {{-- Form wrapper --}}
                <form :action="endorseUrl" method="POST">
                    @csrf
                    
                    <div class="space-y-4 mb-6">
                        <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed font-medium">
                            Provide qualitative feedback and evaluation for <span class="font-extrabold text-gray-700 dark:text-gray-300 x-text" x-text="selectedInternName"></span> to review during the endorsement process. This feedback is completely confidential and will not be displayed to the intern.
                        </p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach([
                                'technical_proficiency' => 'Technical Proficiency',
                                'professionalism_ethics' => 'Professionalism & Ethics',
                                'communication_skills' => 'Communication Skills',
                                'team_collaboration' => 'Team Collaboration'
                            ] as $key => $label)
                                <div class="space-y-2">
                                    <label for="{{ $key }}" class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">{{ $label }}</label>
                                    <select id="{{ $key }}" 
                                            name="{{ $key }}" 
                                            required
                                            class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl p-3 text-xs text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-[#0B5694] dark:focus:ring-blue-600 focus:border-transparent font-medium">
                                        <option value="Excellent">Excellent</option>
                                        <option value="Very Good">Very Good</option>
                                        <option value="Good" selected>Good</option>
                                        <option value="Fair">Fair</option>
                                        <option value="Poor">Poor</option>
                                    </select>
                                </div>
                            @endforeach
                        </div>

                        <div class="space-y-2">
                            <label for="supervisor_feedback" class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">Feedback & Notes</label>
                            <textarea id="supervisor_feedback" 
                                      name="supervisor_feedback" 
                                      rows="4" 
                                      class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl p-4 text-xs text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-[#0B5694] dark:focus:ring-blue-600 focus:border-transparent placeholder-gray-400 resize-none font-medium"
                                      placeholder="Write your feedback regarding the intern's work behavior, competency, attendance, and overall performance..."></textarea>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center justify-end gap-3">
                        <button type="button" 
                                @click="closeFeedbackModal"
                                class="bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 px-4 sm:px-5 py-2.5 sm:py-3 rounded-xl text-xs font-bold transition uppercase tracking-widest">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="bg-[#0B5694] dark:bg-blue-600 hover:bg-[#084577] dark:hover:bg-blue-700 text-white px-6 py-3 rounded-xl text-xs font-bold transition shadow-lg shadow-blue-500/20 uppercase tracking-widest active:scale-95">
                            Endorse Intern
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-page-layout>
@endsection