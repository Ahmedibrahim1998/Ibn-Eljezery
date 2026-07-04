<?php

namespace App\Repositories\Program;

use App\Foundation\Repositories\Repository;
use App\Models\Program;
use App\Repositories\Concerns\ListsActiveOrdered;

class ProgramRepository extends Repository implements ProgramRepositoryInterface
{
    use ListsActiveOrdered;

    public function __construct(Program $model)
    {
        parent::__construct($model);
    }
}
