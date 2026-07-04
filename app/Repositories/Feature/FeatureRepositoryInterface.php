<?php

namespace App\Repositories\Feature;

use App\Foundation\Repositories\RepositoryInterface;
use App\Repositories\Concerns\ListsActiveOrderedInterface;

interface FeatureRepositoryInterface extends RepositoryInterface, ListsActiveOrderedInterface
{
}
