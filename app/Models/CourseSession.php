<?php

namespace App\Models;

use App\Enum\Course\CourseTypeEnum;
use App\Observers\CourseSessionObserver;
use App\Utilities\Traits\HasLocalizedAttributes;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
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

    public function isOnline(): bool
    {
        return $this->course?->type === CourseTypeEnum::ONLINE;
    }
}
