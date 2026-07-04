<?php

namespace App\Http\DTOs\Web\Lead;

use App\Enum\Lead\LeadSourceEnum;
use Illuminate\Validation\Rule;
use WendellAdriel\ValidatedDTO\ValidatedDTO;

class StoreLeadDTO extends ValidatedDTO
{
    public string $name;

    public string $phone;

    public ?string $email;

    public ?string $age_group;

    public ?string $level;

    public ?string $program;

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
        return [];
    }
}
