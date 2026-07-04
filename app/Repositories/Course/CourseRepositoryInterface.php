<?php

namespace App\Repositories\Course;

use App\Foundation\Repositories\RepositoryInterface;
use App\Repositories\Concerns\ListsActiveOrderedInterface;

interface CourseRepositoryInterface extends RepositoryInterface, ListsActiveOrderedInterface
{
}
