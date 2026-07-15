<?php

namespace App\Services\Web\Testimonial;

use App\Http\DTOs\Web\Testimonial\StoreTestimonialDTO;
use App\Models\Testimonial;

class TestimonialService
{
    /**
     * Store a review submitted by a visitor. It stays hidden (is_active =
     * false) until an admin approves it. The visitor writes in one language,
     * so we mirror the text into both locale columns to keep it visible.
     */
    public function store(StoreTestimonialDTO $dto): Testimonial
    {
        return Testimonial::create([
            'body_ar' => $dto->body,
            'body_en' => $dto->body,
            'author_name_ar' => $dto->name,
            'author_name_en' => $dto->name,
            'author_role_ar' => $dto->role,
            'author_role_en' => $dto->role,
            'is_active' => false,
        ]);
    }
}
