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

    /**
     * Dynamically sanitize content on retrieval to neutralize legacy stored XSS.
     */
    protected function content(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: function (?string $value) {
                if (!$value) {
                    return $value;
                }

                $config = \HTMLPurifier_Config::createDefault();
                $config->set('HTML.Allowed', 'p[style],b[style],strong[style],i[style],em[style],u[style],a[href|target|title|rel|style],ul[style],ol[style],li[style],h1[style],h2[style],h3[style],h4[style],h5[style],h6[style],br,span[style],blockquote[style],pre[style],code[style]');
                $config->set('CSS.AllowedProperties', 'font-weight,font-style,text-decoration,color,background-color,font-family,font-size,text-align,list-style-type');
                $config->set('Attr.AllowedFrameTargets', ['_blank', '_self', '_parent', '_top']);

                $purifier = new \HTMLPurifier($config);
                return $purifier->purify($value);
            }
        );
    }
}
