<?php

namespace App\Models;

use App\Enum\Course\CourseTypeEnum;
use App\Utilities\Traits\HasListingScopes;
use App\Utilities\Traits\HasLocalizedAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Course extends Model
{
    use HasListingScopes;
    use HasLocalizedAttributes;

    protected $fillable = [
        'teacher_id',
        'type',
        'title_ar',
        'title_en',
        'description_ar',
        'description_en',
        'badge_ar',
        'badge_en',
        'items',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'type' => CourseTypeEnum::class,
        'items' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function sessions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CourseSession::class);
    }

    /**
     * Localized bullet items for the current locale.
     *
     * @return array<int, string>
     */
    public function localizedItems(): array
    {
        $key = app()->getLocale() === 'ar' ? 'ar' : 'en';

        return collect($this->items ?? [])
            ->map(fn (array $item) => $item[$key] ?? $item['en'] ?? $item['ar'] ?? null)
            ->filter()
            ->values()
            ->all();
    }
}
