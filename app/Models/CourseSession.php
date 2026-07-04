<?php

namespace App\Models;

use App\Enum\Course\CourseTypeEnum;
use App\Observers\CourseSessionObserver;
use App\Utilities\Traits\HasLocalizedAttributes;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy(CourseSessionObserver::class)]
class CourseSession extends Model
{
    use HasLocalizedAttributes;

    protected $fillable = [
        'course_id',
        'starts_at',
        'duration_minutes',
        'capacity',
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
        'capacity' => 'integer',
        'is_active' => 'boolean',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function isOnline(): bool
    {
        return $this->course?->type === CourseTypeEnum::ONLINE;
    }

    /**
     * Bookings that occupy a seat (everything except cancelled).
     */
    public function activeBookingsCount(): int
    {
        return $this->bookings()
            ->where('status', '!=', \App\Enum\Booking\BookingStatusEnum::CANCELLED->value)
            ->count();
    }

    public function seatsLeft(): ?int
    {
        if ($this->capacity === null) {
            return null; // unlimited
        }

        return max(0, $this->capacity - $this->activeBookingsCount());
    }

    public function isFull(): bool
    {
        $left = $this->seatsLeft();

        return $left !== null && $left <= 0;
    }
}
