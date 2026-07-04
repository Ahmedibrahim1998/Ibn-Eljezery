<?php

namespace App\Models;

use App\Utilities\Traits\HasListingScopes;
use App\Utilities\Traits\HasLocalizedAttributes;
use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    use HasListingScopes;
    use HasLocalizedAttributes;

    protected $fillable = [
        'title_ar',
        'title_en',
        'subtitle_ar',
        'subtitle_en',
        'price',
        'currency_ar',
        'currency_en',
        'features',
        'badge_ar',
        'badge_en',
        'is_featured',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'features' => 'array',
        'price' => 'decimal:2',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Localized feature lines for the current locale.
     * Each stored item is shaped like ['ar' => '...', 'en' => '...'].
     *
     * @return array<int, string>
     */
    public function localizedFeatures(): array
    {
        $key = app()->getLocale() === 'ar' ? 'ar' : 'en';

        return collect($this->features ?? [])
            ->map(fn (array $item) => $item[$key] ?? $item['en'] ?? $item['ar'] ?? null)
            ->filter()
            ->values()
            ->all();
    }
}
