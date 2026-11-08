<?php

namespace App\Services\Api\V1\Learning;

use App\Enum\Learning\MadhhabEnum;
use App\Models\LearningSection;
use App\Models\LearningSubject;
use App\Models\Lesson;
use App\Repositories\Learning\LearningSectionRepositoryInterface;
use App\Repositories\Learning\LearningSubjectRepositoryInterface;
use App\Repositories\Learning\LessonRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Read-only learning content for the mobile app. Everything returned here
 * is active and (for lessons) published — drafts never leave the panel.
 */
class LearningService
{
    public function __construct(
        private readonly LearningSectionRepositoryInterface $sections,
        private readonly LearningSubjectRepositoryInterface $subjects,
        private readonly LessonRepositoryInterface $lessons,
    ) {}

    public function sections(): Collection
    {
        return $this->sections->activeOrdered();
    }

    public function section(string $slug): ?LearningSection
    {
        return $this->sections->findActiveBySlug($slug);
    }

    public function subjects(LearningSection $section, ?MadhhabEnum $madhhab = null): Collection
    {
        return $this->subjects->activeForSection($section, $madhhab);
    }

    public function subject(int $id): ?LearningSubject
    {
        return $this->subjects->findActive($id);
    }

    public function lessons(LearningSubject $subject): Collection
    {
        return $this->lessons->publishedForSubject($subject);
    }

    public function lesson(int $id): ?Lesson
    {
        return $this->lessons->findPublished($id);
    }
}
