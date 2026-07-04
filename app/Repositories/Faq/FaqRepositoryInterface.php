<?php

namespace App\Repositories\Faq;

use App\Foundation\Repositories\RepositoryInterface;
use App\Repositories\Concerns\ListsActiveOrderedInterface;

interface FaqRepositoryInterface extends RepositoryInterface, ListsActiveOrderedInterface
{
}
