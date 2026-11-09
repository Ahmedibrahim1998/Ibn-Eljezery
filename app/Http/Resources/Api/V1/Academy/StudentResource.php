<?php

namespace App\Http\Resources\Api\V1\Academy;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A student in one of the teacher's groups (for the teacher only).
 *
 * @mixin Booking
 */
class StudentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'status' => $this->status?->value,
            'status_label' => $this->status?->getLabel(),
            'attended_classes' => $this->attendedDays(),
            'whatsapp_url' => $this->phone ? waUrl(normalizePhone($this->phone)) : null,
        ];
    }
}
