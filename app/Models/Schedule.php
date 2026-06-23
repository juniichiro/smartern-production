<?php

namespace App\Models;

use App\Traits\HasShortRouteKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory, HasShortRouteKey;

    protected $fillable = [
        'date',
        'school_name',
        'type',
        'start_date',
        'target_end_date',
        'start_time',
        'end_time',
        'daily_hours',
        'rounding_mode',
        'late_policy',
        'description',
    ];

    protected $casts = [
        'date' => 'date',
        'start_date' => 'date',
        'target_end_date' => 'date',
    ];
}
