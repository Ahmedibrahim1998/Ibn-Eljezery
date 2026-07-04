<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Contracts\View\View;

class CourseController extends Controller
{
    public function show(Course $course): View
    {
        abort_unless($course->is_active, 404);

        $course->load(['teacher', 'sessions' => function ($q) {
            $q->where('is_active', true)
                ->where('starts_at', '>=', now()->subHours(2))
                ->orderBy('starts_at');
        }]);

        return view('courses.show', ['course' => $course]);
    }
}
