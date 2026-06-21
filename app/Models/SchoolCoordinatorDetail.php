<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolCoordinatorDetail extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'first_name',
        'middle_name',
        'last_name',
        'profile_picture',
        'school',
        'phone_number',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'school_coordinator_detail_id');
    }
}
