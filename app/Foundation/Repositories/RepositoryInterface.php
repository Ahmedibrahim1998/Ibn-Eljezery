<?php

namespace App\Foundation\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface RepositoryInterface
{
    public function all(): Collection;

    public function create(array $attributes): Model;

    public function fillAndSave(array $attributes): Model;

    public function update(int $id, array $attributes): Model;

    public function delete(int $id): bool;

    public function find(int $id): ?Model;

    public function with(array $relations): static;

    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function getModel(): Model;
}
