<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Repositories\Course\CourseRepositoryInterface;
use App\Repositories\Faq\FaqRepositoryInterface;
use App\Repositories\Program\ProgramRepositoryInterface;
use App\Repositories\Teacher\TeacherRepositoryInterface;
use App\Repositories\Testimonial\TestimonialRepositoryInterface;
use Illuminate\Contracts\View\View;

class ListingController extends Controller
{
    public function teachers(TeacherRepositoryInterface $repo): View
    {
        return view('listing.cards', [
            'title' => siteText('sections.teachers_title'),
            'items' => $repo->activeOrderedPaginated(12),
            'cardPartial' => 'home.partials.teacher-card',
            'colClass' => 'col-md-6 col-lg-3',
        ]);
    }

    public function programs(ProgramRepositoryInterface $repo): View
    {
        return view('listing.cards', [
            'title' => siteText('sections.programs_title'),
            'items' => $repo->activeOrderedPaginated(9),
            'cardPartial' => 'home.partials.program-card',
            'colClass' => 'col-md-6 col-lg-4',
        ]);
    }

    public function courses(CourseRepositoryInterface $repo): View
    {
        return view('listing.cards', [
            'title' => siteText('sections.courses_title'),
            'items' => $repo->activeOrderedPaginated(8),
            'cardPartial' => 'home.partials.course-card',
            'colClass' => 'col-md-6',
        ]);
    }

    public function testimonials(TestimonialRepositoryInterface $repo): View
    {
        return view('listing.cards', [
            'title' => siteText('sections.testimonials_title'),
            'items' => $repo->activeOrderedPaginated(12),
            'cardPartial' => 'home.partials.testimonial-card',
            'colClass' => 'col-md-6 col-lg-4',
        ]);
    }

    public function faqs(FaqRepositoryInterface $repo): View
    {
        return view('listing.faqs', [
            'title' => siteText('sections.faq_title'),
            'items' => $repo->activeOrderedPaginated(15),
        ]);
    }
}
