<?php

namespace App\Repositories\Learning;

use App\Foundation\Repositories\Repository;
use App\Models\LearningSection;
use App\Repositories\Concerns\ListsActiveOrdered;

class LearningSectionRepository extends Repository implements LearningSectionRepositoryInterface
{
    use ListsActiveOrdered;

    public function __construct(LearningSection $model)
    {
        parent::__construct($model);
    }

    public function findActiveBySlug(string $slug): ?LearningSection
    {
        return $this->getModel()->newQuery()->active()->where('slug', $slug)->first();
    }
}
