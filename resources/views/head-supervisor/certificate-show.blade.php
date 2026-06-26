@extends('layouts.app')

@section('title', 'Certificate Preview')

@section('content')
<x-page-layout 
    portalType="Head Supervisor" 
    userName="{{ Auth::user()->supervisorDetails->first_name }}" 
    userRole="{{ Auth::user()->supervisorDetails->office->name }}"
>
    <div x-data="headSupervisorCertificatePreviewManager" class="flex flex-col h-full">
        {{-- Header & Back Navigation --}}
        <header class="mb-6 sm:mb-8 flex flex-col md:flex-row justify-between items-start md:items-end gap-4 sm:gap-6">
            <div>
                <a href="{{ route('head-supervisor.certificates.index') }}" class="flex items-center gap-2 text-gray-400 hover:text-[#0B5694] dark:hover:text-blue-400 transition font-bold uppercase tracking-widest text-xs mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    Back to Registry
                </a>
                <h2 class="text-3xl md:text-4xl font-black text-gray-800 dark:text-gray-100 tracking-tight">Artifact Preview</h2>
                <p class="text-sm md:text-base text-gray-400 dark:text-gray-500 mt-2 font-medium tracking-tight">Review the exact document output before finalizing</p>
            </div>

            {{-- Action Buttons & Edit Modal Context --}}
            <div x-data="{ openEditModal: false }" class="flex gap-3 w-full md:w-auto items-center">
                {{-- Edit Button --}}
                <button @click="openEditModal = true" type="button" class="w-full md:w-auto bg-amber-500 hover:bg-amber-600 text-white px-6 py-4 rounded-2xl text-xs font-black shadow-lg hover:shadow-amber-500/20 transition-all uppercase tracking-widest flex items-center justify-center gap-2 active:scale-95 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                    Correct Details
                </button>

                @if($certificate->status === 'pending')
                    <form action="{{ route('head-supervisor.certificates.publish', ['certificate' => $certificate]) }}" method="POST" class="w-full md:w-auto">
                        @csrf
                        <button type="submit" class="w-full md:w-auto bg-[#0B5694] dark:bg-blue-600 text-white px-8 py-4 rounded-2xl text-xs font-black shadow-xl shadow-blue-500/20 hover:bg-[#084577] dark:hover:bg-blue-700 transition-all uppercase tracking-widest flex items-center justify-center gap-3 active:scale-95">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22h6a2 2 0 0 0 2-2V7l-5-5H6a2 2 0 0 0-2 2v3"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M5 17l-3-3 3-3"/><path d="M2 14h9"/></svg>
                            Finalize & Dispatch
                        </button>
                    </form>
                @elseif($certificate->status === 'published')
                    <a href="{{ route('head-supervisor.certificates.download', ['certificate' => $certificate]) }}" 
                       class="w-full md:w-auto bg-gray-900 dark:bg-gray-800 text-white px-8 py-4 rounded-2xl text-xs font-black shadow-lg hover:bg-black dark:hover:bg-gray-700 transition-all uppercase tracking-widest flex items-center justify-center gap-3 active:scale-95">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Export Artifact
                    </a>
                @endif

                {{-- Edit Details Modal --}}
                <div x-show="openEditModal" 
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
                     x-cloak>
                    
                    <div x-show="openEditModal"
                         x-transition:enter="transition ease-out duration-300 transform"
                         x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-200 transform"
                         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                         x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                         @click.away="openEditModal = false"
                         class="bg-white dark:bg-gray-900 w-full max-w-2xl rounded-3xl border border-gray-100 dark:border-gray-800 shadow-2xl overflow-hidden text-left">
                        
                        {{-- Modal Header --}}
                        <div class="px-8 py-6 bg-gray-50 dark:bg-gray-800/40 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="p-2.5 bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 rounded-xl">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                </div>
                                <div>
                                    <h3 class="text-sm font-black text-gray-800 dark:text-gray-100 uppercase tracking-wider">Correct Certificate Details</h3>
                                    <p class="text-[11px] text-gray-400 mt-0.5">Override names, hours, or dates to fix system glitches.</p>
                                </div>
                            </div>
                            <button @click="openEditModal = false" type="button" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            </button>
                        </div>

                        {{-- Modal Body Form --}}
                        <form action="{{ route('head-supervisor.certificates.update-details', ['certificate' => $certificate]) }}" method="POST" class="p-8 space-y-6">
                            @csrf
                            @method('PUT')

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div>
                                    <label for="intern_name" class="block text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-2">Intern Full Name</label>
                                    <input type="text" name="intern_name" id="intern_name" value="{{ old('intern_name', $certificate->intern_name ?? (($certificate->intern->first_name ?? '') . ' ' . ($certificate->intern->last_name ?? ''))) }}" class="w-full bg-gray-50 dark:bg-gray-800/30 border border-gray-100 dark:border-gray-800 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-[#0B5694] transition text-gray-800 dark:text-gray-100 font-bold uppercase" required>
                                </div>

                                <div>
                                    <label for="intern_course" class="block text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-2">Intern Course</label>
                                    <input type="text" name="intern_course" id="intern_course" value="{{ old('intern_course', $certificate->intern_course ?? $certificate->intern->course ?? 'Information Technology') }}" class="w-full bg-gray-50 dark:bg-gray-800/30 border border-gray-100 dark:border-gray-800 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-[#0B5694] transition text-gray-800 dark:text-gray-100 font-medium" required>
                                </div>

                                <div>
                                    <label for="intern_school" class="block text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-2">School / University</label>
                                    <input type="text" name="intern_school" id="intern_school" value="{{ old('intern_school', $certificate->intern_school ?? $certificate->intern->school ?? 'FEU-TECH') }}" class="w-full bg-gray-50 dark:bg-gray-800/30 border border-gray-100 dark:border-gray-800 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-[#0B5694] transition text-gray-800 dark:text-gray-100 font-medium" required>
                                </div>

                                <div>
                                    <label sku="completed_hours" class="block text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-2">Completed Hours</label>
                                    <input type="number" name="completed_hours" id="completed_hours" value="{{ old('completed_hours', (int)($certificate->completed_hours ?? 0)) }}" class="w-full bg-gray-50 dark:bg-gray-800/30 border border-gray-100 dark:border-gray-800 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-[#0B5694] transition text-gray-800 dark:text-gray-100 font-medium" required>
                                </div>

                                <div>
                                    <label for="start_date" class="block text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-2">Start Date</label>
                                    <input type="date" name="start_date" id="start_date" value="{{ old('start_date', $certificate->start_date ? $certificate->start_date->format('Y-m-d') : '') }}" class="w-full bg-gray-50 dark:bg-gray-800/30 border border-gray-100 dark:border-gray-800 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-[#0B5694] transition text-gray-800 dark:text-gray-100 font-medium" required>
                                </div>

                                <div>
                                    <label for="end_date" class="block text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-2">End Date</label>
                                    <input type="date" name="end_date" id="end_date" value="{{ old('end_date', $certificate->end_date ? $certificate->end_date->format('Y-m-d') : '') }}" class="w-full bg-gray-50 dark:bg-gray-800/30 border border-gray-100 dark:border-gray-800 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-[#0B5694] transition text-gray-800 dark:text-gray-100 font-medium" required>
                                </div>

                                <div class="sm:col-span-2">
                                    <label for="intern_unit" class="block text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-2">Assigned Office Unit</label>
                                    <input type="text" name="intern_unit" id="intern_unit" value="{{ old('intern_unit', $certificate->intern_unit ?? 'Human Resource Teaching Unit') }}" class="w-full bg-gray-50 dark:bg-gray-800/30 border border-gray-100 dark:border-gray-800 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-[#0B5694] transition text-gray-800 dark:text-gray-100 font-medium" required>
                                </div>
                            </div>

                            <div class="flex justify-end gap-3 pt-6 border-t border-gray-100 dark:border-gray-800">
                                <button type="button" @click="openEditModal = false" class="px-5 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-black uppercase tracking-wider hover:bg-gray-50 dark:hover:bg-gray-700 transition active:scale-95 shadow-sm">Cancel</button>
                                <button type="submit" class="px-5 py-3 bg-[#0B5694] dark:bg-blue-600 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-sm hover:bg-[#084577] transition active:scale-95">Save Correction</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        {{-- Skeletons --}}
        <div x-show="loading" class="flex justify-center my-10">
            <x-skeleton class="w-[215.9mm] max-w-full h-[279.4mm] rounded-none shadow-2xl" />
        </div>

        {{-- Blind Supervisor Feedback Alert --}}
        @if($certificate->supervisor_feedback || $certificate->evaluation)
            <div x-show="!loading" x-transition.opacity.duration.500ms class="mb-8 bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-[24px] p-6 shadow-sm max-w-[215.9mm] mx-auto text-left flex flex-col gap-4">
                <div class="flex items-start gap-4">
                    <div class="p-3 bg-blue-50 dark:bg-blue-900/20 text-[#0B5694] dark:text-blue-400 rounded-xl shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    </div>
                    <div>
                        <h4 class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest leading-none">Supervisor Feedback (Confidential)</h4>
                        @if($certificate->supervisor_feedback)
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2.5 font-medium italic leading-relaxed">
                                "{{ $certificate->supervisor_feedback }}"
                            </p>
                        @else
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-2.5 font-medium italic leading-relaxed">
                                No qualitative comments submitted.
                            </p>
                        @endif
                    </div>
                </div>

                @if($certificate->evaluation)
                    <div class="border-t border-gray-100 dark:border-gray-800 pt-4">
                        <h5 class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-3">Supervisor Assessment Ratings</h5>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                            @foreach([
                                'Technical' => $certificate->evaluation->technical_proficiency,
                                'Professionalism' => $certificate->evaluation->professionalism_ethics,
                                'Communication' => $certificate->evaluation->communication_skills,
                                'Collaboration' => $certificate->evaluation->team_collaboration,
                            ] as $label => $score)
                                <div class="bg-gray-50 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800 p-3 rounded-2xl">
                                    <p class="text-[9px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">{{ $label }}</p>
                                    <p class="text-xs font-black text-gray-700 dark:text-gray-300">{{ $score ?? 'Pending' }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <div x-show="!loading" x-transition.opacity.duration.500ms class="my-6 sm:my-10 flex justify-center overflow-x-auto pb-10 -mx-3 sm:-mx-6 lg:-mx-10 px-3 sm:px-6 lg:px-10">
            <style>
                /* 1. Exact Font Mapping for Browser Preview */
                @font-face {
                    font-family: 'OldEnglishText';
                    src: url('{{ asset("fonts/oldenglish.TTF") }}');
                    font-weight: normal; font-style: normal;
                }
                @font-face {
                    font-family: 'Bookman';
                    src: url('{{ asset("fonts/bookman.TTF") }}');
                    font-weight: normal; font-style: normal;
                }
                @font-face {
                    font-family: 'Bookman';
                    src: url('{{ asset("fonts/bookman-bold.TTF") }}');
                    font-weight: bold; font-style: normal;
                }

                /* 2. PDF Simulation Container */
                .pdf-simulation {
                    width: 215.9mm;
                    height: 279.4mm;
                    background-color: white;
                    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
                    padding: 20mm 30mm 25mm 30mm; /* Matching @page margins */
                    color: #000;
                    position: relative;
                    box-sizing: border-box;
                    margin: 0 auto;
                    flex-shrink: 0;
                }

                .gov-text {
                    font-family: 'OldEnglishText', serif;
                    font-size: 14px;
                    line-height: 1;
                    margin: 0;
                }
                .dept-text {
                    font-family: 'OldEnglishText', serif;
                    font-size: 26px;
                    font-weight: bold;
                    line-height: 1.1;
                    margin: 0;
                }
                .sdo-text {
                    font-family: 'Times New Roman', serif;
                    font-size: 11px;
                    text-transform: uppercase;
                    line-height: 0.95;
                    margin: 0;
                }
                .title-text {
                    font-family: 'Bookman', serif;
                    font-weight: bold;
                    font-size: 22px;
                    margin-top: 50px;
                    margin-bottom: 40px;
                    letter-spacing: 1px;
                    text-align: center;
                }
                .body-text {
                    font-family: 'Bookman', serif;
                    font-size: 15px;
                    line-height: 1.2;
                    text-align: justify;
                }
                .body-text p {
                    text-indent: 60px;
                    margin-bottom: 20px;
                }
                .footer-line-container {
                    position: absolute;
                    bottom: 15mm;
                    left: 30mm;
                    right: 30mm;
                    border-top: 1px solid #000;
                    padding-top: 8px;
                    text-align: center;
                }
            </style>

            {{-- Simulated Short Bond Paper --}}
            <div class="pdf-simulation border border-gray-200">
                
                <!-- DRAFT Watermark -->
                @if($certificate->status === 'pending')
                    <div class="absolute inset-0 flex items-center justify-center opacity-[0.04] pointer-events-none z-0">
                        <h1 class="text-[150px] font-black uppercase transform -rotate-45 tracking-widest text-black">DRAFT</h1>
                    </div>
                @endif

                <!-- Header -->
                <div class="text-center relative z-10">
                    <img src="{{ asset('images/Deped Logo.png') }}" alt="DepEd Logo" class="w-[70px] h-auto mx-auto mb-[5px]">
                    <div class="gov-text">Republic of the Philippines</div>
                    <div class="dept-text">Department of Education</div>
                    <div class="sdo-text" style="margin-top: 2px;">NATIONAL CAPITAL REGION</div>
                    <div class="sdo-text">SCHOOLS DIVISION OFFICE QUEZON CITY</div>
                    <div class="border-t-[1.5px] border-black mt-[10px] w-full mx-auto"></div>
                </div>

                <!-- Title -->
                <div class="title-text relative z-10">CERTIFICATE OF COMPLETION</div>

                <!-- Main Content -->
                <div class="body-text relative z-10">
                    @php
                        $course = $certificate->intern_course ?? $certificate->intern->course ?? 'Information Technology';
                        $article = in_array(strtolower(substr($course, 0, 1)), ['a','e','i','o','u']) ? 'an' : 'a';
                        $startDate = $certificate->start_date ? \Carbon\Carbon::parse($certificate->start_date)->format('F d, Y') : 'N/A';
                        $endDate = $certificate->end_date ? \Carbon\Carbon::parse($certificate->end_date)->format('F d, Y') : 'N/A';
                        $displayDate = $certificate->issue_date ? \Carbon\Carbon::parse($certificate->issue_date) : now();
                        $displayName = $certificate->intern_name ?? (($certificate->intern->first_name ?? '') . ' ' . ($certificate->intern->last_name ?? ''));
                    @endphp
                    <p>
                        This is to certify that <span class="font-bold uppercase">{{ $displayName }}</span> {{ $article }} 
                        <span>{{ $course }}</span> student of 
                        <span>{{ $certificate->intern_school ?? $certificate->intern->school ?? 'FEU-TECH' }}</span>, 
                        has completed {{ (int)($certificate->completed_hours ?? 0) }} hours On-the-Job Training 
                        on {{ $startDate }} to {{ $endDate }} 
                        at the Schools Division Office-Quezon City, <span>{{ $certificate->intern_unit ?? 'Human Resource Teaching Unit' }}</span>.
                    </p>

                    <p>
                        Issued this {{ $displayDate->format('j') }}<sup>{{ $displayDate->format('S') }}</sup> day of {{ $displayDate->format('F Y') }} at the Schools Division Office-Quezon City.
                    </p>
                </div>

                <!-- Signature Section (Table layout for alignment) -->
                <table style="width: 100%; margin-top: 60px; border: 0;" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="vertical-align: bottom; font-size: 10px; font-weight: bold; font-style: italic; font-family: 'Bookman', serif;">
                            HRNTP/rocelle/Pj
                        </td>
                        <td style="text-align: right; vertical-align: bottom;">
                            <div style="display: inline-block; text-align: center; width: 250px; font-family: 'Bookman', serif;">
                                <div class="font-bold uppercase" style="font-size: 15px; margin-bottom: 2px;">MICHELLE A. MAL-IN</div>
                                <div style="font-size: 14px;">Administrative Officer IV</div>
                            </div>
                        </td>
                    </tr>
                </table>

                <!-- Verification & Footer -->
                <div class="verification-code" style="position: absolute; bottom: 22mm; width: 100%; left: 0; text-align: center; font-size: 11px; color: #777; font-family: monospace;">
                    @if($certificate->status === 'published')
                        Verification Code: {{ $certificate->verification_code }}
                    @endif
                </div>

                <div class="footer-line-container">
                    <img src="{{ asset('images/certificate-footer.png') }}" alt="certificate footer" style="width: 100%; max-width: 650px; height: auto;">
                </div>
            </div>
        </div>
    </div>
</x-page-layout>
@endsection