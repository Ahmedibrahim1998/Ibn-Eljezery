<?php

namespace App\Http\Resources\Api\V1\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The signed-in app user. `role` decides which screens the app shows.
 *
 * @mixin User
 */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $teacher = $this->hasRole('teacher') ? $this->teacher : null;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'role' => $teacher ? 'teacher' : 'student',
            'teacher' => $teacher ? [
                'id' => $teacher->id,
                'name' => $teacher->localized('name'),
            ] : null,
        ];
    }
}
