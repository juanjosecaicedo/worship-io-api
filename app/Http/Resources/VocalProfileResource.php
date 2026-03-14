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
            /** @var int $id */
            'id' => $this->id,
            /** @var string $voice_type */
            'voice_type' => $this->voice_type,
            /** @var string $comfortable_key */
            'comfortable_key' => $this->comfortable_key,
            /** @var int $range_min */
            'range_min' => $this->range_min,
            /** @var int $range_max */
            'range_max' => $this->range_max,
            /** @var string $notes */
            'notes' => $this->notes,
        ];
    }
}
