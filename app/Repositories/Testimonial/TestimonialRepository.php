<?php

namespace App\Repositories\Testimonial;

use App\Foundation\Repositories\Repository;
use App\Models\Testimonial;
use App\Repositories\Concerns\ListsActiveOrdered;

class TestimonialRepository extends Repository implements TestimonialRepositoryInterface
{
    use ListsActiveOrdered;

    public function __construct(Testimonial $model)
    {
        parent::__construct($model);
    }
}
