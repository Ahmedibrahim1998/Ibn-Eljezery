<?php

namespace App\Repositories\Course;

use App\Foundation\Repositories\Repository;
use App\Models\Course;
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
}
