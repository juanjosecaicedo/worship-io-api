<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SongNoteResource extends JsonResource
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
            'type'       => $this->type,
            'content'    => $this->content,
            'section_id' => $this->section_id,
            'user'       => $this->whenLoaded(
                'user',
                fn() =>
                new UserResource($this->user)
            ),
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
