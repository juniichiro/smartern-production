<?php

namespace App\Models;

use App\Traits\HasShortRouteKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory, HasShortRouteKey;

    protected $primaryKey = 'notification_id';

    protected $fillable = [
        'recipient_id',
        'sender_id',
        'type',
        'category',
        'message',
        'target_url', // Added this
        'is_read',
        'is_external',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'is_external' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function senderInternDetails(): BelongsTo
    {
        return $this->belongsTo(InternDetail::class, 'sender_id', 'user_id');
    }

    public function senderSupervisorDetails(): BelongsTo
    {
        return $this->belongsTo(SupervisorDetail::class, 'sender_id', 'user_id');
    }
}
