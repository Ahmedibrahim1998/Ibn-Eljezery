<?php

namespace App\Foundation\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

abstract class Repository implements RepositoryInterface
{
    protected Model $model;

    /**
     * Relations eagerly loaded on the next query.
     *
     * @var array<int, string>
     */
    protected array $relations = [];

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return $this->query()->get();
    }

    public function create(array $attributes): Model
    {
        return $this->model->newQuery()->create($attributes);
    }

    public function fillAndSave(array $attributes): Model
    {
        $this->model->fill($attributes)->save();

        return $this->model;
    }

    public function update(int $id, array $attributes): Model
    {
        $model = $this->findOrFail($id);
        $model->fill($attributes)->save();

        return $model;
    }

    public function delete(int $id): bool
    {
        return (bool) $this->findOrFail($id)->delete();
    }

    public function find(int $id): ?Model
    {
        return $this->query()->find($id);
    }

    public function findOrFail(int $id): Model
    {
        return $this->query()->findOrFail($id);
    }

    public function with(array $relations): static
    {
        $this->relations = $relations;

        return $this;
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()->paginate($perPage);
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Build a fresh query applying (and resetting) pending eager loads.
     */
    protected function query(): Builder
    {
        $query = $this->model->newQuery();

        if (! empty($this->relations)) {
            $query->with($this->relations);
            $this->relations = [];
        }

        return $query;
    }
}
