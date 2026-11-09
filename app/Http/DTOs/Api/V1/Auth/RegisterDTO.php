<?php

namespace App\Http\DTOs\Api\V1\Auth;

use WendellAdriel\ValidatedDTO\ValidatedDTO;

/** Student sign-up from the mobile app (phone is the login; email optional). */
class RegisterDTO extends ValidatedDTO
{
    public string $name;

    public string $phone;

    public ?string $email;

    public string $password;

    public ?string $device_name;

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^[+\d\s\-()]{8,30}$/'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:255'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => trans('api.auth.name_required'),
            'phone.required' => trans('api.auth.phone_required'),
            'phone.regex' => trans('api.auth.phone_invalid'),
            'email.email' => trans('api.auth.email_invalid'),
            'email.unique' => trans('api.auth.email_taken'),
            'password.required' => trans('api.auth.password_required'),
            'password.min' => trans('api.auth.password_min'),
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
