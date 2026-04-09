<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;


class EventResource extends JsonApiResource
{
    /**
     * Get the resource's ID.
     */
    public function toId(Request $request): string
    {
        return (string) $this->id;
    }

    /**
     * Get the resource's type.
     */
    public function toType(Request $request): string
    {
        return 'events';
    }

    /**
     * Transform the resource into an array of attributes.
     */
    public function toAttributes(Request $request): array
    {
        return [
            'title'          => $this->title,
            'type'           => $this->type,
            'description'    => $this->description,
            'location'       => $this->location,
            'start_datetime' => $this->start_datetime instanceof \Carbon\Carbon
                ? $this->start_datetime->toDateTimeString()
                : $this->start_datetime,
            'end_datetime'   => $this->end_datetime instanceof \Carbon\Carbon
                ? $this->end_datetime->toDateTimeString()
                : $this->end_datetime,
            'status'         => $this->status,
            'color'          => $this->color,
            'is_recurring'   => $this->is_recurring ?? false,
            'recurrence_id'  => $this->recurrence_id,
            'original_date'  => $this->original_date,
            'gcal_event_id'  => $this->gcal_event_id,
            'created_at'     => $this->created_at instanceof \Carbon\Carbon
                ? $this->created_at->toDateTimeString()
                : null,
        ];
    }

    /**
     * Get the resource's relationships.
     */
    public function toRelationships(Request $request): array
    {
        return [
            'creator'        => UserResource::class,
            // Roles agrupados por tipo
            /*'band_director'  => $this->roles->where('role', 'band_director')->first(),
            'vocalists'      => $this->roles->where('role', 'vocalist')->values(),
            'choir'          => $this->roles->where('role', 'choir')->values(),
            'musicians'      => $this->roles->where('role', 'musician')->values(),
            'technicians'    => $this->roles->where('role', 'technician')->values(),*/
            'attendees'      => EventAttendeeResource::class,
            'setlists'       => SetlistResource::class,
        ];
    }
}
