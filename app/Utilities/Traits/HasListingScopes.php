<?php

namespace App\Utilities\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Common query scopes for content models that are sortable and toggleable.
 */
trait HasListingScopes
{
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
