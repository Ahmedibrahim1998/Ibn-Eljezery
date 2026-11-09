<?php

namespace App\Http\Resources\Api\V1\Academy;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * One of the signed-in teacher's groups: student counts, upcoming classes
 * with the Zoom host link, and the public booking page to share.
 *
 * @mixin Course
 */
class TeacherCourseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            ...(new CourseResource($this->resource))->toArray($request),
            'booking_url' => route('courses.show', $this->id),
            'confirmed_count' => (int) $this->confirmed_count,
            'pending_count' => (int) $this->pending_count,
            'sessions' => $this->sessions->map(
                fn ($s) => new CourseSessionResource($s, withJoinUrl: true, withStartUrl: true)
            ),
        ];
    }
}
