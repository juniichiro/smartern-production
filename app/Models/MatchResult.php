<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchResult extends Model
{
    protected $fillable = [
        'applicant_name',
        'school',
        'office_id',
        'score',
        'confidence',
        'status',
        'is_confirmed',
        'breakdown',
        'requested_by_supervisor',
    ];

    protected $casts = [
        'is_confirmed' => 'boolean',
        'requested_by_supervisor' => 'boolean',
    ];

    /**
     * Get the office associated with this match result
     */
    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }
}
