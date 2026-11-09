<?php

namespace App\Http\Resources\Api\V1\Academy;

use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Teacher card / page. `whatsapp_url` lets a student ask before booking
 * (falls back to the academy number when the teacher has none).
 *
 * @mixin Teacher
 */
class TeacherResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->localized('name'),
            'certification' => $this->localized('certification'),
            'description' => $this->localized('description'),
            'badge' => $this->localized('badge'),
            'photo' => $this->photo ? asset('storage/'.$this->photo) : null,
            'whatsapp_url' => $this->whatsappUrl(),
            'open_courses_count' => $this->whenCounted('open_courses_count'),
            'courses' => CourseResource::collection($this->whenLoaded('courses')),
        ];
    }
}
