<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Certificate of Completion</title>
    <style>
        /* Aggressive Font Mapping for Local TTF Files*/
        
        /* Old English Mapping - Used for Government and Department Text*/
        @font-face {
            font-family: 'OldEnglishText';
            src: url('{{ public_path("fonts/oldenglish.TTF") }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        @font-face {
            font-family: 'OldEnglishText';
            src: url('{{ public_path("fonts/oldenglish.TTF") }}') format('truetype');
            font-weight: bold;
            font-style: normal;
        }

        /* Bookman Family Mapping - Used for Body Text*/
        @font-face {
            font-family: 'Bookman';
            src: url('{{ public_path("fonts/bookman.TTF") }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        @font-face {
            font-family: 'Bookman';
            src: url('{{ public_path("fonts/bookman-bold.TTF") }}') format('truetype');
            font-weight: bold;
            font-style: normal;
        }
        @font-face {
            font-family: 'Bookman';
            src: url('{{ public_path("fonts/bookman-italic.TTF") }}') format('truetype');
            font-weight: normal;
            font-style: italic;
        }
        @font-face {
            font-family: 'Bookman';
            src: url('{{ public_path("fonts/bookman-bolditalic.TTF") }}') format('truetype');
            font-weight: bold;
            font-style: italic;
        }

        /* Page and Body Setup*/
        @page {
            margin: 20mm 30mm 25mm 30mm; 
            size: 8.5in 11in; 
        }
        body {
            font-family: 'Bookman', Georgia, serif;
            color: # ;
            background-color: # fff;
            margin: 0;
            padding: 0;
        }

        /* Header Styling - Matched to Sample Spacing*/
        .header {
            text-align: center;
        }
        .header img.logo {
            width: 70px;
            height: auto;
            margin-bottom: 5px;
        }
        .gov-text {
            font-family: 'OldEnglishText', serif !important;
            font-size: 14px;
            margin: 0;
            line-height: 1; 
        }
        .dept-text {
            font-family: 'OldEnglishText', serif !important;
            font-size: 26px;
            font-weight: bold;
            margin: 0;
            line-height: 1.1; 
        }
        .region-text, .office-text {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11px;
            text-transform: uppercase;
            margin: 0;
            padding: 0;
            line-height: 0.95; /* Removes the gap between these two lines*/
        }
        .region-text {
            margin-top: 2px; 
        }
        .header-line {
            border-top: 1.5px solid # ;
            width: 100%;
            margin-top: 10px;
        }

        /* Title and Content Spacing*/
        .title {
            text-align: center;
            font-size: 22px;
            font-weight: bold;
            margin-top: 50px; 
            margin-bottom: 40px; 
            letter-spacing: 1px;
        }
        .content {
            font-size: 15px;
            line-height: 1.2; /* Single spacing as requested*/
            text-align: justify;
        }
        .content p {
            text-indent: 60px; 
            margin-top: 0;
            margin-bottom: 20px;
        }
        .bold-uppercase {
            font-weight: bold;
            text-transform: uppercase;
        }

        /* Footer and Signature Section*/
        .footer {
            position: fixed;
            bottom: -15mm; 
            left: 0; 
            right: 0;  
            text-align: center;
            border-top: 1px solid # ; /* Horizontal line on footer */
            padding-top: 8px; 
        }
        .footer img {
            width: 100%; 
            max-width: 650px;
            height: auto;
        }
        
        .verification-code {
            position: fixed;
            bottom: 12mm; 
            left: 0;
            width: 100%;
            text-align: center;
            font-size: 11px;
            color: # ;
            font-family: monospace;
        }

        .bottom-section {
            width: 100%;
            margin-top: 60px;
        }

        /* Watermark style for PDF*/
        .watermark {
            position: fixed;
            top: 35%;
            left: 5%;
            width: 90%;
            text-align: center;
            opacity: 0.05;
            z-index: -1000;
            font-size: 110px;
            font-weight: bold;
            font-family: sans-serif;
            transform: rotate(-45deg);
        }
    </style>
</head>
@php
    $logoUrl = (isset($template) && $template->logo_path) 
        ? public_path('storage/' . $template->logo_path) 
        : public_path('images/Deped Logo.png');
    $footerUrl = (isset($template) && $template->footer_path) 
        ? public_path('storage/' . $template->footer_path) 
        : public_path('images/certificate-footer.png');
    
    $govText = $template->gov_text ?? 'Republic of the Philippines';
    $deptText = $template->dept_text ?? 'Department of Education';
    $regionText = $template->region_text ?? 'NATIONAL CAPITAL REGION';
    $officeText = $template->office_text ?? 'SCHOOLS DIVISION OFFICE QUEZON CITY';
    
    $titleText = $template->title_text ?? 'CERTIFICATE OF COMPLETION';
    $bodyTextPattern = $template->body_text ?? 'This is to certify that {name} {article} {course} student of {school}, has completed {hours} hours On-the-Job Training on {start_date} to {end_date} at the Schools Division Office-Quezon City, {unit}.';
    
    $signatoryName = $template->signatory_name ?? 'MICHELLE A. MAL-IN';
    $signatoryTitle = $template->signatory_title ?? 'Administrative Officer IV';
    $initials = $template->initials ?? 'HRNTP/rocelle/Pj';

    $courseStr = $intern->course ?? 'Information Technology';
    $article = in_array(strtolower(substr($courseStr, 0, 1)), ['a','e','i','o','u']) ? 'an' : 'a';
    
    $startDate = $intern->start_date ? \Carbon\Carbon::parse($intern->start_date)->format('F d, Y') : 'N/A';
    $endDate = $intern->end_date ? \Carbon\Carbon::parse($intern->end_date)->format('F d, Y') : 'N/A';
    
    $processedBody = str_replace(
        ['{name}', '{article}', '{course}', '{school}', '{hours}', '{start_date}', '{end_date}', '{unit}'],
        [
            '<span class="bold-uppercase">' . ($intern->name ?? '') . '</span>',
            $article,
            '<span>' . $courseStr . '</span>',
            '<span>' . ($intern->school ?? 'FEU-TECH') . '</span>',
            '<span>' . (int)($intern->total_hours ?? 0) . '</span>',
            $startDate,
            $endDate,
            '<span>' . ($intern->unit ?? 'Human Resource Teaching Unit') . '</span>'
        ],
        $bodyTextPattern
    );
@endphp
<body>

    @if(!isset($verificationCode) || str_starts_with($verificationCode, 'DRAFT-'))
        <div class="watermark">DRAFT</div>
    @endif

    <!-- Footer fixed element -->
    <div class="footer">
        <img src="{{ $footerUrl }}" alt="certificate footer">
    </div>

    <!-- Verification code overlay -->
    @if(isset($verificationCode))
        <div class="verification-code">
            Verification Code: {{ $verificationCode }}
        </div>
    @endif

    <!-- Header Section -->
    <div class="header">
        <img src="{{ $logoUrl }}" alt="DepEd Logo" class="logo">
        <div class="gov-text">{{ $govText }}</div>
        <div class="dept-text">{{ $deptText }}</div>
        <div class="region-text">{{ $regionText }}</div>
        <div class="office-text">{{ $officeText }}</div>
        <div class="header-line"></div>
    </div>

    <!-- Title Section -->
    <div class="title">{{ $titleText }}</div>

    <!-- Main Certificate Content -->
    <div class="content">
        <p>
            {!! $processedBody !!}
        </p>

        <p>
            Issued this {{ $date->format('j') }}<sup>{{ $date->format('S') }}</sup> day of {{ $date->format('F Y') }} at the Schools Division Office-Quezon City.
        </p>
    </div>

    <!-- Signature and Initials Section -->
    <table class="bottom-section" border="0" cellpadding="0" cellspacing="0">
        <tr>
            <!-- Left side: Initials -->
            <td style="vertical-align: bottom; font-size: 10px; font-style: italic; font-weight: bold; width: 50%;">
                {{ $initials }}
            </td>
            
            <!-- Right side: Signature Block -->
            <td align="right" style="vertical-align: bottom; width: 50%;">
                <div style="text-align: center; width: 250px; display: inline-block;">
                    <div style="font-weight: bold; font-size: 15px; text-transform: uppercase; margin-bottom: 2px;">
                        {{ $signatoryName }}
                    </div>
                    <div style="font-size: 14px;">
                        {{ $signatoryTitle }}
                    </div>
                </div>
            </td>
        </tr>
    </table>

</body>
</html>