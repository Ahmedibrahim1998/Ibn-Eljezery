<?php

namespace App\Repositories\Learning;

use App\Enum\Learning\MadhhabEnum;
use App\Foundation\Repositories\Repository;
use App\Models\LearningSection;
use App\Models\LearningSubject;
use Illuminate\Database\Eloquent\Collection;

class LearningSubjectRepository extends Repository implements LearningSubjectRepositoryInterface
{
    public function __construct(LearningSubject $model)
    {
        parent::__construct($model);
    }

    public function activeForSection(LearningSection $section, ?MadhhabEnum $madhhab = null): Collection
    {
        return $this->getModel()->newQuery()
            ->where('learning_section_id', $section->id)
            ->when($madhhab, fn ($q) => $q->ofMadhhab($madhhab))
            ->active()
            ->withCount('publishedLessons')
            ->ordered()
            ->get();
    }

    public function findActive(int $id): ?LearningSubject
    {
        return $this->getModel()->newQuery()->active()->find($id);
    }
}
