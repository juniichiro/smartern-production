<?php

namespace App\Models;

use App\Traits\HasShortRouteKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificate extends Model
{
    use HasFactory, HasShortRouteKey;

    protected $primaryKey = 'certificate_id';

    protected $fillable = [
        'intern_id',
        'supervisor_id',
        'head_supervisor_id',
        'status',
        'verification_status',
        'completed_hours',
        'start_date',
        'end_date',
        'issue_date',
        'intern_unit',
        'intern_name',      // Snapshot
        'intern_course',    // Snapshot
        'intern_school',    // Snapshot
        'file_path',
        'verification_code',
        'published_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'issue_date' => 'date',
        'published_at' => 'datetime',
        'completed_hours' => 'integer',
    ];

    // Relationship to User (for email, role, etc.)
    public function intern()
    {
        // Ensure this links 'intern_id' to the 'user_id' in InternDetail
        return $this->belongsTo(InternDetail::class, 'intern_id', 'user_id');
    }

    public function officeSupervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function headSupervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'head_supervisor_id');
    }

    // Relationship to InternDetail (for name, course, school)
    public function internDetails(): BelongsTo
    {
        return $this->belongsTo(InternDetail::class, 'intern_id', 'user_id');
    }

    // Helper: Get intern's full name
    public function getInternNameAttribute(): string
    {
        if ($this->internDetails) {
            return trim($this->internDetails->first_name.' '.$this->internDetails->last_name);
        }

        return 'Unknown Intern';
    }

    // Helper methods for status
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isEndorsed(): bool
    {
        return $this->status === 'endorsed';
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isVerified(): bool
    {
        return $this->verification_status === 'verified';
    }

    public function canBeVerified(): bool
    {
        return $this->verification_status === 'pending';
    }

    public function canBePublished(): bool
    {
        return $this->isEndorsed() && $this->isVerified();
    }
}
