<?php

namespace App\Models;

use App\Utilities\Traits\HasListingScopes;
use App\Utilities\Traits\HasLocalizedAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A top-level learning section in the mobile app (e.g. الفقه, التجويد).
 * It contains subjects, which contain lessons.
 */
class LearningSection extends Model
{
    use HasListingScopes;
    use HasLocalizedAttributes;

    protected $fillable = [
        'slug',
        'title_ar',
        'title_en',
        'description_ar',
        'description_en',
        'icon',
        'color',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function subjects(): HasMany
    {
        return $this->hasMany(LearningSubject::class);
    }
}
