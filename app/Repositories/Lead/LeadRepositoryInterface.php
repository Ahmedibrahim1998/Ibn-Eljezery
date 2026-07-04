<?php

namespace App\Repositories\Lead;

use App\Foundation\Repositories\RepositoryInterface;

interface LeadRepositoryInterface extends RepositoryInterface
{
    public function countNew(): int;
}
