<?php

namespace App\Http\Controllers\Api\V1\Academy;

use App\Enum\Course\CourseTypeEnum;
use App\Exceptions\Booking\AlreadyBookedException;
use App\Http\Controllers\Controller;
use App\Http\Middleware\Api\SetLocaleFromHeader;
use App\Http\Resources\Api\V1\Academy\CourseResource;
use App\Http\Resources\Api\V1\Academy\CourseSessionResource;
use App\Http\Resources\Api\V1\Academy\MyBookingResource;
use App\Http\Resources\Api\V1\Academy\StudentResource;
use App\Http\Resources\Api\V1\Academy\TeacherCourseResource;
use App\Http\Resources\Api\V1\Academy\TeacherResource;
use App\Services\Api\V1\Academy\AcademyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\WhereNumber;

/**
 * The academy in the mobile app: catalogue (public), enrollment and
 * "my courses" (students), "my groups" + students (teachers).
 */
#[Prefix('academy')]
#[Middleware(SetLocaleFromHeader::class)]
class AcademyController extends Controller
{
    public function __construct(private readonly AcademyService $service) {}

    /** ?type=online|offline */
    #[Get('courses', name: 'api.v1.academy.courses')]
    public function courses(Request $request): AnonymousResourceCollection
    {
        $type = $request->filled('type') ? CourseTypeEnum::tryFrom($request->string('type')) : null;
        abort_if($request->filled('type') && $type === null, 422, 'Unknown course type.');

        return CourseResource::collection($this->service->courses($type));
    }

    #[Get('teachers', name: 'api.v1.academy.teachers')]
    public function teachers(): AnonymousResourceCollection
    {
        return TeacherResource::collection($this->service->teachers());
    }

    /** Teacher page: profile + WhatsApp + their groups open for enrollment. */
    #[Get('teachers/{teacher}', name: 'api.v1.academy.teacher')]
    #[WhereNumber('teacher')]
    public function teacher(int $teacher): TeacherResource
    {
        $model = $this->service->teacher($teacher);
        abort_if($model === null, 404);

        return new TeacherResource($model);
    }

    /** Public group page; `is_enrolled` is filled when a token is sent. */
    #[Get('courses/{course}', name: 'api.v1.academy.course')]
    #[WhereNumber('course')]
    public function course(Request $request, int $course): JsonResponse
    {
        $model = $this->service->course($course);
        abort_if($model === null, 404, trans('api.booking.closed'));
        $user = $request->user('sanctum');

        return response()->json(['data' => [
            ...(new CourseResource($model))->resolve($request),
            'sessions' => CourseSessionResource::collection($model->sessions)->resolve($request),
            'is_enrolled' => $user ? $this->service->isEnrolled($model, $user) : false,
        ]]);
    }

    #[Post('courses/{course}/book', name: 'api.v1.academy.book', middleware: ['auth:sanctum', 'throttle:10,1'])]
    #[WhereNumber('course')]
    public function book(Request $request, int $course): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasRole('student'), 403);
        $model = $this->service->course($course);
        abort_if($model === null, 404, trans('api.booking.closed'));
        $notes = $request->validate(['notes' => ['nullable', 'string', 'max:1000']])['notes'] ?? null;

        try {
            $booking = $this->service->book($model, $user, $notes);
        } catch (AlreadyBookedException) {
            return response()->json(['message' => trans('api.booking.already')], 422);
        }

        return response()->json([
            'message' => trans('api.booking.success'),
            'data' => new MyBookingResource($booking->load('course.teacher', 'course.category')),
        ], 201);
    }

    #[Get('my/bookings', name: 'api.v1.academy.my-bookings', middleware: 'auth:sanctum')]
    public function myBookings(Request $request): AnonymousResourceCollection
    {
        return MyBookingResource::collection($this->service->myBookings($request->user()));
    }

    #[Get('teacher/courses', name: 'api.v1.academy.teacher-courses', middleware: 'auth:sanctum')]
    public function teacherCourses(Request $request): AnonymousResourceCollection
    {
        return TeacherCourseResource::collection($this->service->teacherCourses($request->user()));
    }

    #[Get('teacher/courses/{course}/students', name: 'api.v1.academy.teacher-students', middleware: 'auth:sanctum')]
    #[WhereNumber('course')]
    public function teacherStudents(Request $request, int $course): AnonymousResourceCollection
    {
        return StudentResource::collection($this->service->teacherStudents($request->user(), $course));
    }
}
