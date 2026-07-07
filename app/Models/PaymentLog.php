<?php

namespace App\Models;

use App\Utilities\Traits\HasLocalizedAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A record of one monthly-subscription payment made by an enrolled student.
 * Written by the supervisor via the attendance panel. Snapshots the student
 * and course so history stays readable even after those records change.
 */
class PaymentLog extends Model
{
    use HasLocalizedAttributes;

    protected $fillable = [
        'booking_id',
        'course_id',
        'supervisor_id',
        'course_title_ar',
        'course_title_en',
        'student_name',
        'student_phone',
        'month_number',
        'classes_attended',
        'paid_at',
    ];

    protected $casts = [
        'month_number' => 'integer',
        'classes_attended' => 'integer',
        'paid_at' => 'datetime',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }
}
