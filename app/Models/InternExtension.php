<?php

namespace App\Models;

use App\Traits\HasShortRouteKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternExtension extends Model
{
    use HasFactory, HasShortRouteKey;

    protected $fillable = [
        'intern_detail_id',
        'previous_end_date',
        'new_end_date',
        'reason',
        'status',
        'resolved_by',
        'rejection_note',
    ];

    protected $casts = [
        'previous_end_date' => 'date:Y-m-d',
        'new_end_date' => 'date:Y-m-d',
    ];

    public function internDetail(): BelongsTo
    {
        return $this->belongsTo(InternDetail::class, 'intern_detail_id');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
