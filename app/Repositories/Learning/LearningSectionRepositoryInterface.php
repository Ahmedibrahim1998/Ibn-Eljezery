<?php

namespace App\Repositories\Learning;

use App\Foundation\Repositories\RepositoryInterface;
use App\Models\LearningSection;
use App\Repositories\Concerns\ListsActiveOrderedInterface;

interface LearningSectionRepositoryInterface extends RepositoryInterface, ListsActiveOrderedInterface
{
    public function findActiveBySlug(string $slug): ?LearningSection;
}
