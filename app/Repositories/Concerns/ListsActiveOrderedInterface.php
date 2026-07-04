<?php

namespace App\Repositories\Concerns;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Contract for content repositories that list active + ordered records,
 * with limiting, counting, and pagination for the public site.
 */
interface ListsActiveOrderedInterface
{
    public function activeOrdered(): Collection;

    public function activeOrderedLimited(int $limit): Collection;

    public function activeCount(): int;

    public function activeOrderedPaginated(int $perPage = 12): LengthAwarePaginator;
}
