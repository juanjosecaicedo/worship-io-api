<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'status'               => $this->status,
            'is_active'            => $this->isActive(),
            'is_trialing'          => $this->isTrialing(),
            'is_cancelled'         => $this->isCancelled(),
            'trial_ends_at'        => $this->trial_ends_at?->toDateTimeString(),
            'current_period_start' => $this->current_period_start?->toDateTimeString(),
            'current_period_end'   => $this->current_period_end?->toDateTimeString(),
            'cancelled_at'         => $this->cancelled_at?->toDateTimeString(),
            'ends_at'              => $this->ends_at?->toDateTimeString(),
            'payment_gateway'      => $this->payment_gateway,
            'plan'                 => $this->whenLoaded(
                'plan',
                fn() =>
                new SubscriptionPlanResource($this->plan)
            ),
            'groups'               => $this->whenLoaded(
                'groups',
                fn() =>
                $this->groups->map(fn($sg) => [
                    'id'          => $sg->group_id,
                    'assigned_at' => $sg->assigned_at,
                ])
            ),
            'created_at'           => $this->created_at->toDateTimeString(),
        ];
    }
}
