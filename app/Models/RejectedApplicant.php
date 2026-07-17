<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RejectedApplicant extends Model
{
    protected $fillable = [
        'full_name',
        'email',
        'phone_number',
        'rejected_at',
        'reason',
    ];

    protected $casts = [
        'rejected_at' => 'datetime',
    ];
}
