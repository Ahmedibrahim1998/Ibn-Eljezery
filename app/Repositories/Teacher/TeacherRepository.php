<?php

namespace App\Repositories\Teacher;

use App\Foundation\Repositories\Repository;
use App\Models\Teacher;
use App\Repositories\Concerns\ListsActiveOrdered;
use Illuminate\Database\Eloquent\Collection;

class TeacherRepository extends Repository implements TeacherRepositoryInterface
{
    use ListsActiveOrdered;

    public function __construct(Teacher $model)
    {
        parent::__construct($model);
    }

    public function activeWithOpenCourses(): Collection
    {
        return $this->getModel()->newQuery()
            ->active()
            ->withCount(['courses as open_courses_count' => fn ($q) => $q->active()->enrollmentOpen()])
            ->ordered()
            ->get();
    }

    public function findActiveWithOpenCourses(int $id): ?Teacher
    {
        return $this->getModel()->newQuery()
            ->active()
            ->with(['courses' => fn ($q) => $q->active()->enrollmentOpen()->with('category')->ordered()])
            ->find($id);
    }
}
