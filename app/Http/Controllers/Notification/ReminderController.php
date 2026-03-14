<?php

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use App\Http\Requests\Notification\CreateReminderRequest;
use App\Http\Resources\ReminderResource;
use App\Models\Event;
use App\Models\Group;
use App\Models\Reminder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReminderController extends Controller
{
    /**
     * List reminders for an event
     */
    public function index(Request $request, Group $group, Event $event): JsonResponse
    {
        abort_unless($group->hasMember($request->user()->id), 403);
        abort_if($event->group_id !== $group->id, 404);

        $reminders = Reminder::where('event_id', $event->id)
            ->orderBy('minutes_before')
            ->get();

        return response()->json([
            'data' => ReminderResource::collection($reminders),
            'presets' => Reminder::PRESET_OPTIONS,
        ]);
    }

    /**
     * Create a reminder for an event
     */
    public function store(CreateReminderRequest $request, Group $group, Event $event): JsonResponse
    {
        abort_unless($group->isAdminOrLeader($request->user()->id), 403);
        abort_if($event->group_id !== $group->id, 404);

        // Verificar que el evento no haya pasado
        abort_if(
            $event->start_datetime->isPast(),
            422,
            'No se pueden agregar recordatorios a eventos pasados.'
        );

        // Verificar que no exista ya el mismo recordatorio
        $exists = Reminder::where('event_id', $event->id)
            ->where('minutes_before', $request->minutes_before)
            ->where('channel', $request->channel)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Ya existe un recordatorio con estos mismos parámetros.',
            ], 409);
        }

        $reminder = Reminder::create([
            ...$request->validated(),
            'event_id' => $event->id,
        ]);

        return response()->json([
            'message' => 'Recordatorio creado correctamente.',
            'data' => new ReminderResource($reminder),
        ], 201);
    }

    /**
     * Delete a reminder
     */
    public function destroy(Request $request, Group $group, Event $event, Reminder $reminder): JsonResponse
    {
        abort_unless($group->isAdminOrLeader($request->user()->id), 403);
        abort_if($event->group_id !== $group->id, 404);
        abort_if($reminder->event_id !== $event->id, 404);
        abort_if($reminder->is_sent, 422, 'No se puede eliminar un recordatorio ya enviado.');

        $reminder->delete();

        return response()->json([
            'message' => 'Recordatorio eliminado correctamente.',
        ]);
    }
}
