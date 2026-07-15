<?php

namespace App\Http\Controllers\Web;

use App\Enum\Course\CourseTypeEnum;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseCategory;
use Illuminate\Contracts\View\View;

class CourseController extends Controller
{
    /** Level 1: the two course types (offline / online). */
    public function index(): View
    {
        $counts = [];
        foreach (CourseTypeEnum::cases() as $type) {
            $counts[$type->value] = CourseCategory::query()->active()->ofType($type)->count();
        }

        return view('courses.index', ['counts' => $counts]);
    }

    /** Level 2: the courses (categories) of one type. */
    public function type(string $type): View
    {
        $typeEnum = CourseTypeEnum::tryFrom($type);
        abort_if($typeEnum === null, 404);

        $categories = CourseCategory::query()
            ->active()
            ->ofType($typeEnum)
            ->ordered()
            ->get();

        return view('courses.type', [
            'type' => $typeEnum,
            'categories' => $categories,
        ]);
    }

    /** Level 3: the teacher groups inside one course. */
    public function category(CourseCategory $courseCategory): View
    {
        abort_unless($courseCategory->is_active, 404);

        $groups = $courseCategory->groups()
            ->with('teacher')
            ->active()
            ->enrollmentOpen()
            ->ordered()
            ->get();

        return view('courses.category', [
            'category' => $courseCategory,
            'groups' => $groups,
        ]);
    }

    /** Group detail + enrollment. */
    public function show(Course $course): View
    {
        abort_unless($course->is_active, 404);

        $course->load(['teacher', 'category', 'sessions' => function ($q) {
            $q->where('is_active', true)->orderBy('starts_at');
        }]);

        return view('courses.show', ['course' => $course]);
    }
}
