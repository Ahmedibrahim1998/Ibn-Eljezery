<?php

namespace App\Repositories\Course;

use App\Foundation\Repositories\Repository;
use App\Models\Course;
use App\Repositories\Concerns\ListsActiveOrdered;

class CourseRepository extends Repository implements CourseRepositoryInterface
{
    use ListsActiveOrdered;

    public function __construct(Course $model)
    {
        parent::__construct($model);
    }
}
