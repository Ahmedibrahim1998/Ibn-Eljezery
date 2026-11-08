<?php

namespace App\Http\Resources\Api\V1\Learning;

use App\Models\LearningSubject;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin LearningSubject */
class LearningSubjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->localized('title'),
            'description' => $this->localized('description'),
            'author' => $this->localized('author'),
            'madhhab' => $this->madhhab?->value,
            'madhhab_label' => $this->madhhab?->getLabel(),
            'level' => $this->level?->value,
            'level_label' => $this->level?->getLabel(),
            'cover_image' => $this->cover_image ? asset('storage/'.$this->cover_image) : null,
            'lessons_count' => $this->whenCounted('publishedLessons'),
        ];
    }
}
