<?php

namespace App\Models;

use App\Enum\Course\CourseTypeEnum;
use App\Utilities\Traits\HasListingScopes;
use App\Utilities\Traits\HasLocalizedAttributes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Course extends Model
{
    use HasListingScopes;
    use HasLocalizedAttributes;

    protected $fillable = [
        'course_category_id',
        'teacher_id',
        'supervisor_id',
        'type',
        'title_ar',
        'title_en',
        'description_ar',
        'description_en',
        'badge_ar',
        'badge_en',
        'duration_months',
        'enrollment_deadline',
        'items',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'type' => CourseTypeEnum::class,
        'items' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'duration_months' => 'integer',
        'enrollment_deadline' => 'date',
    ];

    protected static function booted(): void
    {
        // A group inherits its type (offline/online) from its parent course.
        static::saving(function (Course $course): void {
            if ($course->course_category_id) {
                $category = $course->relationLoaded('category')
                    ? $course->category
                    : CourseCategory::find($course->course_category_id);

                if ($category) {
                    $course->type = $category->type;
                }
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CourseCategory::class, 'course_category_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function sessions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CourseSession::class);
    }

    /** Student enrollments in this course. */
    public function bookings(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /** Courses still within their advertising / enrollment window. */
    public function scopeEnrollmentOpen(Builder $query): Builder
    {
        return $query->where(function (Builder $q): void {
            $q->whereNull('enrollment_deadline')
                ->orWhereDate('enrollment_deadline', '>=', today());
        });
    }

    /** Whether the course is still open for enrollment today. */
    public function enrollmentOpen(): bool
    {
        return $this->enrollment_deadline === null
            || $this->enrollment_deadline->startOfDay()->gte(today());
    }

    /** Whole days left before the enrollment window closes (null if no deadline). */
    public function daysLeft(): ?int
    {
        if ($this->enrollment_deadline === null) {
            return null;
        }

        return (int) today()->diffInDays($this->enrollment_deadline->startOfDay(), false);
    }

    /** Locale-aware "X days left" phrase for the countdown badge (null if none/closed). */
    public function daysLeftLabel(): ?string
    {
        $d = $this->daysLeft();

        if ($d === null || $d < 0) {
            return null;
        }

        $ar = app()->getLocale() === 'ar';

        if ($d === 0) {
            return $ar ? 'آخر يوم للتسجيل' : 'Last day to enroll';
        }

        if ($ar) {
            return match (true) {
                $d === 1 => 'باقي يوم واحد',
                $d === 2 => 'باقي يومان',
                $d <= 10 => "باقي {$d} أيام",
                default => "باقي {$d} يومًا",
            };
        }

        return $d === 1 ? '1 day left' : "{$d} days left";
    }

    /** Human, locale-aware course length, e.g. "5 أشهر" / "5 months". */
    public function durationLabel(): ?string
    {
        $n = $this->duration_months;

        if (! $n) {
            return null;
        }

        if (app()->getLocale() === 'ar') {
            return match (true) {
                $n === 1 => 'شهر',
                $n === 2 => 'شهران',
                $n <= 10 => "{$n} أشهر",
                default => "{$n} شهرًا",
            };
        }

        return $n === 1 ? 'One month' : "{$n} months";
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
