<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use App\Http\Requests\Event\CreateEventRequest;
use App\Http\Requests\Event\UpdateEventRequest;
use App\Http\Resources\EventResource;
use App\Models\Event;
use App\Models\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * List all events of a group
     */
    public function index(Request $request, Group $group): JsonResponse
    {
        abort_unless($group->hasMember($request->user()->id), 403);

        $query = Event::where('group_id', $group->id)
            ->withCount('attendees')
            ->with('creator');

        // Filtros
        if ($request->filled('type')) {
            $query->byType($request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('month') && $request->filled('year')) {
            $query->byMonth($request->year, $request->month);
        }

        // Próximos o pasados
        if ($request->boolean('upcoming')) {
            $query->upcoming();
        } elseif ($request->boolean('past')) {
            $query->past();
        } else {
            $query->orderBy('start_datetime');
        }

        $events = $query->paginate($request->per_page ?? 20);

        return response()->json([
            'data' => EventResource::collection($events),
            'meta' => [
                'current_page' => $events->currentPage(),
                'last_page' => $events->lastPage(),
                'per_page' => $events->perPage(),
                'total' => $events->total(),
            ],
        ]);
    }

    /**
     * Create a new event
     */
    public function store(CreateEventRequest $request, Group $group): JsonResponse
    {
        abort_unless($group->isAdminOrLeader($request->user()->id), 403);

        $event = Event::create([
            ...$request->validated(),
            'group_id' => $group->id,
            'created_by' => $request->user()->id,
        ]);

        // Convocar automáticamente a todos los miembros activos del grupo
        $memberIds = $group->members()
            ->where('is_active', true)
            ->pluck('user_id');

        $attendees = $memberIds->map(fn ($userId) => [
            'event_id' => $event->id,
            'user_id' => $userId,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ])->toArray();

        $event->attendees()->insert($attendees);

        return response()->json([
            'message' => 'Evento creado correctamente.',
            'data' => new EventResource($event->load('creator', 'attendees.user')),
        ], 201);
    }

    /**
     * Get an event by id
     */
    public function show(Request $request, Group $group, Event $event): JsonResponse
    {
        abort_unless($group->hasMember($request->user()->id), 403);
        abort_if($event->group_id !== $group->id, 404);

        $event->load([
            'creator',
            'roles.user',
            'attendees.user',
            'setlists',
        ]);

        return response()->json([
            'data' => new EventResource($event),
        ]);
    }

    /**
     * Update an event
     */
    public function update(UpdateEventRequest $request, Group $group, Event $event): JsonResponse
    {
        abort_unless($group->isAdminOrLeader($request->user()->id), 403);
        abort_if($event->group_id !== $group->id, 404);

        $event->update($request->validated());

        return response()->json([
            'message' => 'Evento actualizado correctamente.',
            'data' => new EventResource($event->load('creator', 'roles.user')),
        ]);
    }

    /**
     * Cancel an event
     */
    public function destroy(Request $request, Group $group, Event $event): JsonResponse
    {
        abort_unless($group->isAdminOrLeader($request->user()->id), 403);
        abort_if($event->group_id !== $group->id, 404);

        $event->update(['status' => 'cancelled']);

        return response()->json([
            'message' => 'Evento cancelado correctamente.',
        ]);
    }
}
