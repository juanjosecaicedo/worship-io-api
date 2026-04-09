<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class NotificationResource extends JsonApiResource
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
        return 'notifications';
    }

    /**
     * Transform the resource into an array of attributes.
     */
    public function toAttributes(Request $request): array
    {
        return [
            'type'       => $this->type,
            'title'      => $this->title,
            'body'       => $this->body,
            'data'       => $this->data,
            'channel'    => $this->channel,
            'is_read'    => $this->isRead(),
            'read_at'    => $this->read_at instanceof \Carbon\Carbon 
                ? $this->read_at->toDateTimeString() 
                : $this->read_at,
            'created_at' => $this->created_at instanceof \Carbon\Carbon 
                ? $this->created_at->toDateTimeString() 
                : $this->created_at,
        ];
    }
}
