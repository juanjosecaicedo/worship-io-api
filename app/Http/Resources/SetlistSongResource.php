<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SetlistSongResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'order'             => $this->order,
            'key_override'      => $this->key_override,
            'effective_key'     => $this->effectiveKey(),
            'duration_override' => $this->duration_override,
            'notes'             => $this->notes,
            'group_song'        => $this->whenLoaded(
                'groupSong',
                fn() =>
                new GroupSongResource($this->groupSong)
            ),
            // Agrupados por rol vocal
            'vocalists'         => $this->whenLoaded('vocalists', fn() => [
                'lead'    => SetlistVocalistResource::collection(
                    $this->vocalists->where('vocal_role', 'lead')->values()
                ),
                'harmony' => SetlistVocalistResource::collection(
                    $this->vocalists->where('vocal_role', 'harmony')->values()
                ),
                'choir'   => SetlistVocalistResource::collection(
                    $this->vocalists->where('vocal_role', 'choir')->values()
                ),
            ]),
        ];
    }
}
