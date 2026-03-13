<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventAttendeeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'status'       => $this->status,
            'notes'        => $this->notes,
            'responded_at' => $this->responded_at?->toDateTimeString(),
            'user'         => $this->whenLoaded(
                'user',
                fn() =>
                new UserResource($this->user)
            ),
        ];
    }
}
