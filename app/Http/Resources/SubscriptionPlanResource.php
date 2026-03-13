<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionPlanResource extends JsonResource
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
            'name'            => $this->name,
            'slug'            => $this->slug,
            'description'     => $this->description,
            'price'           => $this->price,
            'price_formatted' => $this->formattedPrice(),
            'currency'        => $this->currency,
            'interval'        => $this->interval,
            'trial_days'      => $this->trial_days,
            'is_featured'     => $this->is_featured,
            'features'        => $this->whenLoaded(
                'features',
                fn() =>
                $this->features->mapWithKeys(fn($f) => [
                    $f->feature => $f->value,
                ])
            ),
        ];
    }
}
