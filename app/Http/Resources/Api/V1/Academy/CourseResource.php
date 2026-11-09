<?php

namespace App\Http\Resources\Api\V1\Academy;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A course group (one teacher) as shown in the app catalogue.
 *
 * @mixin Course
 */
class CourseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->localized('title'),
            'category' => $this->whenLoaded('category', fn () => $this->category ? [
                'id' => $this->category->id,
                'title' => $this->category->localized('title'),
            ] : null),
            'type' => $this->type?->value,
            'type_label' => $this->type?->getLabel(),
            'description' => $this->localized('description'),
            'badge' => $this->localized('badge'),
            'items' => $this->localizedItems(),
            'duration_label' => $this->durationLabel(),
            'days_left_label' => $this->daysLeftLabel(),
            'teacher' => new TeacherResource($this->whenLoaded('teacher')),
        ];
    }
}
