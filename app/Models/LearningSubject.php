<?php

namespace App\Models;

use App\Enum\Learning\ContentStatusEnum;
use App\Enum\Learning\LevelEnum;
use App\Enum\Learning\MadhhabEnum;
use App\Utilities\Traits\HasListingScopes;
use App\Utilities\Traits\HasLocalizedAttributes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** A subject / book inside a learning section (e.g. فقه الطهارة — المالكي). */
class LearningSubject extends Model
{
    use HasListingScopes;
    use HasLocalizedAttributes;

    protected $fillable = [
        'learning_section_id',
        'title_ar',
        'title_en',
        'description_ar',
        'description_en',
        'author_ar',
        'author_en',
        'madhhab',
        'level',
        'cover_image',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'madhhab' => MadhhabEnum::class,
        'level' => LevelEnum::class,
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(LearningSection::class, 'learning_section_id');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }

    public function publishedLessons(): HasMany
    {
        return $this->lessons()->where('status', ContentStatusEnum::PUBLISHED->value);
    }

    public function scopeOfMadhhab(Builder $query, MadhhabEnum|string $madhhab): Builder
    {
        return $query->where('madhhab', $madhhab instanceof MadhhabEnum ? $madhhab->value : $madhhab);
    }
}
