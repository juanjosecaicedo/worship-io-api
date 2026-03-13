<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupSongResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->id,
            'title'                 => $this->title,
            'author'                => $this->author,
            'custom_key'            => $this->custom_key,
            'custom_tempo'          => $this->custom_tempo,
            'custom_time_signature' => $this->custom_time_signature,
            'genre'                 => $this->genre,
            'tags'                  => $this->tags ?? [],
            'youtube_url'           => $this->youtube_url,
            'is_public'             => $this->is_public,
            'is_forked'             => $this->isForked(),
            'sections_order'        => $this->sections_order,
            'global_song'           => $this->whenLoaded(
                'globalSong',
                fn() =>
                new GlobalSongResource($this->globalSong)
            ),
            'sections'              => $this->whenLoaded(
                'sections',
                fn() =>
                GroupSongSectionResource::collection($this->sections)
            ),
            'sections_count'        => $this->whenCounted('sections'),
            'my_key'                => $this->whenLoaded(
                'userSongKeys',
                fn() =>
                $this->userSongKeys->first()
                    ? new UserSongKeyResource($this->userSongKeys->first())
                    : null
            ),
            'creator'               => $this->whenLoaded(
                'creator',
                fn() =>
                new UserResource($this->creator)
            ),
            'created_at'            => $this->created_at->toDateTimeString(),
        ];
    }
}
