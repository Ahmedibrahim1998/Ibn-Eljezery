<?php

namespace App\Repositories\Booking;

use App\Enum\Booking\BookingStatusEnum;
use App\Foundation\Repositories\Repository;
use App\Models\Booking;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class BookingRepository extends Repository implements BookingRepositoryInterface
{
    public function __construct(Booking $model)
    {
        parent::__construct($model);
    }

    public function forStudent(User $user): Collection
    {
        return $this->getModel()->newQuery()
            ->where('user_id', $user->id)
            ->with(['course.teacher', 'course.category', 'course.sessions' => fn ($q) => $q->upcoming()])
            ->latest()
            ->get();
    }

    public function isEnrolled(Course $course, ?int $userId, string $phone): bool
    {
        return $this->getModel()->newQuery()
            ->where('course_id', $course->id)
            ->where('status', '!=', BookingStatusEnum::CANCELLED->value)
            ->where(fn ($q) => $q->where('phone', $phone)->when($userId, fn ($q) => $q->orWhere('user_id', $userId)))
            ->exists();
    }

    public function forCourse(Course $course): Collection
    {
        return $this->getModel()->newQuery()
            ->where('course_id', $course->id)
            ->where('status', '!=', BookingStatusEnum::CANCELLED->value)
            ->orderByRaw('CASE WHEN status = ? THEN 0 ELSE 1 END', [BookingStatusEnum::PENDING->value])
            ->orderBy('name')
            ->get();
    }
}
