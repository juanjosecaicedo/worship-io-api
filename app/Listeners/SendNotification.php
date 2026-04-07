<?php

namespace App\Listeners;

use App\Events\NewEvent;
use Illuminate\Contracts\Queue\ShouldQueue;


class SendNotification implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(NewEvent $event): void {}
}
