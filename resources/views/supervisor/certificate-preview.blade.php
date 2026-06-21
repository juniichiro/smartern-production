@extends('layouts.app')

@section('title', 'Certificate Preview')

@section('content')
<div class="flex min-h-screen bg-gray-50 dark:bg-gray-950 font-sans text-[14px]" x-data="supervisorCertificatePreviewManager">
    {{-- Sidebar --}}
    <x-sidebar portalType="Supervisor" 
               userName="{{ Auth::user()->supervisorDetails->first_name }}" 
               userRole="{{ Auth::user()->supervisorDetails->office->name }}" />

    <main class="ml-0 lg:ml-64 mr-0 lg:mr-80 flex-1 p-4 md:p-6 lg:p-10 pt-20 lg:pt-10 text-left">
        {{-- Header & Back Navigation --}}
        <header class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
            <div>
                <a href="{{ route('supervisor.certificates.index') }}" class="flex items-center gap-2 text-gray-400 dark:text-gray-500 hover:text-[#0B5694] dark:hover:text-blue-400 transition font-bold uppercase tracking-widest text-xs mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    Back to List
                </a>
                <h2 class="text-2xl md:text-4xl font-bold text-gray-800 dark:text-gray-100 tracking-tight">Certificate Preview</h2>
                <p class="text-sm md:text-base text-gray-400 dark:text-gray-500 mt-2 font-medium italic">Review the exact document output before endorsement</p>
            </div>

            {{-- Action Buttons --}}
            <div class="flex flex-wrap gap-4 w-full md:w-auto">
                <a href="{{ route('supervisor.certificates.download', ['certificate' => $intern->user_id]) }}" 
                   class="w-full sm:w-auto bg-gray-800 dark:bg-gray-700 text-white px-6 py-3 rounded-xl text-xs font-black shadow-lg hover:bg-black dark:hover:bg-gray-600 transition uppercase tracking-widest flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                    Download PDF Draft
                </a>

                @if(!$certificateRecord)
                    <form action="{{ route('supervisor.certificates.endorse', ['certificate' => $intern->user_id]) }}" method="POST" class="w-full sm:w-auto">
                        @csrf
                        <button type="submit" class="w-full bg-[#0B5694] dark:bg-blue-600 text-white px-6 py-3 rounded-xl text-xs font-black shadow-lg shadow-blue-500/20 hover:bg-[#084577] dark:hover:bg-blue-700 transition uppercase tracking-widest flex items-center justify-center gap-2 active:scale-95">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            Endorse to Head
                        </button>
                    </form>
                @endif
            </div>
        </header>

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
                padding: 20mm 30mm 25mm 30mm;
                color: #000;
                position: relative;
                box-sizing: border-box;
                margin: 0 auto;
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

        {{-- Skeletons --}}
        <template x-if="loading">
            <div class="overflow-x-auto pb-10 flex justify-center w-full">
                <x-skeleton class="w-[215.9mm] h-[279.4mm] rounded-none shadow-2xl" />
            </div>
        </template>

        {{-- Simulated Short Bond Paper --}}
        <div x-show="!loading" x-transition.opacity.duration.500ms class="overflow-x-auto pb-10 flex justify-center custom-scrollbar">
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
    </main>

    <x-notification-sidebar :notifications="$notifications" />
</div>
@endsection
