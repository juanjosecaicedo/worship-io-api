<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
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
            'title'          => $this->title,
            'type'           => $this->type,
            'description'    => $this->description,
            'location'       => $this->location,
            'start_datetime' => $this->start_datetime->toDateTimeString(),
            'end_datetime'   => $this->end_datetime->toDateTimeString(),
            'status'         => $this->status,
            'color'          => $this->color,
            'gcal_event_id'  => $this->gcal_event_id,
            'creator'        => $this->whenLoaded(
                'creator',
                fn() =>
                new UserResource($this->creator)
            ),
            // Roles agrupados por tipo
            'band_director'  => $this->whenLoaded(
                'roles',
                fn() =>
                $this->roles
                    ->where('role', 'band_director')
                    ->map(fn($r) => new EventRoleResource($r))
                    ->values()
                    ->first()
            ),
            'vocalists'      => $this->whenLoaded(
                'roles',
                fn() =>
                EventRoleResource::collection(
                    $this->roles->where('role', 'vocalist')->values()
                )
            ),
            'choir'          => $this->whenLoaded(
                'roles',
                fn() =>
                EventRoleResource::collection(
                    $this->roles->where('role', 'choir')->values()
                )
            ),
            'musicians'      => $this->whenLoaded(
                'roles',
                fn() =>
                EventRoleResource::collection(
                    $this->roles->where('role', 'musician')->values()
                )
            ),
            'technicians'    => $this->whenLoaded(
                'roles',
                fn() =>
                EventRoleResource::collection(
                    $this->roles->where('role', 'technician')->values()
                )
            ),
            'attendees'      => $this->whenLoaded(
                'attendees',
                fn() =>
                EventAttendeeResource::collection($this->attendees)
            ),
            'attendees_count' => $this->whenCounted('attendees'),
            'setlists'       => $this->whenLoaded(
                'setlists',
                fn() =>
                SetlistResource::collection($this->setlists)
            ),
            'created_at'     => $this->created_at->toDateTimeString(),
        ];
    }
}
