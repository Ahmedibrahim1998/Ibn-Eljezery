<?php

namespace App\Http\DTOs\Web\Booking;

use WendellAdriel\ValidatedDTO\Casting\IntegerCast;
use WendellAdriel\ValidatedDTO\ValidatedDTO;

class StoreBookingDTO extends ValidatedDTO
{
    public int $course_session_id;

    public string $name;

    public string $phone;

    public ?string $email;

    public ?string $notes;

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'course_session_id' => ['required', 'integer', 'exists:course_sessions,id'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
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
        return [
            'course_session_id' => new IntegerCast(),
        ];
    }
}
