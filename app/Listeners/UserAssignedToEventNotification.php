<?php

namespace App\Listeners;

use App\Events\UserAssignedToEvent;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UserAssignedToEventNotification
{
    /**
     * Create the event listener.
     */
    public function __construct(private NotificationService $service) {}

    /**
     * Handle the event.
     */
    public function handle(UserAssignedToEvent $event): void
    {
        $eventRole = $event->eventRole;
        $event = $eventRole->event;
        $user = $eventRole->user;

        $this->service->send(
            user: $user,
            type: 'user_assigned_to_event',
            title: __('notifications.user_assigned_to_event_title'),
            body: __('notifications.user_assigned_to_event_body', [
                'event' => $event->title,
                'role' => __("events.role_{$eventRole->role}")
            ]),
            data: [
                'event_id' => $event->id,
                'role'     => $eventRole->role,
            ],
            channel: 'push',
        );
    }
}
