<?php

namespace App\Repositories\WeeklyPlanRow;

use App\Foundation\Repositories\Repository;
use App\Models\WeeklyPlanRow;
use App\Repositories\Concerns\ListsActiveOrdered;

class WeeklyPlanRowRepository extends Repository implements WeeklyPlanRowRepositoryInterface
{
    use ListsActiveOrdered;

    public function __construct(WeeklyPlanRow $model)
    {
        parent::__construct($model);
    }
}
