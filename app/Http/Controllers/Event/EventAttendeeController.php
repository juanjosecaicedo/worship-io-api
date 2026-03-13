<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use App\Http\Requests\Event\RespondAttendanceRequest;
use App\Http\Resources\EventAttendeeResource;
use App\Models\Event;
use App\Models\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventAttendeeController extends Controller
{
    public function index(Request $request, Group $group, Event $event): JsonResponse
    {
        abort_unless($group->hasMember($request->user()->id), 403);
        abort_if($event->group_id !== $group->id, 404);

        $attendees = $event->attendees()
            ->with('user')
            ->get()
            ->groupBy('status');

        return response()->json([
            'data' => [
                'confirmed' => EventAttendeeResource::collection($attendees->get('confirmed', collect())),
                'pending'   => EventAttendeeResource::collection($attendees->get('pending', collect())),
                'declined'  => EventAttendeeResource::collection($attendees->get('declined', collect())),
                'attended'  => EventAttendeeResource::collection($attendees->get('attended', collect())),
                'absent'    => EventAttendeeResource::collection($attendees->get('absent', collect())),
            ],
        ]);
    }

    /**
     * respond to invitation (user confirms or declines)
     * @param RespondAttendanceRequest $request
     * @param Group $group
     * @param Event $event
     * @return JsonResponse
     */
    public function respond(RespondAttendanceRequest $request, Group $group, Event $event): JsonResponse
    {
        abort_unless($group->hasMember($request->user()->id), 403);
        abort_if($event->group_id !== $group->id, 404);

        $attendee = $event->attendees()
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $attendee->update([
            'status'       => $request->status,
            'notes'        => $request->notes,
            'responded_at' => now(),
        ]);

        return response()->json([
            'message' => 'Respuesta registrada correctamente.',
            'data'    => new EventAttendeeResource($attendee),
        ]);
    }

    /**
     * Mark actual attendance (post-event) — admin/leader only
     * @param Request $request
     * @param Group $group
     * @param Event $event
     * @return JsonResponse
     */
    public function markAttendance(Request $request, Group $group, Event $event): JsonResponse
    {
        abort_unless($group->isAdminOrLeader($request->user()->id), 403);
        abort_if($event->group_id !== $group->id, 404);

        $request->validate([
            'attendances'            => ['required', 'array'],
            'attendances.*.user_id'  => ['required', 'exists:users,id'],
            'attendances.*.attended' => ['required', 'boolean'],
        ]);

        foreach ($request->attendances as $attendance) {
            $event->attendees()
                ->where('user_id', $attendance['user_id'])
                ->update([
                    'status' => $attendance['attended'] ? 'attended' : 'absent',
                ]);
        }

        return response()->json([
            'message' => 'Asistencia registrada correctamente.',
        ]);
    }
}
