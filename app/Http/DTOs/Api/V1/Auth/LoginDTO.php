<?php

namespace App\Http\DTOs\Api\V1\Auth;

use WendellAdriel\ValidatedDTO\ValidatedDTO;

/** App login: `login` is a phone number (students) or an email (teachers). */
class LoginDTO extends ValidatedDTO
{
    public string $login;

    public string $password;

    public ?string $device_name;

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'login.required' => trans('api.auth.login_required'),
            'password.required' => trans('api.auth.password_required'),
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
