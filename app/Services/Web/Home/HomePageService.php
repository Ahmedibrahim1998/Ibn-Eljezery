<?php

namespace App\Services\Web\Home;

use App\Models\Setting;
use App\Repositories\Course\CourseRepositoryInterface;
use App\Repositories\Faq\FaqRepositoryInterface;
use App\Repositories\Feature\FeatureRepositoryInterface;
use App\Repositories\Program\ProgramRepositoryInterface;
use App\Repositories\Teacher\TeacherRepositoryInterface;
use App\Repositories\Testimonial\TestimonialRepositoryInterface;
use App\Repositories\WeeklyPlanRow\WeeklyPlanRowRepositoryInterface;

readonly class HomePageService
{
    public function __construct(
        private TeacherRepositoryInterface $teachers,
        private ProgramRepositoryInterface $programs,
        private CourseRepositoryInterface $courses,
        private TestimonialRepositoryInterface $testimonials,
        private FaqRepositoryInterface $faqs,
        private FeatureRepositoryInterface $features,
        private WeeklyPlanRowRepositoryInterface $weeklyPlan,
    ) {}

    /**
     * Default number of cards shown per section on the homepage.
     */
    private function limit(string $key, int $default): int
    {
        return (int) (Setting::get("limit_{$key}", (string) $default) ?: $default);
    }

    /**
     * Build the full view-model for the landing page. Content sections show
     * a limited number of cards; the matching total lets the view decide
     * whether to render a "view all" button.
     *
     * @return array<string, mixed>
     */
    public function data(): array
    {
        $limitTeachers = $this->limit('teachers', 8);
        $limitPrograms = $this->limit('programs', 6);
        $limitCourses = $this->limit('courses', 6);
        $limitTestimonials = $this->limit('testimonials', 6);
        $limitFaqs = $this->limit('faqs', 8);

        return [
            'settings' => Setting::map(),

            // Always shown in full (small, fixed sets).
            'features' => $this->features->activeOrdered(),
            'weeklyPlan' => $this->weeklyPlan->activeOrdered(),

            // Limited sections + totals for the "view all" buttons.
            'teachers' => $this->teachers->activeOrderedLimited($limitTeachers),
            'teachersTotal' => $this->teachers->activeCount(),

            'programs' => $this->programs->activeOrderedLimited($limitPrograms),
            'programsTotal' => $this->programs->activeCount(),

            'courses' => $this->courses->activeOrderedLimited($limitCourses),
            'coursesTotal' => $this->courses->activeCount(),

            'testimonials' => $this->testimonials->activeOrderedLimited($limitTestimonials),
            'testimonialsTotal' => $this->testimonials->activeCount(),

            'faqs' => $this->faqs->activeOrderedLimited($limitFaqs),
            'faqsTotal' => $this->faqs->activeCount(),
        ];
    }
}
