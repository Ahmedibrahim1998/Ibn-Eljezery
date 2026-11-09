<?php

namespace App\Repositories\Course;

use App\Enum\Booking\BookingStatusEnum;
use App\Enum\Course\CourseTypeEnum;
use App\Foundation\Repositories\Repository;
use App\Models\Course;
use App\Models\Teacher;
use App\Repositories\Concerns\ListsActiveOrdered;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CourseRepository extends Repository implements CourseRepositoryInterface
{
    use ListsActiveOrdered;

    public function __construct(Course $model)
    {
        parent::__construct($model);
    }

    // Public listings only show courses whose advertising window is still open
    // (no deadline, or a deadline that hasn't passed) — the card disappears after.

    public function activeOrdered(): Collection
    {
        return $this->getModel()->newQuery()->with('teacher')->active()->enrollmentOpen()->ordered()->get();
    }

    public function activeOrderedLimited(int $limit): Collection
    {
        return $this->getModel()->newQuery()->with('teacher')->active()->enrollmentOpen()->ordered()->limit($limit)->get();
    }

    public function activeCount(): int
    {
        return $this->getModel()->newQuery()->active()->enrollmentOpen()->count();
    }

    public function activeOrderedPaginated(int $perPage = 12): LengthAwarePaginator
    {
        return $this->getModel()->newQuery()->with('teacher')->active()->enrollmentOpen()->ordered()->paginate($perPage);
    }

    // ---- Mobile app ----

    public function openForApp(?CourseTypeEnum $type = null): Collection
    {
        return $this->getModel()->newQuery()
            ->with(['teacher', 'category'])
            ->when($type, fn ($q) => $q->where('type', $type->value))
            ->active()
            ->enrollmentOpen()
            ->ordered()
            ->get();
    }

    public function findOpenForApp(int $id): ?Course
    {
        return $this->getModel()->newQuery()
            ->with(['teacher', 'category', 'sessions' => fn ($q) => $q->upcoming()])
            ->active()
            ->enrollmentOpen()
            ->find($id);
    }

    public function forTeacher(Teacher $teacher): Collection
    {
        return $this->getModel()->newQuery()
            ->where('teacher_id', $teacher->id)
            ->active()
            ->with(['category', 'sessions' => fn ($q) => $q->upcoming()])
            ->withCount([
                'bookings as confirmed_count' => fn ($q) => $q->where('status', BookingStatusEnum::CONFIRMED->value),
                'bookings as pending_count' => fn ($q) => $q->where('status', BookingStatusEnum::PENDING->value),
            ])
            ->ordered()
            ->get();
    }
}
