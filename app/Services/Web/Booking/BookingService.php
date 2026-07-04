<?php

namespace App\Services\Web\Booking;

use App\Enum\Booking\BookingStatusEnum;
use App\Exceptions\Booking\AlreadyBookedException;
use App\Exceptions\Booking\SessionFullException;
use App\Http\DTOs\Web\Booking\StoreBookingDTO;
use App\Models\Booking;
use App\Models\CourseSession;

class BookingService
{
    /**
     * Create a booking for a course session, enforcing capacity.
     *
     * @throws SessionFullException
     * @throws AlreadyBookedException
     */
    public function store(StoreBookingDTO $dto): Booking
    {
        /** @var CourseSession $session */
        $session = CourseSession::query()->findOrFail($dto->course_session_id);

        if (! $session->is_active || $session->isFull()) {
            throw new SessionFullException();
        }

        // Prevent the same student (by phone) from booking the same session twice.
        $alreadyBooked = Booking::query()
            ->where('course_session_id', $session->id)
            ->where('phone', $dto->phone)
            ->where('status', '!=', BookingStatusEnum::CANCELLED->value)
            ->exists();

        if ($alreadyBooked) {
            throw new AlreadyBookedException();
        }

        return Booking::create([
            'course_session_id' => $session->id,
            'name' => $dto->name,
            'phone' => $dto->phone,
            'email' => $dto->email,
            'notes' => $dto->notes,
            'status' => BookingStatusEnum::PENDING->value,
        ]);
    }
}
