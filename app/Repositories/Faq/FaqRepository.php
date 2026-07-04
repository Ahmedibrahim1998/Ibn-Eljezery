<?php

namespace App\Repositories\Faq;

use App\Foundation\Repositories\Repository;
use App\Models\Faq;
use App\Repositories\Concerns\ListsActiveOrdered;

class FaqRepository extends Repository implements FaqRepositoryInterface
{
    use ListsActiveOrdered;

    public function __construct(Faq $model)
    {
        parent::__construct($model);
    }
}
