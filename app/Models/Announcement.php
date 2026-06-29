<?php

namespace App\Models;

use App\Traits\HasShortRouteKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    use HasFactory, HasShortRouteKey;

    protected $fillable = [
        'user_id',
        'office_id',
        'title',
        'content',
        'attachment',
    ];

    /**
     * Get the user (Supervisor) who created the announcement.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the office this announcement is scoped to (null if global).
     */
    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    /**
     * Helper to get the specific supervisor details through the user.
     * This assumes your User model has a supervisorDetails relationship.
     */
    public function supervisor()
    {
        return $this->user->supervisorDetails();
    }
}
