<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SetlistVocalistResource extends JsonResource
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
            'vocal_role'   => $this->vocal_role,
            'key_override' => $this->key_override,
            'notes'        => $this->notes,
            'user'         => $this->whenLoaded(
                'user',
                fn() =>
                new UserResource($this->user)
            ),
        ];
    }
}
