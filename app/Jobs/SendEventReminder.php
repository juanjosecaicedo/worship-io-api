<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\Reminder;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendEventReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */

    public int $tries   = 3;
    public int $timeout = 90;

    public function __construct(
        public Reminder $reminder
    ) {
        $this->onQueue('reminders');
    }

    /**
     * Execute the job.
     */
    public function handle(NotificationService $service): void
    {
        $event = $this->reminder->event;

        if (! $event || $event->status === 'cancelled') {
            $this->reminder->update(['is_sent' => true, 'sent_at' => now()]);
            return;
        }

        // Obtener todos los asistentes confirmados o pendientes
        $attendees = $event->attendees()
            ->whereIn('status', ['pending', 'confirmed'])
            ->with('user')
            ->get();

        foreach ($attendees as $attendee) {
            $service->send(
                user: $attendee->user,
                type: 'event_reminder',
                title: "Recordatorio: {$event->title}",
                body: $this->buildBody($event),
                data: [
                    'event_id'   => $event->id,
                    'group_id'   => $event->group_id,
                    'type'       => $event->type,
                    'start_date' => $event->start_datetime->toDateTimeString(),
                ],
                channel: $this->reminder->channel,
            );
        }

        $this->reminder->update([
            'is_sent' => true,
            'sent_at' => now(),
        ]);
    }

    private function buildBody(Event $event): string
    {
        $date = $event->start_datetime->format('d/m/Y H:i');
        $location = $event->location ? " en {$event->location}" : '';

        return "El evento \"{$event->title}\" comienza el {$date}{$location}.";
    }
}
