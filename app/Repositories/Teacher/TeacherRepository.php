<?php

namespace App\Repositories\Teacher;

use App\Foundation\Repositories\Repository;
use App\Models\Teacher;
use App\Repositories\Concerns\ListsActiveOrdered;

class TeacherRepository extends Repository implements TeacherRepositoryInterface
{
    use ListsActiveOrdered;

    public function __construct(Teacher $model)
    {
        parent::__construct($model);
    }
}
