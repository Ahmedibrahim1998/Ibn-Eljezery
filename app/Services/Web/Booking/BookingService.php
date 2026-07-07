<?php

namespace App\Services\Web\Booking;

use App\Enum\Booking\BookingStatusEnum;
use App\Exceptions\Booking\AlreadyBookedException;
use App\Http\DTOs\Web\Booking\StoreBookingDTO;
use App\Models\Booking;
use App\Models\Course;

class BookingService
{
    /**
     * Enroll a student in a course (once). Prevents duplicate enrollment
     * by phone for the same course.
     *
     * @throws AlreadyBookedException
     */
    public function store(StoreBookingDTO $dto): Booking
    {
        /** @var Course $course */
        $course = Course::query()->where('is_active', true)->enrollmentOpen()->findOrFail($dto->course_id);

        $alreadyEnrolled = Booking::query()
            ->where('course_id', $course->id)
            ->where('phone', $dto->phone)
            ->where('status', '!=', BookingStatusEnum::CANCELLED->value)
            ->exists();

        if ($alreadyEnrolled) {
            throw new AlreadyBookedException();
        }

        return Booking::create([
            'course_id' => $course->id,
            'name' => $dto->name,
            'phone' => $dto->phone,
            'email' => $dto->email,
            'notes' => $dto->notes,
            'status' => BookingStatusEnum::PENDING->value,
        ]);
    }
}
