<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupervisorDetail extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'office_id',
        'first_name',
        'middle_name',
        'last_name',
        'profile_picture',
        'phone_number',
        'is_hr_head',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function office()
    {
        return $this->belongsTo(Office::class, 'office_id');
    }
}
