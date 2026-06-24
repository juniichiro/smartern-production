<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use App\Traits\HasShortRouteKey;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasShortRouteKey, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = ['role_id', 'display_id', 'public_id', 'email', 'password', 'google2fa_secret', 'google2fa_enabled', 'failed_attempts', 'locked_until', 'is_first_login', 'privacy_acknowledged_at'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'locked_until' => 'datetime',
            'password' => 'hashed',
            'role_id' => UserRole::class,
            'google2fa_secret' => 'encrypted',
            'google2fa_enabled' => 'boolean',
            'privacy_acknowledged_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope('hide_super_admin', function ($builder) {
            $authId = null;
            if (session() && app()->bound('auth')) {
                try {
                    $guard = \Illuminate\Support\Facades\Auth::guard('web');
                    if (method_exists($guard, 'getName')) {
                        $authId = session()->get($guard->getName());
                    }
                } catch (\Exception $e) {
                    // Safe fallback if session is not started or guard is not bound
                }
            }

            if ($authId) {
                $builder->where(function ($q) use ($authId) {
                    $q->where('role_id', '!=', UserRole::SUPER_ADMIN->value)
                      ->orWhere('id', '=', $authId);
                });
            } else {
                $builder->where('role_id', '!=', UserRole::SUPER_ADMIN->value);
            }
        });

        static::creating(function ($user) {
            $roleVal = $user->role_id instanceof UserRole ? $user->role_id->value : (int)$user->role_id;
            if ($roleVal === UserRole::SUPER_ADMIN->value) {
                $exists = self::withoutGlobalScopes()->where('role_id', UserRole::SUPER_ADMIN->value)->exists();
                if ($exists) {
                    throw new \Exception('A Super Admin account already exists. Only one is allowed.');
                }
            }
            if (empty($user->display_id)) {
                $user->display_id = static::generateDisplayId($user->role_id);
            }
            if (empty($user->public_id)) {
                $user->public_id = hash('sha256', $user->display_id);
            }
        });

        static::updating(function ($user) {
            if ($user->isDirty('role_id')) {
                $roleVal = $user->role_id instanceof UserRole ? $user->role_id->value : (int)$user->role_id;
                if ($roleVal === UserRole::SUPER_ADMIN->value) {
                    $exists = self::withoutGlobalScopes()->where('role_id', UserRole::SUPER_ADMIN->value)->where('id', '!=', $user->id)->exists();
                    if ($exists) {
                        throw new \Exception('A Super Admin account already exists. Only one is allowed.');
                    }
                }
            }
        });
    }

    public static function generateDisplayId($roleId, $year = null): string
    {
        $year = $year ?? now()->format('Y');
        $roleVal = $roleId instanceof UserRole ? $roleId->value : (int) $roleId;

        $base = match ($roleVal) {
            1 => 101,      // Admin: 3 digits
            2 => 201,      // Head Supervisor: 3 digits
            3 => 3001,     // Supervisor: 4 digits
            4 => 4001,     // School Coordinator: 4 digits
            5 => 50001,    // Intern: 5 digits
            6 => 601,      // Super Admin: 3 digits
            default => 50001
        };

        // Query existing users with display_ids matching the pattern for this role and year
        $lastUser = self::withoutGlobalScopes()
            ->where('role_id', $roleVal)
            ->whereNotNull('display_id')
            ->where('display_id', 'like', "{$year}%")
            ->orderBy('display_id', 'desc')
            ->first();

        if ($lastUser) {
            $lastDisplayId = $lastUser->display_id;
            $yearLength = strlen($year);
            $suffixStr = substr((string) $lastDisplayId, $yearLength);
            $nextSuffixVal = ((int) $suffixStr) + 1;

            return $year.$nextSuffixVal;
        }

        return $year.$base;
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class)->withoutGlobalScope('hide_super_admin_role');
    }

    public function internDetails()
    {
        return $this->hasOne(InternDetail::class);
    }

    public function supervisorDetails()
    {
        return $this->hasOne(SupervisorDetail::class);
    }

    public function schoolCoordinatorDetails()
    {
        return $this->hasOne(SchoolCoordinatorDetail::class);
    }

    /**
     * Get the user's name dynamically.
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->internDetails) {
                    return $this->internDetails->first_name.' '.$this->internDetails->last_name;
                }
                if ($this->supervisorDetails) {
                    return $this->supervisorDetails->first_name.' '.$this->supervisorDetails->last_name;
                }
                if ($this->schoolCoordinatorDetails) {
                    return $this->schoolCoordinatorDetails->first_name.' '.$this->schoolCoordinatorDetails->last_name;
                }

                return 'System User';
            },
            set: function ($value) {
                $parts = explode(' ', $value, 2);
                $firstName = $parts[0] ?? '';
                $lastName = $parts[1] ?? '';

                if ($this->internDetails) {
                    $this->internDetails->update([
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                    ]);
                } elseif ($this->supervisorDetails) {
                    $this->supervisorDetails->update([
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                    ]);
                } elseif ($this->schoolCoordinatorDetails) {
                    $this->schoolCoordinatorDetails->update([
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                    ]);
                }

                return [];
            }
        );
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token): void
    {
        \Illuminate\Support\Facades\Mail::to($this->email)->send(new \App\Mail\ResetPasswordMail($this, $token));
    }
}
