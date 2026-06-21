<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    protected $primaryKey = 'attendance_id';

    protected $fillable = [
        'intern_id',
        'office_id',
        'clock_in',
        'clock_out',
        'latitude',
        'longitude',
        'status',
        'remarks',
    ];

    protected $casts = [
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
    ];

    public function internDetails(): BelongsTo
    {
        return $this->belongsTo(InternDetail::class, 'intern_id', 'user_id');
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'office_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'intern_id');
    }
}
