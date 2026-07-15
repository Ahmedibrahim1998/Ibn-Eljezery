<?php

namespace App\Http\DTOs\Web\Testimonial;

use WendellAdriel\ValidatedDTO\ValidatedDTO;

class StoreTestimonialDTO extends ValidatedDTO
{
    public string $name;

    public ?string $role;

    public string $body;

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'role' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string', 'min:5', 'max:2000'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaults(): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [];
    }
}
