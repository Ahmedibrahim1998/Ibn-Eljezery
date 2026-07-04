<?php

namespace App\Repositories\Concerns;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Shared behaviour for content repositories whose models use the
 * HasListingScopes trait (active() + ordered()).
 */
trait ListsActiveOrdered
{
    public function activeOrdered(): Collection
    {
        return $this->getModel()->newQuery()->active()->ordered()->get();
    }

    public function activeOrderedLimited(int $limit): Collection
    {
        return $this->getModel()->newQuery()->active()->ordered()->limit($limit)->get();
    }

    public function activeCount(): int
    {
        return $this->getModel()->newQuery()->active()->count();
    }

    public function activeOrderedPaginated(int $perPage = 12): LengthAwarePaginator
    {
        return $this->getModel()->newQuery()->active()->ordered()->paginate($perPage);
    }
}
