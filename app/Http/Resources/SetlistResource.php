<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SetlistResource extends JsonResource
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
            'name'       => $this->name,
            'notes'      => $this->notes,
            'is_active'  => $this->is_active,
            'creator'    => $this->whenLoaded(
                'creator',
                fn() =>
                new UserResource($this->creator)
            ),
            'songs'      => $this->whenLoaded(
                'songs',
                fn() =>
                SetlistSongResource::collection($this->songs)
            ),
            'songs_count'    => $this->whenCounted('songs'),
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
