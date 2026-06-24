<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $templateData['title'] }}</title>
    <style>
        /* 1. Aggressive Font Mapping for Local TTF Files */
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

        /* 2. Page and Body Setup */
        @page {
            margin: 15mm 20mm 20mm 20mm; 
            size: 8.5in 11in; 
        }
        body {
            font-family: 'Bookman', Georgia, serif;
            color: #000;
            background-color: #fff;
            margin: 0;
            padding: 0;
            font-size: 11.5px;
            line-height: 1.35;
        }

        /* 3. Header Styling - Matched to Certificate */
        .header {
            text-align: center;
        }
        .header img.logo {
            width: 60px;
            height: auto;
            margin-bottom: 3px;
        }
        .gov-text {
            font-family: 'OldEnglishText', serif !important;
            font-size: 13px;
            margin: 0;
            line-height: 1; 
        }
        .dept-text {
            font-family: 'OldEnglishText', serif !important;
            font-size: 24px;
            font-weight: bold;
            margin: 0;
            line-height: 1.1; 
        }
        .region-text, .office-text {
            font-family: 'Times New Roman', Times, serif;
            font-size: 10px;
            text-transform: uppercase;
            margin: 0;
            padding: 0;
            line-height: 0.95;
        }
        .region-text {
            margin-top: 2px; 
        }
        .header-line {
            border-top: 1.5px solid #000;
            width: 100%;
            margin-top: 8px;
        }

        /* 4. Footer Banner - Matched to Certificate */
        .footer {
            position: fixed;
            bottom: -12mm; 
            left: 0; 
            right: 0;  
            text-align: center;
            border-top: 1px solid #000;
            padding-top: 5px; 
        }
        .footer img {
            width: 100%; 
            max-width: 650px;
            height: auto;
        }

        /* 5. Document Content Styling */
        .form-code {
            text-align: right;
            font-family: monospace;
            font-size: 8.5px;
            color: #777;
            margin-bottom: 3px;
        }
        .title {
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 15px;
            margin-bottom: 15px;
            letter-spacing: 0.5px;
        }
        .section-title {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10.5px;
            border-bottom: 1px solid #000;
            padding-bottom: 2px;
            margin-top: 14px;
            margin-bottom: 8px;
        }
        .field-group {
            margin-bottom: 6px;
        }
        .field-label {
            font-weight: bold;
            display: inline-block;
            width: 220px;
            vertical-align: top;
        }
        .field-value {
            display: inline-block;
            vertical-align: top;
        }
        
        /* Stacked Block layout for multi-line fields/long labels */
        .field-group-block .field-label {
            display: block;
            width: 100%;
            margin-bottom: 3px;
        }
        .field-group-block .field-value {
            display: block;
            width: 100%;
        }

        .footer-note {
            margin-top: 15px;
            font-size: 9.5px;
            font-style: italic;
            color: #444;
            line-height: 1.25;
            border-left: 2px solid #000;
            padding-left: 8px;
        }

        /* 6. Signature Section - Layout aligned with Document footer */
        .signature-section {
            margin-top: 25px;
            width: 100%;
        }
        .signature-col {
            width: 50%;
            float: left;
            text-align: center;
        }
        .signature-line {
            width: 180px;
            border-bottom: 1px solid #000;
            margin: 25px auto 4px auto;
        }
        .signature-label {
            font-size: 9.5px;
            font-weight: bold;
            text-transform: uppercase;
        }
    </style>
</head>
<body>

    <!-- SDO Footer Banner -->
    <div class="footer">
        <img src="{{ public_path('images/certificate-footer.png') }}" alt="certificate footer">
    </div>

    <div class="form-code">{{ $templateData['form_code'] }}</div>

    <!-- Official Header -->
    <div class="header">
        <img src="{{ public_path('images/Deped Logo.png') }}" alt="DepEd Logo" class="logo">
        <div class="gov-text">Republic of the Philippines</div>
        <div class="dept-text">Department of Education</div>
        <div class="region-text">NATIONAL CAPITAL REGION</div>
        <div class="office-text">SCHOOLS DIVISION OFFICE QUEZON CITY</div>
        <div class="header-line"></div>
    </div>

    <!-- Document Title -->
    <div class="title">{{ $templateData['title'] }}</div>

    <!-- Form Sections -->
    @foreach($templateData['sections'] as $sectionName => $section)
        <div class="section-title">{{ $sectionName }}</div>
        
        @if(isset($section['data']))
            @foreach($section['data'] as $label => $value)
                @php
                    $isLongField = (strpos($value, "\n") !== false || strlen($label) > 40);
                @endphp
                <div class="field-group {{ $isLongField ? 'field-group-block' : '' }}">
                    <span class="field-label">{{ $label }}:</span>
                    <span class="field-value">{!! nl2br(e($value)) !!}</span>
                </div>
            @endforeach
        @endif
    @endforeach

    <div class="footer-note">
        <strong>Important Direction:</strong> {{ $templateData['note'] }}
    </div>

    <!-- Sign-off Block -->
    <div class="signature-section">
        <div class="signature-col">
            <div class="signature-line"></div>
            <div class="signature-label">Applicant Signature</div>
            <div style="font-size: 8.5px; color: #666; margin-top: 2px;">Date signed: __________________</div>
        </div>
        <div class="signature-col">
            <div class="signature-line"></div>
            <div class="signature-label">Endorsing Officer / Admin</div>
            <div style="font-size: 8.5px; color: #666; margin-top: 2px;">Date signed: __________________</div>
        </div>
        <div style="clear: both;"></div>
    </div>

</body>
</html>
