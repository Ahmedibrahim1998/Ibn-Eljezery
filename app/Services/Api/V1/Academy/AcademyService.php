<?php

namespace App\Services\Api\V1\Academy;

use App\Enum\Booking\BookingStatusEnum;
use App\Enum\Course\CourseTypeEnum;
use App\Exceptions\Booking\AlreadyBookedException;
use App\Models\Booking;
use App\Models\Course;
use App\Models\Teacher;
use App\Models\User;
use App\Repositories\Booking\BookingRepositoryInterface;
use App\Repositories\Course\CourseRepositoryInterface;
use App\Repositories\Teacher\TeacherRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * The academy in the mobile app: browse open groups, enroll, and see
 * "my courses" (student) or "my groups + students" (teacher).
 *
 * Zoom links are only handed to students whose enrollment was CONFIRMED by a
 * supervisor (see MyBookingResource). The teacher's WhatsApp is public so a
 * student can ask before booking (the academy number when the teacher has none).
 */
class AcademyService
{
    public function __construct(
        private readonly CourseRepositoryInterface $courses,
        private readonly BookingRepositoryInterface $bookings,
        private readonly TeacherRepositoryInterface $teachers,
    ) {}

    public function teachers(): Collection
    {
        return $this->teachers->activeWithOpenCourses();
    }

    public function teacher(int $id): ?Teacher
    {
        return $this->teachers->findActiveWithOpenCourses($id);
    }

    public function courses(?CourseTypeEnum $type = null): Collection
    {
        return $this->courses->openForApp($type);
    }

    public function course(int $id): ?Course
    {
        return $this->courses->findOpenForApp($id);
    }

    public function isEnrolled(Course $course, User $user): bool
    {
        return $this->bookings->isEnrolled($course, $user->id, (string) $user->phone);
    }

    /**
     * Enroll the signed-in student (pending until a supervisor confirms).
     *
     * @throws AlreadyBookedException
     */
    public function book(Course $course, User $user, ?string $notes = null): Booking
    {
        if ($this->isEnrolled($course, $user)) {
            throw new AlreadyBookedException();
        }

        /** @var Booking */
        return $this->bookings->create([
            'course_id' => $course->id,
            'user_id' => $user->id,
            'name' => $user->name,
            'phone' => $user->phone,
            'email' => $user->email,
            'notes' => $notes,
            'status' => BookingStatusEnum::PENDING->value,
        ]);
    }

    public function myBookings(User $user): Collection
    {
        return $this->bookings->forStudent($user);
    }

    public function teacherCourses(User $user): Collection
    {
        abort_unless($user->teacher, 403);

        return $this->courses->forTeacher($user->teacher);
    }

    /** Students of one of the teacher's own groups. */
    public function teacherStudents(User $user, int $courseId): Collection
    {
        /** @var Course|null $course */
        $course = $this->courses->find($courseId);
        abort_unless($course && $user->teacher && $course->teacher_id === $user->teacher->id, 404);

        return $this->bookings->forCourse($course);
    }
}
