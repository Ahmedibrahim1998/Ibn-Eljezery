<?php

namespace App\Http\Resources\Api\V1\Academy;

use App\Enum\Booking\BookingStatusEnum;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * One of the student's enrollments. The Zoom join links and the teacher's
 * WhatsApp only appear once a supervisor has CONFIRMED the enrollment.
 *
 * @mixin Booking
 */
class MyBookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $confirmed = $this->status === BookingStatusEnum::CONFIRMED;
        $course = $this->course;

        return [
            'id' => $this->id,
            'status' => $this->status?->value,
            'status_label' => $this->status?->getLabel(),
            'created_at' => $this->created_at?->toIso8601String(),
            'attended_classes' => $this->attendedDays(),
            'payment_due' => $this->paymentDue(),
            'course' => $course ? new CourseResource($course) : null,
            'sessions' => $course
                ? $course->sessions->map(fn ($s) => new CourseSessionResource($s, withJoinUrl: $confirmed))
                : [],
            'teacher_whatsapp_url' => $confirmed ? $course?->teacher?->whatsappUrl() : null,
        ];
    }
}
