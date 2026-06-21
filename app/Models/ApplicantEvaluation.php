<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicantEvaluation extends Model
{
    protected $fillable = [
        'applicant_id',
        'head_supervisor_id',
        'interview_notes',
        'verified_skills',
        'behavioral_score',
        'is_recommended',
    ];

    protected $casts = [
        'verified_skills' => 'array',
        'is_recommended' => 'boolean',
    ];

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(OnboardingApplicant::class, 'applicant_id');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'head_supervisor_id');
    }
}
