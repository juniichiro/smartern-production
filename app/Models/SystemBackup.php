<?php

namespace App\Models;

use App\Traits\HasShortRouteKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemBackup extends Model
{
    use HasFactory, HasShortRouteKey;

    protected $primaryKey = 'backup_id';

    protected $fillable = [
        'admin_id',
        'backup_type',
        'file_path',
        'file_size',
        'status',
        'sha256_hash',
    ];

    /**
     * Get the user (admin) who triggered the backup.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
