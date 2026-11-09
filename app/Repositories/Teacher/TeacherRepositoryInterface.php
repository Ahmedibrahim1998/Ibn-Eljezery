<?php

namespace App\Repositories\Teacher;

use App\Foundation\Repositories\RepositoryInterface;
use App\Models\Teacher;
use App\Repositories\Concerns\ListsActiveOrderedInterface;
use Illuminate\Database\Eloquent\Collection;

interface TeacherRepositoryInterface extends RepositoryInterface, ListsActiveOrderedInterface
{
    /** Active teachers with the number of their groups open for enrollment (app). */
    public function activeWithOpenCourses(): Collection;

    /** One active teacher with their open groups (app). */
    public function findActiveWithOpenCourses(int $id): ?Teacher;
}
