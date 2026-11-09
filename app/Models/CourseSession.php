<?php

namespace App\Models;

use App\Enum\Course\CourseTypeEnum;
use App\Observers\CourseSessionObserver;
use App\Utilities\Traits\HasLocalizedAttributes;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A scheduled class (date/time) within a course — the course's schedule.
 * For online courses it may carry an auto-created Zoom meeting.
 * Attendance is NOT tracked here (see the Attendance model), so a course
 * can run many sessions over months while students enroll once.
 */
#[ObservedBy(CourseSessionObserver::class)]
class CourseSession extends Model
{
    use HasLocalizedAttributes;

    protected $fillable = [
        'course_id',
        'starts_at',
        'duration_minutes',
        'location_ar',
        'location_en',
        'zoom_meeting_id',
        'zoom_join_url',
        'zoom_start_url',
        'is_active',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'duration_minutes' => 'integer',
        'is_active' => 'boolean',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Active sessions that haven't finished yet, soonest first. A session that
     * started up to 3 hours ago still counts, so its Zoom link stays reachable
     * while the class is running.
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where('starts_at', '>=', now()->subHours(3))
            ->orderBy('starts_at');
    }

    public function isOnline(): bool
    {
        return $this->course?->type === CourseTypeEnum::ONLINE;
    }
}
