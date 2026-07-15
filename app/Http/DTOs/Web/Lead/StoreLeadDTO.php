<?php

namespace App\Http\DTOs\Web\Lead;

use App\Enum\Lead\LeadSourceEnum;
use Illuminate\Validation\Rule;
use WendellAdriel\ValidatedDTO\Casting\IntegerCast;
use WendellAdriel\ValidatedDTO\ValidatedDTO;

class StoreLeadDTO extends ValidatedDTO
{
    public string $name;

    public string $phone;

    public ?string $email;

    public ?string $age_group;

    public ?string $level;

    public ?string $program;

    public ?int $course_id;

    public ?string $message;

    public string $source;

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'age_group' => ['nullable', 'string', 'max:255'],
            'level' => ['nullable', 'string', 'max:255'],
            'program' => ['nullable', 'string', 'max:255'],
            'course_id' => ['nullable', 'required_if:source,hero', 'integer', 'exists:courses,id'],
            'message' => ['nullable', 'string', 'max:2000'],
            'source' => ['required', Rule::in(LeadSourceEnum::values())],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaults(): array
    {
        // status is assigned by LeadService, not by the public form input.
        return [
            'source' => LeadSourceEnum::CONTACT->value,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'course_id' => new IntegerCast(),
        ];
    }
}
