<?php

namespace App\Repositories\Lead;

use App\Enum\Lead\LeadStatusEnum;
use App\Foundation\Repositories\Repository;
use App\Models\Lead;

class LeadRepository extends Repository implements LeadRepositoryInterface
{
    public function __construct(Lead $model)
    {
        parent::__construct($model);
    }

    public function countNew(): int
    {
        return $this->model->newQuery()
            ->where('status', LeadStatusEnum::NEW->value)
            ->count();
    }
}
