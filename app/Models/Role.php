<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    // Disable timestamps if you didn't include them in the migration
    public $timestamps = false;

    protected $fillable = ['name'];

    /**
     * A role can be assigned to many users.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
