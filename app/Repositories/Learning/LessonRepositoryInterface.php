<?php

namespace App\Repositories\Learning;

use App\Foundation\Repositories\RepositoryInterface;
use App\Models\LearningSubject;
use App\Models\Lesson;
use Illuminate\Database\Eloquent\Collection;

interface LessonRepositoryInterface extends RepositoryInterface
{
    public function publishedForSubject(LearningSubject $subject): Collection;

    /** A published lesson whose subject (and section) are still active. */
    public function findPublished(int $id): ?Lesson;
}
