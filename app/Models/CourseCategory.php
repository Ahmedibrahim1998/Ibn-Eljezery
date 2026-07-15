<?php

namespace App\Models;

use App\Enum\Course\CourseTypeEnum;
use App\Utilities\Traits\HasListingScopes;
use App\Utilities\Traits\HasLocalizedAttributes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A "course" (parent): a named subject of a given type (offline/online).
 * It contains several "groups" (Course rows), one per teacher.
 */
class CourseCategory extends Model
{
    use HasListingScopes;
    use HasLocalizedAttributes;

    protected $fillable = [
        'type',
        'title_ar',
        'title_en',
        'description_ar',
        'description_en',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'type' => CourseTypeEnum::class,
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /** The teacher groups under this course. */
    public function groups(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function scopeOfType(Builder $query, CourseTypeEnum|string $type): Builder
    {
        return $query->where('type', $type instanceof CourseTypeEnum ? $type->value : $type);
    }

    /** Count of active groups (that are still within their enrollment window). */
    public function activeGroupsCount(): int
    {
        return $this->groups()->active()->enrollmentOpen()->count();
    }
}
