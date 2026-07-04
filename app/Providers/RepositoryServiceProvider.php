<?php

namespace App\Providers;

use App\Repositories\Course\CourseRepository;
use App\Repositories\Course\CourseRepositoryInterface;
use App\Repositories\Faq\FaqRepository;
use App\Repositories\Faq\FaqRepositoryInterface;
use App\Repositories\Feature\FeatureRepository;
use App\Repositories\Feature\FeatureRepositoryInterface;
use App\Repositories\Lead\LeadRepository;
use App\Repositories\Lead\LeadRepositoryInterface;
use App\Repositories\Program\ProgramRepository;
use App\Repositories\Program\ProgramRepositoryInterface;
use App\Repositories\Teacher\TeacherRepository;
use App\Repositories\Teacher\TeacherRepositoryInterface;
use App\Repositories\Testimonial\TestimonialRepository;
use App\Repositories\Testimonial\TestimonialRepositoryInterface;
use App\Repositories\WeeklyPlanRow\WeeklyPlanRowRepository;
use App\Repositories\WeeklyPlanRow\WeeklyPlanRowRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Interface => implementation bindings.
     *
     * @var array<class-string, class-string>
     */
    private array $repositories = [
        TeacherRepositoryInterface::class => TeacherRepository::class,
        ProgramRepositoryInterface::class => ProgramRepository::class,
        CourseRepositoryInterface::class => CourseRepository::class,
        TestimonialRepositoryInterface::class => TestimonialRepository::class,
        FaqRepositoryInterface::class => FaqRepository::class,
        FeatureRepositoryInterface::class => FeatureRepository::class,
        WeeklyPlanRowRepositoryInterface::class => WeeklyPlanRowRepository::class,
        LeadRepositoryInterface::class => LeadRepository::class,
    ];

    public function register(): void
    {
        foreach ($this->repositories as $interface => $implementation) {
            $this->app->bind($interface, $implementation);
        }
    }
}
