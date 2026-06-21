<?php

namespace App\Models;

use App\Traits\HasShortRouteKey;
use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
    use HasShortRouteKey;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'location_data',
        'latitude',
        'longitude',
        'radius_meters',
        'qr_attendance_enabled',
        'intern_limit',
    ];

    protected $casts = [
        'location_data' => 'array',
        'qr_attendance_enabled' => 'boolean',
    ];

    // Relationship: One Office can have many Interns
    public function internDetails()
    {
        return $this->hasMany(InternDetail::class, 'office_id');
    }

    // Relationship: One Office can have many Supervisors
    public function supervisorDetails()
    {
        return $this->hasMany(SupervisorDetail::class, 'office_id');
    }
}
