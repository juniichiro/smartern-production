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

    protected static function booted(): void
    {
        static::addGlobalScope('hide_super_admin_role', function ($builder) {
            $builder->where('id', '!=', \App\Enums\UserRole::SUPER_ADMIN->value);
        });
    }

    /**
     * A role can be assigned to many users.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
