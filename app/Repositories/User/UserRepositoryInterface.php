<?php

namespace App\Repositories\User;

use App\Foundation\Repositories\RepositoryInterface;
use App\Models\User;

interface UserRepositoryInterface extends RepositoryInterface
{
    public function findByPhone(string $normalizedPhone): ?User;

    public function findByEmail(string $email): ?User;
}
