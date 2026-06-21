<?php

namespace App\Models;

use App\Traits\HasShortRouteKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OnboardingApplicant extends Model
{
    use HasShortRouteKey;

    protected $fillable = [
        'applicant_id',
        'full_name',
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'university',
        'tech_skills',
        'soft_skills',
        'gender',
        'gender_other_specify',
        'phone_number',
        'profile_picture',
        'emergency_contact',
        'emergency_contact_number',
        'resume_path',
        'privacy_acknowledged_at',
    ];

    protected $casts = [
        'tech_skills' => 'array',
        'soft_skills' => 'array',
        'privacy_acknowledged_at' => 'datetime',
    ];

    public function evaluation(): HasOne
    {
        return $this->hasOne(ApplicantEvaluation::class, 'applicant_id');
    }
}
