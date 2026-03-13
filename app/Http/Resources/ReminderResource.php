<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReminderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'minutes_before' => $this->minutes_before,
            'label'          => $this->labelForMinutes(),
            'channel'        => $this->channel,
            'is_sent'        => $this->is_sent,
            'sent_at'        => $this->sent_at?->toDateTimeString(),
            'scheduled_at'   => $this->scheduledAt()->toDateTimeString(),
            'created_at'     => $this->created_at->toDateTimeString(),
        ];
    }

    private function labelForMinutes(): string
    {
        $minutes = $this->minutes_before;

        return match (true) {
            $minutes < 60   => "{$minutes} minutos antes",
            $minutes < 1440 => ($minutes / 60) . ' horas antes',
            $minutes < 10080 => ($minutes / 1440) . ' días antes',
            default          => ($minutes / 10080) . ' semanas antes',
        };
    }
}
