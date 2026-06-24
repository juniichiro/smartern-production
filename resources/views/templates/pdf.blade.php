<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $templateData['title'] }}</title>
    <style>
        @page {
            margin: 25mm 25mm 25mm 25mm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 13px;
            line-height: 1.5;
            color: #000;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header-gov {
            font-size: 13px;
            font-style: italic;
            margin-bottom: 2px;
        }
        .header-dept {
            font-size: 20px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .header-region {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }
        .header-sdo {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .double-line {
            border-top: 1.5px solid #000;
            border-bottom: 0.5px solid #000;
            height: 3px;
            margin-top: 8px;
            margin-bottom: 20px;
        }
        .form-code {
            text-align: right;
            font-family: monospace;
            font-size: 10px;
            color: #555;
            margin-bottom: 10px;
        }
        .title {
            text-align: center;
            font-size: 15px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 30px;
            letter-spacing: 0.5px;
        }
        .section-title {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12px;
            border-bottom: 1px solid #000;
            padding-bottom: 3px;
            margin-top: 25px;
            margin-bottom: 15px;
        }
        .field-group {
            margin-bottom: 12px;
        }
        .field-label {
            font-weight: bold;
            display: inline-block;
            width: 250px;
        }
        .field-value {
            display: inline-block;
        }
        .text-block {
            margin-top: 10px;
            font-family: Courier, monospace;
            white-space: pre-wrap;
        }
        .footer-note {
            margin-top: 40px;
            font-size: 11px;
            font-style: italic;
            color: #444;
            line-height: 1.3;
            border-left: 2.5px solid #666;
            padding-left: 10px;
        }
        .signature-section {
            margin-top: 50px;
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
            margin: 40px auto 5px auto;
        }
        .signature-label {
            font-size: 11px;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <div class="form-code">{{ $templateData['form_code'] }}</div>

    <div class="header">
        <div class="header-gov">Republic of the Philippines</div>
        <div class="header-dept">Department of Education</div>
        <div class="header-region">National Capital Region</div>
        <div class="header-sdo">Schools Division Office, Quezon City</div>
        <div class="double-line"></div>
    </div>

    <div class="title">{{ $templateData['title'] }}</div>

    @foreach($templateData['sections'] as $sectionName => $fields)
        <div class="section-title">{{ $sectionName }}</div>
        
        @foreach($fields as $label => $value)
            <div class="field-group">
                <span class="field-label">{{ $label }}:</span>
                <span class="field-value">{!! nl2br(e($value)) !!}</span>
            </div>
        @endforeach
    @endforeach

    <div class="footer-note">
        <strong>Important Note:</strong> {{ $templateData['note'] }}
    </div>

    <div class="signature-section">
        <div class="signature-col">
            <div class="signature-line"></div>
            <div class="signature-label">Applicant / Intern Signature</div>
            <div style="font-size: 10px; color: #666;">Date: __________________</div>
        </div>
        <div class="signature-col">
            <div class="signature-line"></div>
            <div class="signature-label">SDO Administrator / Supervisor</div>
            <div style="font-size: 10px; color: #666;">Date: __________________</div>
        </div>
        <div style="clear: both;"></div>
    </div>

</body>
</html>
