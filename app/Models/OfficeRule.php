<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfficeRule extends Model
{
    // Disable timestamps since they weren't in your migration
    public $timestamps = false;

    protected $fillable = [
        'office_id',
        'require_rule',
        'rule_value',
        'priority',
    ];

    /**
     * Get the office that owns the rule.
     */
    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }
}
