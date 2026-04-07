<?php

namespace App\Observers;

use App\Events\NewEvent;
use App\Events\UpdatedEvent;
use App\Models\Event;
use App\Services\NotificationService;

class EventObserver
{
    public function __construct(protected NotificationService $service) {}

    /**
     * Handle the Event "created" event.
     */
    public function created(Event $event): void
    {
        $this->service->notifyGroup(
            groupId: $event->group_id,
            type: 'event_created',
            title: __('notifications.event_created_title'),
            body: __('notifications.event_created_body', [
                'title' => $event->title,
                'datetime' => $event->start_datetime->format('d/m/Y H:i')
            ]),
            data: [
                'event_id' => $event->id,
                'group_id' => $event->group_id,
                'type'     => $event->type,
            ],
            channel: 'push',
        );

        NewEvent::dispatch($event);
    }

    /**
     * Handle the Event "updated" event.
     */
    public function updated(Event $event): void
    {
        if ($event->wasChanged('status') && $event->status === 'cancelled') {
            $this->service->notifyGroup(
                groupId: $event->group_id,
                type: 'event_cancelled',
                title: __('notifications.event_cancelled_title'),
                body: __('notifications.event_cancelled_body', ['title' => $event->title]),
                data: ['event_id' => $event->id, 'group_id' => $event->group_id],
                channel: 'push',
            );
            return;
        }

        if ($event->wasChanged(['title', 'start_datetime', 'location'])) {
            $this->service->notifyGroup(
                groupId: $event->group_id,
                type: 'event_updated',
                title: __('notifications.event_updated_title'),
                body: __('notifications.event_updated_body', ['title' => $event->title]),
                data: ['event_id' => $event->id, 'group_id' => $event->group_id],
                channel: 'in_app',
            );
        }

        UpdatedEvent::dispatch($event);
    }

    /**
     * Handle the Event "deleted" event.
     */
    public function deleted(Event $event): void
    {
        //
    }

    /**
     * Handle the Event "restored" event.
     */
    public function restored(Event $event): void
    {
        //
    }

    /**
     * Handle the Event "force deleted" event.
     */
    public function forceDeleted(Event $event): void
    {
        //
    }
}
