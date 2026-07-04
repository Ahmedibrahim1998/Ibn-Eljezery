<?php

namespace App\Models;

use App\Enum\Booking\AttendanceEnum;
use App\Enum\Booking\BookingStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $fillable = [
        'course_session_id',
        'name',
        'phone',
        'email',
        'notes',
        'status',
        'attendance',
    ];

    protected $casts = [
        'status' => BookingStatusEnum::class,
        'attendance' => AttendanceEnum::class,
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(CourseSession::class, 'course_session_id');
    }
}
