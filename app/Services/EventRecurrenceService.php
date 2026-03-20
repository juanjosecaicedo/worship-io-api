<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventRecurrence;
use App\Models\EventRecurrenceException;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class EventRecurrenceService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Create a new recurring event with its rule.
     * @param array $eventData
     * @param array $recurrenceData
     * @return Event
     */
    public function createRecurring(array $eventData, array $recurrenceData): Event
    {
        // Crear evento plantilla
        $event = Event::create([
            ...$eventData,
            'is_template' => true,
        ]);

        // Crear regla de recurrencia
        $event->recurrence()->create($recurrenceData);

        // Convocar a los miembros para las próximas N ocurrencias
        $this->conveneForUpcoming($event, 4);

        return $event->load('recurrence');
    }


    /**
     * Get events for a group in a date range (including recurring).
     * @param int $groupId
     * @param Carbon $from
     * @param Carbon $to
     * @return Collection
     */
    public function getEventsInRange(int $groupId, Carbon $from, Carbon $to): Collection
    {
        // Regular events (not templates) in the range
        $regular = Event::where('group_id', $groupId)
            ->where('is_template', false)
            ->whereBetween('start_datetime', [$from, $to])
            ->with(['roles.user', 'attendees'])
            ->get();

        // Generate occurrences of recurring events
        $templates   = Event::where('group_id', $groupId)
            ->where('is_template', true)
            ->with(['recurrence.exceptions.event'])
            ->get();

        $occurrences = collect();

        foreach ($templates as $template) {
            if (! $template->recurrence) continue;

            $generated = $template->recurrence->generateOccurrences($from, $to);

            foreach ($generated as $occ) {
                // If it's already materialized, show it as a real event
                if ($occ['status'] === 'materialized') continue;

                $occurrences->push([
                    'id'             => "recurring_{$occ['recurrence_id']}_{$occ['original_date']}",
                    'title'          => $occ['event']->title,
                    'type'           => $occ['event']->type,
                    'location'       => $occ['event']->location,
                    'start_datetime' => $occ['start_datetime'],
                    'end_datetime'   => $occ['end_datetime'],
                    'status'         => $occ['status'] === 'modified' ? 'scheduled' : 'scheduled',
                    'is_recurring'   => true,
                    'recurrence_id'  => $occ['recurrence_id'],
                    'original_date'  => $occ['original_date'],
                    'color'          => $occ['event']->color,
                    'group_id'       => $groupId,
                ]);
            }
        }

        // Combine and sort by date
        return $regular->concat($occurrences)
            ->sortBy('start_datetime')
            ->values();
    }

    /**
     * Materialize an occurrence (to be able to assign setlist, roles, etc.).
     * @param EventRecurrence $recurrence
     * @param string $originalDate
     * @return Event
     */
    public function materialize(
        EventRecurrence $recurrence,
        string $originalDate
    ): Event {
        $template  = $recurrence->event;
        $date      = Carbon::parse($originalDate);
        $startTime = Carbon::parse($template->start_datetime)->format('H:i:s');
        $endTime   = Carbon::parse($template->end_datetime)->format('H:i:s');

        // Create a real event based on the template
        $event = Event::create([
            'group_id'       => $template->group_id,
            'created_by'     => $template->created_by,
            'title'          => $template->title,
            'type'           => $template->type,
            'description'    => $template->description,
            'location'       => $template->location,
            'start_datetime' => $date->toDateString() . ' ' . $startTime,
            'end_datetime'   => $date->toDateString() . ' ' . $endTime,
            'status'         => 'scheduled',
            'color'          => $template->color,
            'is_template'    => false,
            'recurrence_id'  => $recurrence->id,
            'original_date'  => $originalDate,
        ]);

        // Register as a modified exception to not show the virtual one
        EventRecurrenceException::updateOrCreate(
            [
                'event_recurrence_id' => $recurrence->id,
                'original_date'       => $originalDate,
            ],
            [
                'type'     => 'modified',
                'event_id' => $event->id,
            ]
        );

        // Convene members of the group
        $this->conveneMembers($event);

        return $event;
    }

    /**
     * Cancel a specific occurrence.
     * @param EventRecurrence $recurrence
     * @param string $originalDate
     * @return void
     */
    public function cancelOccurrence(
        EventRecurrence $recurrence,
        string $originalDate
    ): void {
        EventRecurrenceException::updateOrCreate(
            [
                'event_recurrence_id' => $recurrence->id,
                'original_date'       => $originalDate,
            ],
            [
                'type'     => 'cancelled',
                'event_id' => null,
            ]
        );
    }

    /**
     * Edit from this occurrence onwards (split).
     * @param EventRecurrence $recurrence
     * @param string $fromDate
     * @param array $newEventData
     * @param array $newRecurrenceData
     * @return Event
     */
    public function editFromHere(
        EventRecurrence $recurrence,
        string $fromDate,
        array $newEventData,
        array $newRecurrenceData
    ): Event {
        // End the current recurrence on the previous date
        $recurrence->update([
            'ends_at' => Carbon::parse($fromDate)->subDay()->toDateString(),
        ]);

        // Create a new recurrence from that date
        $newEvent = Event::create([
            ...$newEventData,
            'group_id'    => $recurrence->event->group_id,
            'is_template' => true,
        ]);

        $newEvent->recurrence()->create([
            ...$newRecurrenceData,
            'starts_at' => $fromDate,
        ]);

        return $newEvent->load('recurrence');
    }

    /**
     * Convene members of the group to an event.
     * @param Event $event
     * @return void
     */
    private function conveneMembers(Event $event): void
    {
        $memberIds = $event->group->members()
            ->where('is_active', true)
            ->pluck('user_id');

        $attendees = $memberIds->map(fn($userId) => [
            'event_id'   => $event->id,
            'user_id'    => $userId,
            'status'     => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ])->toArray();

        $event->attendees()->insert($attendees);
    }

    /**
     * Convene members of the group to an event.
     * @param Event $template
     * @param int $next
     * @return void
     */
    private function conveneForUpcoming(Event $template, int $next = 4): void
    {
        $from        = now();
        $occurrences = $template->recurrence->generateOccurrences(
            $from,
            $from->copy()->addMonths(3)
        );

        foreach (array_slice($occurrences, 0, $next) as $occ) {
            $this->materialize($template->recurrence, $occ['original_date']);
        }
    }
}
