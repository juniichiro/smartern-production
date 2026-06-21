@extends('layouts.app')

@section('title', 'My Certificate')

@section('content')
<div class="flex min-h-screen bg-gray-50 dark:bg-gray-950 font-sans text-[14px]">
    {{-- SIDEBAR --}}
    <x-sidebar portalType="Intern" 
               userName="{{ Auth::user()->internDetails->first_name ?? 'Intern' }}" 
               userRole="{{ Auth::user()->internDetails->office->name ?? 'Office' }}"/>

    {{-- MAIN CONTENT --}}
    <main class="flex-1 ml-0 lg:ml-64 mr-0 lg:mr-80 p-4 md:p-6 lg:p-10 pt-20 lg:pt-10 text-left">
        {{-- Header & Download Navigation --}}
        <header class="mb-12 flex flex-col md:flex-row justify-between items-start md:items-end gap-6 shrink-0">
            <div>
                <h2 class="text-2xl md:text-4xl font-extrabold text-gray-900 dark:text-gray-100 tracking-normal uppercase leading-tight">Certificate</h2>
                <p class="text-[10px] md:text-[11px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-[0.3em] mt-1">Official Proof of Internship Completion</p>
            </div>

            <div class="flex gap-4 w-full md:w-auto">
                @if($certificate && $certificate->status === 'published')
                    <a href="{{ route('intern.certificates.download', ['certificate' => $certificate->certificate_id]) }}" 
                       class="w-full md:w-auto bg-[#0B5694] text-white px-8 py-3 rounded-xl text-sm font-bold shadow-lg shadow-blue-500/20 hover:bg-[#084577] transition uppercase tracking-wider flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                        Download PDF
                    </a>
                @endif
            </div>
        </header>

        {{-- Meta Data Cards --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-12">
            <div class="bg-white dark:bg-gray-900 p-6 rounded-[20px] shadow-sm border border-gray-100 dark:border-gray-800 text-center">
                <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Certificate ID</p>
                <p class="text-lg font-black text-gray-800 dark:text-gray-100 font-mono">{{ $certificate->verification_code ?? 'PENDING' }}</p>
            </div>
            <div class="bg-white dark:bg-gray-900 p-6 rounded-[20px] shadow-sm border border-gray-100 dark:border-gray-800 text-center">
                <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Training Hours</p>
                <p class="text-lg font-black text-gray-800 dark:text-gray-100 tracking-tight">{{ $certificate->completed_hours ?? 0 }} Hours</p>
            </div>
            <div class="bg-white dark:bg-gray-900 p-6 rounded-[20px] shadow-sm border border-gray-100 dark:border-gray-800 text-center">
                <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Status</p>
                @if($certificate && $certificate->status === 'published')
                    <p class="text-lg font-black text-green-500 uppercase tracking-wider">Verified</p>
                @else
                    <p class="text-lg font-black text-amber-500 uppercase tracking-wider">Under Review</p>
                @endif
            </div>
        </div>

        {{-- PDF Replica Preview --}}
        <div class="overflow-x-auto pb-10 flex justify-center">
            
            <style>
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

                .gov-text { font-family: 'OldEnglishText', serif; font-size: 14px; line-height: 1; margin: 0; text-align: center; }
                .dept-text { font-family: 'OldEnglishText', serif; font-size: 26px; font-weight: bold; line-height: 1.1; margin: 0; text-align: center; }
                .sdo-text { font-family: 'Times New Roman', serif; font-size: 11px; text-transform: uppercase; line-height: 0.95; margin: 0; text-align: center; }
                .title-text { font-family: 'Bookman', serif; font-weight: bold; font-size: 22px; margin-top: 50px; margin-bottom: 40px; letter-spacing: 1px; text-align: center; }
                .body-text { font-family: 'Bookman', serif; font-size: 15px; line-height: 1.2; text-align: justify; }
                .body-text p { text-indent: 60px; margin-bottom: 20px; }
                
                .footer-line-container {
                    position: absolute;
                    bottom: 10mm;
                    left: 30mm;
                    right: 30mm;
                    border-top: 1.5px solid #000;
                    padding-top: 6px;
                    text-align: center;
                }

                /* THE "STAND OUT" STYLE */
                .verification-tag {
                    position: absolute;
                    bottom: 36mm; /* Adjusted to sit prominently above the line */
                    width: 100%;
                    left: 0;
                    text-align: center;
                    font-size: 11px;
                    color: #777;
                    opacity: 0.7;
                    font-family: 'Courier New', monospace;

                }
            </style>

            <div class="pdf-simulation">
                @if(!$certificate || $certificate->status !== 'published')
                    <div class="absolute inset-0 flex items-center justify-center opacity-[0.08] pointer-events-none z-50">
                        <h1 class="text-[150px] font-black uppercase transform -rotate-45 tracking-widest text-gray-500" style="font-family: sans-serif;">DRAFT</h1>
                    </div>
                @endif

                @if($certificate)
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
                            $intern = $certificate->internDetails ?? $certificate->intern;
                            $internName = $intern 
                                ? trim(($intern->first_name ?? '') . ' ' . ($intern->last_name ?? '')) 
                                : 'Unknown Intern';

                            $course = $intern->course ?? 'Information Technology';
                            $article = in_array(strtolower(substr($course, 0, 1)), ['a','e','i','o','u']) ? 'an' : 'a';
                            
                            $startDate = $certificate->start_date ? \Carbon\Carbon::parse($certificate->start_date)->format('F d, Y') : 'N/A';
                            $endDate = $certificate->end_date ? \Carbon\Carbon::parse($certificate->end_date)->format('F d, Y') : 'N/A';
                            $displayDate = $certificate->issue_date ? \Carbon\Carbon::parse($certificate->issue_date) : now();
                        @endphp
                        <p>
                            This is to certify that <span class="font-bold uppercase">{{ $internName }}</span> {{ $article }} 
                            <span>{{ $course }}</span> student of 
                            <span>{{ $intern->school ?? 'FEU-TECH' }}</span>, 
                            has completed {{ (int)($certificate->completed_hours ?? 0) }} hours On-the-Job Training 
                            on {{ $startDate }} to {{ $endDate }} 
                            at the Schools Division Office-Quezon City, <span>{{ $certificate->intern_unit ?? 'Human Resource Teaching Unit' }}</span>.
                        </p>

                        <p>
                            Issued this {{ $displayDate->format('j') }}<sup>{{ $displayDate->format('S') }}</sup> day of {{ $displayDate->format('F Y') }} at the Schools Division Office-Quezon City.
                        </p>
                    </div>

                    <!-- Signature Block -->
                    <table style="width: 100%; margin-top: 60px; border: 0;" cellpadding="0" cellspacing="0" class="relative z-10">
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

                    <!-- STAND-OUT VERIFICATION TAG -->
                    <div class="verification-tag relative z-10">
                        Verification Code: {{ $certificate->verification_code ?? 'DRAFT-' . Str::upper(Str::random(8)) }}
                    </div>

                    <!-- Footer -->
                    <div class="footer-line-container">
                        <img src="{{ asset('images/certificate-footer.png') }}" 
                             alt="certificate footer" 
                             style="width: 100%; max-width: 650px; height: auto;">
                    </div>
                @else
                    <div class="h-full flex flex-col items-center justify-center text-center p-12">
                        <div class="w-24 h-24 bg-blue-50 dark:bg-blue-900/20 rounded-full flex items-center justify-center text-[#0B5694] dark:text-blue-400 mb-8 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/></svg>
                        </div>
                        <h4 class="text-3xl font-black text-gray-800 dark:text-gray-100 tracking-tight uppercase">Certificate Pending</h4>
                        <p class="text-xl text-gray-400 dark:text-gray-500 mt-4 font-medium italic">Your document will appear here once published by HR</p>
                    </div>
                @endif
            </div>
        </div>
    </main>

    <x-notification-sidebar :notifications="$notifications" />
</div>
@endsection
