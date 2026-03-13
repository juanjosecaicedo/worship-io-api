<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupMemberResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'role'       => $this->role,
            'instrument' => $this->instrument,
            'joined_at'  => $this->joined_at?->toDateString(),
            'is_active'  => $this->is_active,
            'user'       => $this->whenLoaded(
                'user',
                fn() =>
                new UserResource($this->user)
            ),
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
