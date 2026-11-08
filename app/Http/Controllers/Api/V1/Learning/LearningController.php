<?php

namespace App\Http\Controllers\Api\V1\Learning;

use App\Enum\Learning\MadhhabEnum;
use App\Http\Controllers\Controller;
use App\Http\Middleware\Api\SetLocaleFromHeader;
use App\Http\Resources\Api\V1\Learning\LearningSectionResource;
use App\Http\Resources\Api\V1\Learning\LearningSubjectResource;
use App\Http\Resources\Api\V1\Learning\LessonResource;
use App\Services\Api\V1\Learning\LearningService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\WhereNumber;

/**
 * Public learning content for the mobile app: section -> subjects -> lessons.
 * Registered under /api/v1 by spatie/laravel-route-attributes.
 */
#[Prefix('learning')]
#[Middleware(SetLocaleFromHeader::class)]
class LearningController extends Controller
{
    public function __construct(private readonly LearningService $service) {}

    #[Get('sections', name: 'api.v1.learning.sections')]
    public function sections(): AnonymousResourceCollection
    {
        return LearningSectionResource::collection($this->service->sections());
    }

    /** ?madhhab=maliki|shafii|… filters the subjects to one madhhab. */
    #[Get('sections/{slug}/subjects', name: 'api.v1.learning.subjects')]
    public function subjects(Request $request, string $slug): AnonymousResourceCollection
    {
        $section = $this->service->section($slug);
        abort_if($section === null, 404);

        $madhhab = $request->filled('madhhab') ? MadhhabEnum::tryFrom($request->string('madhhab')) : null;
        abort_if($request->filled('madhhab') && $madhhab === null, 422, 'Unknown madhhab.');

        return LearningSubjectResource::collection($this->service->subjects($section, $madhhab));
    }

    #[Get('subjects/{subject}/lessons', name: 'api.v1.learning.lessons')]
    #[WhereNumber('subject')]
    public function lessons(int $subject): AnonymousResourceCollection
    {
        $model = $this->service->subject($subject);
        abort_if($model === null, 404);

        return LessonResource::collection($this->service->lessons($model));
    }

    #[Get('lessons/{lesson}', name: 'api.v1.learning.lesson')]
    #[WhereNumber('lesson')]
    public function lesson(int $lesson): LessonResource
    {
        $model = $this->service->lesson($lesson);
        abort_if($model === null, 404);

        return new LessonResource($model);
    }
}
