<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CertificateTemplate extends Model
{
    use HasFactory;

    protected $table = 'certificate_templates';

    protected $fillable = [
        'logo_path',
        'footer_path',
        'gov_text',
        'dept_text',
        'region_text',
        'office_text',
        'title_text',
        'body_text',
        'signatory_name',
        'signatory_title',
        'initials',
        'html_layout',
    ];

    /**
     * Get the latest active template, or returns a new instance with default values.
     */
    public static function getActive()
    {
        return self::latest()->first() ?? new self();
    }

    /**
     * Replaces placeholders in the custom uploaded HTML template.
     */
    public function parse($intern, $certificate, $verificationCode, $date)
    {
        $html = $this->html_layout;
        if (!$html) {
            return null;
        }

        $courseStr = $intern->course ?? 'Information Technology';
        $article = in_array(strtolower(substr($courseStr, 0, 1)), ['a','e','i','o','u']) ? 'an' : 'a';
        
        $startDate = $intern->start_date ? \Carbon\Carbon::parse($intern->start_date)->format('F d, Y') : 'N/A';
        $endDate = $intern->end_date ? \Carbon\Carbon::parse($intern->end_date)->format('F d, Y') : 'N/A';
        $issueDateFormatted = $date ? $date->format('jS \d\a\y \o\f F Y') : now()->format('jS \d\a\y \o\f F Y');

        $logoUrl = $this->logo_path ? public_path('storage/' . $this->logo_path) : public_path('images/Deped Logo.png');
        $footerUrl = $this->footer_path ? public_path('storage/' . $this->footer_path) : public_path('images/certificate-footer.png');

        $replacements = [
            '{logo}' => $logoUrl,
            '{footer}' => $footerUrl,
            '{gov_text}' => $this->gov_text ?? 'Republic of the Philippines',
            '{dept_text}' => $this->dept_text ?? 'Department of Education',
            '{region_text}' => $this->region_text ?? 'NATIONAL CAPITAL REGION',
            '{office_text}' => $this->office_text ?? 'SCHOOLS DIVISION OFFICE QUEZON CITY',
            '{title}' => $this->title_text ?? 'CERTIFICATE OF COMPLETION',
            '{name}' => trim(($intern->first_name ?? '') . ' ' . ($intern->last_name ?? '')),
            '{article}' => $article,
            '{course}' => $courseStr,
            '{school}' => $intern->school ?? 'FEU-TECH',
            '{hours}' => (int)($certificate->completed_hours ?? 0),
            '{start_date}' => $startDate,
            '{end_date}' => $endDate,
            '{unit}' => $certificate->intern_unit ?? 'Human Resource Teaching Unit',
            '{issue_date}' => $issueDateFormatted,
            '{verification_code}' => $verificationCode,
            '{signatory_name}' => $this->signatory_name ?? 'MICHELLE A. MAL-IN',
            '{signatory_title}' => $this->signatory_title ?? 'Administrative Officer IV',
            '{initials}' => $this->initials ?? 'HRNTP/rocelle/Pj',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $html);
    }
}
