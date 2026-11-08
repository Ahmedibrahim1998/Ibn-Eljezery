<?php

namespace App\Http\Resources\Api\V1\Learning;

use App\Models\LearningSection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin LearningSection */
class LearningSectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->localized('title'),
            'description' => $this->localized('description'),
            'icon' => $this->icon,
            'color' => $this->color,
        ];
    }
}
