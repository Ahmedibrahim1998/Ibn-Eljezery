<?php

namespace App\Http\Resources\Api\V1\Learning;

use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A lesson. In lists only the summary is sent; the full content only on the
 * single-lesson endpoint (it can be long).
 *
 * @mixin Lesson
 */
class LessonResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'subject_id' => $this->learning_subject_id,
            'title' => $this->localized('title'),
            'duration_minutes' => $this->duration_minutes,
            'has_audio' => filled($this->audio_url),
            'has_video' => filled($this->video_url),
            'version' => $this->version,
            'published_at' => $this->published_at?->toIso8601String(),
            $this->mergeWhen($request->route('lesson') !== null, fn () => [
                'content' => $this->localized('content'),
                'audio_url' => $this->audio_url,
                'video_url' => $this->video_url,
            ]),
        ];
    }
}
