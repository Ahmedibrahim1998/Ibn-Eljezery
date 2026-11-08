<?php

namespace App\Repositories\Learning;

use App\Enum\Learning\MadhhabEnum;
use App\Foundation\Repositories\RepositoryInterface;
use App\Models\LearningSection;
use App\Models\LearningSubject;
use Illuminate\Database\Eloquent\Collection;

interface LearningSubjectRepositoryInterface extends RepositoryInterface
{
    /** Active subjects of a section, optionally for one madhhab, with their published-lesson counts. */
    public function activeForSection(LearningSection $section, ?MadhhabEnum $madhhab = null): Collection;

    public function findActive(int $id): ?LearningSubject;
}
