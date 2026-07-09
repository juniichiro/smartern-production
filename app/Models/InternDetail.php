<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Vinkla\Hashids\Facades\Hashids;

class InternDetail extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'office_id',
        'first_name',
        'middle_name',
        'last_name',
        'profile_picture',
        'course',
        'school',
        'gender',
        'gender_other_specify',
        'phone_number',
        'emergency_contact',
        'emergency_contact_number',
        'schedule_assignment',
        'hours_rendered',
        'required_hours',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'schedule_assignment' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relationship to Office
    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    // Relationship to Attendance (The missing piece)
    public function tasks()
    {
        // Link tasks.intern_id to intern_details.user_id
        return $this->hasMany(Task::class, 'intern_id', 'user_id');
    }

    public function attendances()
    {
        // Link attendances.intern_id to intern_details.user_id
        return $this->hasMany(Attendance::class, 'intern_id', 'user_id');
    }

    public function journals()
    {
        // Link journals.intern_id to intern_details.user_id
        return $this->hasMany(Journal::class, 'intern_id', 'user_id');
    }

    public function certificate()
    {
        // Adjust 'intern_id' and 'user_id' if your actual column names differ
        return $this->hasOne(Certificate::class, 'intern_id', 'user_id');
    }

    public function extensions()
    {
        return $this->hasMany(InternExtension::class, 'intern_detail_id');
    }

    /**
     * Scope a query to only include interns eligible for tasks
     * (Those who do not have a published certificate)
     */
    public function scopeEligibleForTasks($query)
    {
        return $query->whereDoesntHave('certificate', function ($q) {
            $q->where('status', 'published');
        });
    }

    /**
     * Scope a query to only include interns matching user_id, public_id, or intern_details id
     * Supports Hashids and encrypted IDs as well
     */
    public function scopeByPublicOrId($query, $id)
    {
        return $query->where(function ($q) use ($id) {
            try {
                // Attempt Hashids decoding first
                $decoded = is_string($id) ? Hashids::decode($id) : [];
                if (! empty($decoded)) {
                    $q->where('user_id', $decoded[0])
                        ->orWhere('id', $decoded[0]);

                    return;
                }

                // Attempt to decrypt if it looks like an encrypted string (legacy support)
                $decryptedId = is_string($id) ? decrypt($id) : null;
                if ($decryptedId !== null) {
                    $q->where('user_id', $decryptedId)
                        ->orWhere('id', $decryptedId);
                }
            } catch (\Throwable $e) {
                // Fallback to standard ID/Public ID/Display ID checks
                $q->where('id', $id)
                    ->orWhere('user_id', $id)
                    ->orWhereHas('user', function ($uq) use ($id) {
                        $uq->where('public_id', $id)
                            ->orWhere('display_id', $id);
                    });
            }
        });
    }

    /**
     * Get the student_id attribute, mapping it to the user's display_id
     */
    public function getStudentIdAttribute(): string
    {
        return $this->user->display_id ?? 'N/A';
    }
}
