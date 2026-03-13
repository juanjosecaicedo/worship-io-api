<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GlobalSongResource extends JsonResource
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
            'title'            => $this->title,
            'author'           => $this->author,
            'original_key'     => $this->original_key,
            'tempo'            => $this->tempo,
            'time_signature'   => $this->time_signature,
            'duration_seconds' => $this->duration_seconds,
            'genre'            => $this->genre,
            'tags'             => $this->tags ?? [],
            'youtube_url'      => $this->youtube_url,
            'spotify_url'      => $this->spotify_url,
            'is_active'        => $this->is_active,
            'sections'         => $this->whenLoaded(
                'sections',
                fn() =>
                GlobalSongSectionResource::collection($this->sections)
            ),
            'sections_count'   => $this->whenCounted('sections'),
            'creator'          => $this->whenLoaded(
                'creator',
                fn() =>
                new UserResource($this->creator)
            ),
            'created_at'       => $this->created_at->toDateTimeString(),
        ];
    }
}
