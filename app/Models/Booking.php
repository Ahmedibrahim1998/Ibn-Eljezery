<?php

namespace App\Models;

use App\Enum\Booking\BookingStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A Booking is a student's ENROLLMENT in a course (created once),
 * not a single-session reservation. Attendance is tracked per class
 * in the related `attendances` rows.
 */
class Booking extends Model
{
    /** Attended classes required before a new month's subscription is due. */
    public const CLASSES_PER_MONTH = 8;

    protected $fillable = [
        'course_id',
        'name',
        'phone',
        'email',
        'notes',
        'status',
        'months_paid',
    ];

    protected $casts = [
        'status' => BookingStatusEnum::class,
        'months_paid' => 'integer',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function paymentLogs(): HasMany
    {
        return $this->hasMany(PaymentLog::class);
    }

    /** Total attended classes (days). */
    public function attendedDays(): int
    {
        return $this->attendances()->whereNotNull('checked_in_at')->count();
    }

    /** How many months (of 8 classes each) have been earned by attendance. */
    public function monthsEarned(): int
    {
        return intdiv($this->attendedDays(), self::CLASSES_PER_MONTH);
    }

    /** A month's subscription is due when more months were earned than paid. */
    public function paymentDue(): bool
    {
        return $this->monthsEarned() > $this->months_paid;
    }

    /** Today's attendance row, if any. */
    public function todayAttendance(): ?Attendance
    {
        return $this->attendances()->whereDate('attended_on', today())->first();
    }

    /**
     * Record a monthly-subscription payment: increment the paid counter and
     * write a snapshot to the payment log. Returns the created log row.
     */
    public function recordPayment(?int $supervisorId = null): PaymentLog
    {
        $this->increment('months_paid');
        $this->loadMissing('course');

        return $this->paymentLogs()->create([
            'course_id' => $this->course_id,
            'supervisor_id' => $supervisorId,
            'course_title_ar' => $this->course?->title_ar,
            'course_title_en' => $this->course?->title_en,
            'student_name' => $this->name,
            'student_phone' => $this->phone,
            'month_number' => $this->months_paid,
            'classes_attended' => $this->attendedDays(),
            'paid_at' => now(),
        ]);
    }
}
