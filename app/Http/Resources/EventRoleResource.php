<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventRoleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'    => $this->id,
            'role'  => $this->role,
            'notes' => $this->notes,
            'user'  => $this->whenLoaded(
                'user',
                fn() =>
                new UserResource($this->user)
            ),
        ];
    }
}
