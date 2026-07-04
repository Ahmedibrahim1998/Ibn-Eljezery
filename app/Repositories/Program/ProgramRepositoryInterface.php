<?php

namespace App\Repositories\Program;

use App\Foundation\Repositories\RepositoryInterface;
use App\Repositories\Concerns\ListsActiveOrderedInterface;

interface ProgramRepositoryInterface extends RepositoryInterface, ListsActiveOrderedInterface
{
}
