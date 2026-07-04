<?php

namespace App\Repositories\Feature;

use App\Foundation\Repositories\Repository;
use App\Models\Feature;
use App\Repositories\Concerns\ListsActiveOrdered;

class FeatureRepository extends Repository implements FeatureRepositoryInterface
{
    use ListsActiveOrdered;

    public function __construct(Feature $model)
    {
        parent::__construct($model);
    }
}
