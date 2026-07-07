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
    ];

    /**
     * Get the office associated with this match result
     */
    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }
}
