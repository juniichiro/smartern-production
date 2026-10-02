<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class AuditLog extends Model
{
    use HasFactory;

    protected $primaryKey = 'log_id';

    public $timestamps = false; // Using custom 'timestamp' column instead

    protected $fillable = [
        'user_id',
        'action',
        'resource',
        'ip_address',
        'user_agent',
        'device',
        'anomaly_score',
        'timestamp',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'anomaly_score' => 'decimal:3',
    ];

    protected static function booted()
    {
        static::creating(function (AuditLog $log) {
            if (empty($log->device) && ! empty($log->user_agent)) {
                $log->device = static::parseDeviceFromUserAgent($log->user_agent);
            }
            Cache::forget('security_monitor_counts');
        });
    }

    public static function parseDeviceFromUserAgent(?string $userAgent): string
    {
        if (! $userAgent) {
            return 'Unknown';
        }

        $ua = strtolower($userAgent);

        $os = 'Other-OS';
        if (str_contains($ua, 'iphone') || str_contains($ua, 'ipad')) {
            $os = 'iOS';
        } elseif (str_contains($ua, 'android')) {
            $os = 'Android';
        } elseif (str_contains($ua, 'windows')) {
            $os = 'Windows';
        } elseif (str_contains($ua, 'macintosh') || str_contains($ua, 'mac os')) {
            $os = 'MacOS';
        } elseif (str_contains($ua, 'linux')) {
            $os = 'Linux';
        }

        $browser = 'Other-Browser';
        if (str_contains($ua, 'chrome') || str_contains($ua, 'crios')) {
            $browser = 'Chrome';
        } elseif (str_contains($ua, 'firefox')) {
            $browser = 'Firefox';
        } elseif (str_contains($ua, 'safari') && ! str_contains($ua, 'chrome')) {
            $browser = 'Safari';
        } elseif (str_contains($ua, 'edge')) {
            $browser = 'Edge';
        }

        return "{$os}|{$browser}";
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function internDetails(): BelongsTo
    {
        return $this->belongsTo(InternDetail::class, 'user_id', 'user_id');
    }

    public function supervisorDetails(): BelongsTo
    {
        return $this->belongsTo(SupervisorDetail::class, 'user_id', 'user_id');
    }

    /**
     * Get the anomaly severity classification based on configured thresholds.
     */
    public function getSeverityAttribute(): string
    {
        $score = (float) ($this->anomaly_score ?? 0);
        $crit = (float) config('security.thresholds.critical', 0.70);
        $warn = (float) config('security.thresholds.warning', 0.50);

        if ($score > $crit) {
            return 'critical';
        }
        if ($score > $warn) {
            return 'warning';
        }
        return 'normal';
    }

    /**
     * Scope query to a specific anomaly severity.
     */
    public function scopeSeverity($query, string $severity)
    {
        $crit = (float) config('security.thresholds.critical', 0.70);
        $warn = (float) config('security.thresholds.warning', 0.50);

        return match ($severity) {
            'critical' => $query->where('anomaly_score', '>', $crit),
            'warning' => $query->where('anomaly_score', '>', $warn)->where('anomaly_score', '<=', $crit),
            'normal' => $query->where('anomaly_score', '<=', $warn),
            default => $query,
        };
    }
}
