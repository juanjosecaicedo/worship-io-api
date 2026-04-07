<?php

namespace App\Observers;

use App\Events\UserAssignedToEvent;
use App\Models\EventRole;

class EventRolesObserver
{
    /**
     * Handle the EventRole "created" event.
     */
    public function created(EventRole $eventRole): void
    {
        UserAssignedToEvent::dispatch($eventRole);
    }

    /**
     * Handle the EventRole "updated" event.
     */
    public function updated(EventRole $eventRole): void
    {
        //
    }

    /**
     * Handle the EventRole "deleted" event.
     */
    public function deleted(EventRole $eventRole): void
    {
        //
    }

    /**
     * Handle the EventRole "restored" event.
     */
    public function restored(EventRole $eventRole): void
    {
        //
    }

    /**
     * Handle the EventRole "force deleted" event.
     */
    public function forceDeleted(EventRole $eventRole): void
    {
        //
    }
}
