<?php

namespace App\Repositories\Learning;

use App\Foundation\Repositories\Repository;
use App\Models\LearningSubject;
use App\Models\Lesson;
use Illuminate\Database\Eloquent\Collection;

class LessonRepository extends Repository implements LessonRepositoryInterface
{
    public function __construct(Lesson $model)
    {
        parent::__construct($model);
    }

    public function publishedForSubject(LearningSubject $subject): Collection
    {
        return $this->getModel()->newQuery()
            ->where('learning_subject_id', $subject->id)
            ->published()
            ->ordered()
            ->get();
    }

    public function findPublished(int $id): ?Lesson
    {
        return $this->getModel()->newQuery()
            ->published()
            ->whereHas('subject', fn ($q) => $q->active()->whereHas('section', fn ($s) => $s->active()))
            ->with('subject')
            ->find($id);
    }
}
