<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'intern_id',
        'content',
        'file_path',
        'original_filename',
    ];

    /**
     * Get the task that this submission belongs to.
     */
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Get the intern (User) who made the submission.
     */
    public function intern()
    {
        return $this->belongsTo(User::class, 'intern_id');
    }
}
