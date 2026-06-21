<?php

namespace App\Models;

use App\Traits\HasShortRouteKey;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AssessmentItem extends Model
{
    use HasShortRouteKey;

    protected $fillable = [
        'category',
        'competency_text',
        'is_active',
        'order_index',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Scope a query to only include active assessment items.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to order items by category and order_index.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('category')->orderBy('order_index');
    }
}
