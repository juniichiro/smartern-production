<?php

namespace App\Models;

use App\Enums\TaskStatus;
use App\Traits\HasShortRouteKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory, HasShortRouteKey;

    protected $fillable = [
        'supervisor_id',
        'intern_id',
        'office_id',
        'title',
        'description',
        'remarks',
        'due_date',
        'status',
        'is_file_required',
        'file_path',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => TaskStatus::class,
            'due_date' => 'datetime',
            'is_file_required' => 'boolean',
        ];
    }

    public function internDetails(): BelongsTo
    {
        return $this->belongsTo(InternDetail::class, 'intern_id', 'user_id');
    }

    public function supervisor()
    {
        // This links supervisor_id on the tasks table to id on the users table
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'office_id');
    }

    /**
     * Get all submissions for the task.
     */
    public function submissions()
    {
        // We order by latest so the most recent work appears first
        return $this->hasMany(Submission::class)->latest();
    }
}
