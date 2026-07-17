@extends('layouts.app')

@section('title', 'Certificate Preview')

@section('content')
<div class="flex min-h-screen bg-gray-50 dark:bg-gray-950 font-sans text-[14px]" x-data="supervisorCertificatePreviewManager">
    {{-- Sidebar --}}
    <x-sidebar portalType="Supervisor" 
               userName="{{ Auth::user()->supervisorDetails->first_name }}" 
               userRole="{{ Auth::user()->supervisorDetails->office->name }}" />

    <main class="ml-0 lg:ml-64 mr-0 lg:mr-80 flex-1 min-w-0 p-3 sm:p-4 md:p-6 lg:p-10 pt-20 lg:pt-10 text-left">
        {{-- Header & Back Navigation --}}
        <header class="mb-6 md:mb-8 flex flex-col sm:flex-row justify-between items-start md:items-end gap-4">
            <div>
                <a href="{{ route('supervisor.certificates.index') }}" class="flex items-center gap-2 text-gray-400 dark:text-gray-500 hover:text-[#0B5694] dark:hover:text-blue-400 transition font-bold uppercase tracking-widest text-xs mb-3 md:mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    <span class="sm:hidden">Back</span><span class="hidden sm:inline">Back to List</span>
                </a>
                <h2 class="text-xl sm:text-2xl md:text-4xl font-bold text-gray-800 dark:text-gray-100 tracking-tight">Certificate Preview</h2>
                <p class="text-sm md:text-base text-gray-400 dark:text-gray-500 mt-2 font-medium italic">Review the exact document output before endorsement</p>
            </div>

            {{-- Action Buttons --}}
            <div class="flex flex-col sm:flex-row gap-3 md:gap-4 w-full sm:w-auto">
                <a href="{{ route('supervisor.certificates.download', ['certificate' => $intern->user_id]) }}" 
                   class="w-full sm:w-auto bg-gray-800 dark:bg-gray-700 text-white min-h-[48px] px-6 py-3 rounded-xl text-xs font-black shadow-lg hover:bg-black dark:hover:bg-gray-600 transition uppercase tracking-widest flex items-center justify-center gap-2 active:scale-95">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                    <span class="sm:hidden">Download</span><span class="hidden sm:inline">Download PDF Draft</span>
                </a>

                @if(!$certificateRecord)
                    <button type="button" 
                            @click="showFeedbackModal = true"
                            class="w-full sm:w-auto bg-[#0B5694] dark:bg-blue-600 text-white min-h-[48px] px-6 py-3 rounded-xl text-xs font-black shadow-lg shadow-blue-500/20 hover:bg-[#084577] dark:hover:bg-blue-700 transition uppercase tracking-widest flex items-center justify-center gap-2 active:scale-95">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        <span class="sm:hidden">Endorse</span><span class="hidden sm:inline">Endorse to Head</span>
                    </button>
                @endif
            </div>
        </header>

        <style>
            /* Exact Font Mapping for Browser Preview*/
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

            /* PDF Simulation Container*/
            .pdf-simulation {
                width: 215.9mm;
                height: 279.4mm;
                background-color: white;
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
                padding: 20mm 30mm 25mm 30mm;
                color: # ;
                position: relative;
                box-sizing: border-box;
                margin: 0 auto;
            }

            @media (max-width: 1023px) {
                .pdf-simulation {
                    transform: scale(0.7);
                    transform-origin: top center;
                    margin-bottom: -84mm;
                }
            }
            @media (max-width: 639px) {
                .pdf-simulation {
                    transform: scale(0.45);
                    margin-bottom: -154mm;
                }
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
                border-top: 1px solid # ;
                padding-top: 8px;
                text-align: center;
            }
        </style>

        {{-- Skeletons --}}
        <template x-if="loading">
            <div class="overflow-x-auto pb-6 md:pb-10 flex justify-center w-full">
                <x-skeleton class="w-[215.9mm] h-[279.4mm] rounded-none shadow-2xl" />
            </div>
        </template>

        {{-- Simulated Short Bond Paper --}}
        <div x-show="!loading" x-transition.opacity.duration.500ms class="overflow-x-auto pb-2 md:pb-6 flex justify-center custom-scrollbar">
            <div class="pdf-simulation">
                
                <!-- DRAFT Watermark -->
                @if(!$certificateRecord || $certificateRecord->status !== 'published')
                    <div class="absolute inset-0 flex items-center justify-center opacity-[0.04] pointer-events-none z-0">
                        <h1 class="text-[150px] font-black uppercase transform -rotate-45 tracking-widest">DRAFT</h1>
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
                        $course = $intern->course ?? 'Information Technology';
                        $article = in_array(strtolower(substr($course, 0, 1)), ['a','e','i','o','u']) ? 'an' : 'a';
                        $startDate = ($certificateRecord->start_date ?? $intern->start_date) ? \Carbon\Carbon::parse($certificateRecord->start_date ?? $intern->start_date)->format('F d, Y') : 'N/A';
                        $endDate = ($certificateRecord->end_date ?? now()) ? \Carbon\Carbon::parse($certificateRecord->end_date ?? now())->format('F d, Y') : 'N/A';
                        $displayDate = ($certificateRecord->issue_date ?? now()) ? \Carbon\Carbon::parse($certificateRecord->issue_date ?? now()) : now();
                    @endphp
                    <p>
                        This is to certify that <span class="font-bold uppercase">{{ $intern->first_name ?? '' }} {{ $intern->last_name ?? '' }}</span> {{ $article }} 
                        <span>{{ $course }}</span> student of 
                        <span>{{ $intern->school ?? 'FEU-TECH' }}</span>, 
                        has completed {{ (int)($certificateRecord->completed_hours ?? $intern->hours_rendered ?? 480) }} hours On-the-Job Training 
                        on {{ $startDate }} to {{ $endDate }} 
                        at the Schools Division Office-Quezon City, <span>{{ $certificateRecord->intern_unit ?? Auth::user()->supervisorDetails->office->name ?? 'Human Resource Teaching Unit' }}</span>.
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
                    Verification Code: {{ $certificateRecord->verification_code ?? 'DRAFT-' . strtoupper(Str::random(8)) }}
                </div>

                <div class="footer-line-container">
                    <img src="{{ asset('images/certificate-footer.png') }}" alt="certificate footer" style="width: 100%; max-width: 650px; height: auto;">
                </div>
            </div>
        </div>
        <p class="text-center text-[9px] text-gray-300 dark:text-gray-700 font-medium lg:hidden">Scroll or pinch to view full document</p>
    </main>

    {{-- Blind Supervisor Feedback Modal --}}
    <div x-show="showFeedbackModal" 
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 overflow-y-auto"
         x-cloak>
        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-gray-900/60 dark:bg-black/70 backdrop-blur-md transition-opacity" 
             @click="showFeedbackModal = false"
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
            <form action="{{ route('supervisor.certificates.endorse', ['certificate' => $intern->user_id]) }}" method="POST">
                @csrf
                
                <div class="space-y-4 mb-6">
                    <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed font-medium">
                        Provide qualitative feedback and evaluation for <span class="font-extrabold text-gray-700 dark:text-gray-300">{{ $intern->first_name }} {{ $intern->last_name }}</span> to review during the endorsement process. This feedback is completely confidential and will not be displayed to the intern.
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
                            @click="showFeedbackModal = false"
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

    <x-notification-sidebar :notifications="$notifications" />
</div>
@endsection
