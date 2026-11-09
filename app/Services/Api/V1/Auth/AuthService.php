<?php

namespace App\Services\Api\V1\Auth;

use App\Http\DTOs\Api\V1\Auth\LoginDTO;
use App\Http\DTOs\Api\V1\Auth\RegisterDTO;
use App\Models\User;
use App\Repositories\User\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Mobile app accounts (Sanctum personal access tokens).
 * Students register with their phone; teachers log in with the email/password
 * they already use for the teacher panel.
 */
class AuthService
{
    /** Roles that can use the app. Other staff (admin/supervisor) use the panels. */
    public const APP_ROLES = ['student', 'teacher'];

    public function __construct(private readonly UserRepositoryInterface $users) {}

    /**
     * @return array{0: User, 1: string} the new student and their token
     *
     * @throws ValidationException
     */
    public function register(RegisterDTO $dto): array
    {
        $phone = normalizePhone($dto->phone);

        if ($this->users->findByPhone($phone)) {
            throw ValidationException::withMessages(['phone' => trans('api.auth.phone_taken')]);
        }

        $user = DB::transaction(function () use ($dto, $phone) {
            /** @var User $user */
            $user = $this->users->create([
                'name' => $dto->name,
                'phone' => $phone,
                'email' => $dto->email,
                'password' => $dto->password,
            ]);
            $user->assignRole('student');

            return $user;
        });

        return [$user, $this->issueToken($user, $dto->device_name)];
    }

    /**
     * @return array{0: User, 1: string}
     *
     * @throws ValidationException
     */
    public function login(LoginDTO $dto): array
    {
        $user = str_contains($dto->login, '@')
            ? $this->users->findByEmail(trim($dto->login))
            : $this->users->findByPhone(normalizePhone($dto->login));

        if (! $user || ! Hash::check($dto->password, $user->password)) {
            throw ValidationException::withMessages(['login' => trans('api.auth.failed')]);
        }

        if (! $user->hasAnyRole(self::APP_ROLES)) {
            throw ValidationException::withMessages(['login' => trans('api.auth.panel_only')]);
        }

        return [$user, $this->issueToken($user, $dto->device_name)];
    }

    /** Revoke only the token of this device. */
    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }

    /**
     * Delete a student account (Google Play requires in-app deletion).
     * Their enrollments are kept for the academy's records, unlinked from the account.
     */
    public function deleteAccount(User $user): void
    {
        abort_if($user->hasAnyRole(['teacher', 'supervisor', 'super_admin']), 403, trans('api.auth.delete_staff'));

        DB::transaction(function () use ($user) {
            $user->tokens()->delete();
            $user->syncRoles([]);
            $user->delete();
        });
    }

    private function issueToken(User $user, ?string $device): string
    {
        return $user->createToken($device ?: 'mobile-app')->plainTextToken;
    }
}
