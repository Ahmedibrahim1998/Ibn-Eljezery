<?php

namespace App\Repositories\Booking;

use App\Foundation\Repositories\RepositoryInterface;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface BookingRepositoryInterface extends RepositoryInterface
{
    /** The student's app enrollments (newest first), with course + teacher + upcoming sessions. */
    public function forStudent(User $user): Collection;

    /** Whether a non-cancelled enrollment exists for this course by account or phone. */
    public function isEnrolled(Course $course, ?int $userId, string $phone): bool;

    /** Students of a course (for its teacher), pending first then by name. */
    public function forCourse(Course $course): Collection;
}
