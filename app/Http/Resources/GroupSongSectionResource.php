<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupSongSectionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'type'             => $this->type,
            'label'            => $this->label,
            'lyrics'           => $this->lyrics,
            'chords'           => $this->chords ?? [],
            'order'            => $this->order,
            'global_section_id' => $this->global_section_id,
            'notes'            => $this->whenLoaded(
                'notes',
                fn() =>
                SongNoteResource::collection($this->notes)
            ),
        ];
    }
}
