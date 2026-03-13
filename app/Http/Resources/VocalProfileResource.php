<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VocalProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'voice_type'      => $this->voice_type,
            'comfortable_key' => $this->comfortable_key,
            'range_min'       => $this->range_min,
            'range_max'       => $this->range_max,
            'notes'           => $this->notes,
        ];
    }
}
