<?php

namespace App\Repositories\User;

use App\Foundation\Repositories\Repository;
use App\Models\User;

class UserRepository extends Repository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function findByPhone(string $normalizedPhone): ?User
    {
        return $this->getModel()->newQuery()->where('phone', $normalizedPhone)->first();
    }

    public function findByEmail(string $email): ?User
    {
        return $this->getModel()->newQuery()->where('email', $email)->first();
    }
}
