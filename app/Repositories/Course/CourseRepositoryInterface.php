<?php

namespace App\Repositories\Course;

use App\Enum\Course\CourseTypeEnum;
use App\Foundation\Repositories\RepositoryInterface;
use App\Models\Course;
use App\Models\Teacher;
use App\Repositories\Concerns\ListsActiveOrderedInterface;
use Illuminate\Database\Eloquent\Collection;

interface CourseRepositoryInterface extends RepositoryInterface, ListsActiveOrderedInterface
{
    /** Groups open for enrollment (app catalogue), optionally one type. */
    public function openForApp(?CourseTypeEnum $type = null): Collection;

    /** One open group with its upcoming sessions. */
    public function findOpenForApp(int $id): ?Course;

    /** The teacher's active groups with upcoming sessions and student counts. */
    public function forTeacher(Teacher $teacher): Collection;
}
