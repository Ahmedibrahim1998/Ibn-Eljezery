<?php

namespace App\Http\Resources\Api\V1\Academy;

use App\Models\CourseSession;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A scheduled class. Zoom links are opt-in: `join_url` for confirmed students,
 * `start_url` (host link) for the group's own teacher.
 *
 * @mixin CourseSession
 */
class CourseSessionResource extends JsonResource
{
    public function __construct(
        $resource,
        private readonly bool $withJoinUrl = false,
        private readonly bool $withStartUrl = false,
    ) {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'starts_at' => $this->starts_at?->toIso8601String(),
            'duration_minutes' => $this->duration_minutes,
            'location' => $this->localized('location'),
            'is_online' => $this->isOnline(),
            'join_url' => $this->when($this->withJoinUrl, $this->zoom_join_url),
            'start_url' => $this->when($this->withStartUrl, $this->zoom_start_url),
        ];
    }
}
