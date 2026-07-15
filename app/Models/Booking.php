<?php

namespace App\Models;

use App\Enum\Booking\BookingStatusEnum;
use App\Enum\Booking\StudentStatusEnum;
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
        'student_status',
        'monthly_fee',
        'notes',
        'status',
        'months_paid',
        'last_paid_at',
    ];

    protected $casts = [
        'status' => BookingStatusEnum::class,
        'student_status' => StudentStatusEnum::class,
        'monthly_fee' => 'decimal:2',
        'months_paid' => 'integer',
        'last_paid_at' => 'datetime',
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

    /**
     * Total attended CLASSES. A single day may count for one class or two
     * (a "double" class), so we sum `classes_count` rather than counting rows.
     */
    public function attendedDays(): int
    {
        return (int) $this->attendances()->whereNotNull('checked_in_at')->sum('classes_count');
    }

    /** Whether the student is exempt from subscription fees (orphan / poor). */
    public function isExempt(): bool
    {
        return $this->student_status?->isExempt() ?? false;
    }

    /**
     * Months of subscription required so far. Payment is UP-FRONT: the first
     * class already requires month 1, and every further 8 classes requires the
     * next month (classes 1-8 = month 1, 9-16 = month 2, ...).
     */
    public function monthsRequired(): int
    {
        $attended = $this->attendedDays();

        return $attended === 0 ? 0 : intdiv($attended - 1, self::CLASSES_PER_MONTH) + 1;
    }

    /** Payment is due when a required month hasn't been paid — never for exempt students. */
    public function paymentDue(): bool
    {
        if ($this->isExempt()) {
            return false;
        }

        return $this->monthsRequired() > $this->months_paid;
    }

    /** Localized month name of the last recorded payment, e.g. "يناير" (null if none). */
    public function lastPaidMonthLabel(): ?string
    {
        return $this->last_paid_at
            ? $this->last_paid_at->locale(app()->getLocale())->translatedFormat('F')
            : null;
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
        $this->update(['last_paid_at' => now()]);
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
            'amount' => $this->monthly_fee,
            'paid_at' => now(),
        ]);
    }
}
