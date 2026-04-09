<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use App\Http\Requests\Event\CreateEventRequest;
use App\Http\Requests\Event\UpdateEventRequest;
use App\Http\Requests\Event\CreateRecurringEventRequest;
use App\Http\Requests\Event\ListEventsRequest;
use App\Http\Requests\Event\UpdateOccurrenceRequest;
use App\Http\Resources\EventResource;
use App\Services\EventRecurrenceService;
use App\Models\Event;
use App\Models\EventRecurrence;
use App\Models\Group;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function __construct(
        private readonly EventRecurrenceService $recurrenceService
    ) {}

    /**
     * List all events of a group
     * 
     * @param ListEventsRequest $request
     * @param Group $group
     * @return \Illuminate\Http\Resources\JsonApi\AnonymousResourceCollection
     */
    public function index(ListEventsRequest $request, Group $group): \Illuminate\Http\Resources\JsonApi\AnonymousResourceCollection
    {
        abort_unless($group->hasMember($request->user()->id), 403);

        $from = now()->startOfMonth();
        $to = now()->endOfMonth();

        if ($request->filled('from')) {
            $from = Carbon::parse($request->from)->startOfDay();
        }
        if ($request->filled('to')) {
            $to = Carbon::parse($request->to)->endOfDay();
        }

        // Filters for month and year
        if ($request->filled('month')) {
            $from = $from->month($request->month)->startOfMonth();
            $to = $from->copy()->endOfMonth();
        }
        if ($request->filled('year')) {
            $from = $from->year($request->year);
            $to = $to->year($request->year);
        }

        // Filters for upcoming/past
        if ($request->boolean('upcoming')) {
            $from = now();
            $to = now()->addMonths(6); // Default 6 months for upcoming if not specified
        } elseif ($request->boolean('past')) {
            $from = now()->subMonths(6);
            $to = now();
        }

        $filters = $request->only(['type', 'status']);

        // Obtener eventos (reales y virtuales de recurrencia)
        $events = $this->recurrenceService->getEventsInRange($group->id, $from, $to, $filters);

        $perPage = $request->integer('per_page', 20);
        $page = $request->integer('page', 1);

        // Paginación manual ya que getEventsInRange devuelve una Collection, no un Query Builder
        $paginatedEvents = new \Illuminate\Pagination\LengthAwarePaginator(
            $events->forPage($page, $perPage)->values(),
            $events->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return EventResource::collection($paginatedEvents);
    }

    /**
     * Create a new event
     * 
     * @param CreateEventRequest $request
     * @param Group $group
     * @return JsonResponse
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

        $attendees = $memberIds->map(fn($userId) => [
            'event_id' => $event->id,
            'user_id' => $userId,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ])->toArray();

        $event->attendees()->insert($attendees);

        return response()->json([
            'message' => __('events.created_success'),
            'data' => new EventResource($event->load('creator', 'attendees.user')),
        ], 201);
    }

    /**
     * Get an event by id
     * 
     * @param Request $request
     * @param Group $group
     * @param Event $event
     * @return JsonResponse
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
     * 
     * @param UpdateEventRequest $request
     * @param Group $group
     * @param Event $event
     * @return JsonResponse
     */
    public function update(UpdateEventRequest $request, Group $group, Event $event): JsonResponse
    {
        abort_unless($group->isAdminOrLeader($request->user()->id), 403);
        abort_if($event->group_id !== $group->id, 404);

        $event->update($request->validated());

        return response()->json([
            'message' => __('events.updated_success'),
            'data' => new EventResource($event->load('creator', 'roles.user')),
        ]);
    }

    /**
     * Cancel an event
     * 
     * @param Request $request
     * @param Group $group
     * @param Event $event
     * @return JsonResponse
     */
    public function destroy(Request $request, Group $group, Event $event): JsonResponse
    {
        abort_unless($group->isAdminOrLeader($request->user()->id), 403);
        abort_if($event->group_id !== $group->id, 404);

        $event->update(['status' => 'cancelled']);

        return response()->json([
            'message' => __('events.cancelled_success'),
        ]);
    }


    /**
     * Create a new recurring event
     * 
     * @param CreateRecurringEventRequest $request
     * @param Group $group
     * @return JsonResponse
     */
    public function storeRecurring(CreateRecurringEventRequest $request, Group $group): JsonResponse
    {
        abort_unless($group->isAdminOrLeader($request->user()->id), 403);

        // Armar fechas completas desde la fecha de inicio + hora
        $startsAt  = Carbon::parse($request->input('recurrence.starts_at'));

        $eventData = [
            ...$request->except(['recurrence', 'start_time', 'end_time']),
            'group_id'       => $group->id,
            'created_by'     => $request->user()->id,
            'start_datetime' => $startsAt->toDateString() . ' ' . $request->start_time,
            'end_datetime'   => $startsAt->toDateString() . ' ' . $request->end_time,
            'status'         => 'scheduled',
        ];

        $recurrenceData = [
            ...$request->input('recurrence'),
            'interval' => $request->input('recurrence.interval', 1),
        ];

        $event = $this->recurrenceService->createRecurring($eventData, $recurrenceData);

        return response()->json([
            'message' => __('events.recurring_created_success'),
            'data'    => new EventResource($event),
        ], 201);
    }

    /**
     * Materialize an occurrence (to assign setlist, roles, etc.)
     * 
     * @param Request $request
     * @param Group $group
     * @param int $recurrenceId
     * @param string $date
     * @return JsonResponse
     */
    public function materializeOccurrence(Request $request, Group $group, int $recurrenceId, string $date): JsonResponse
    {
        abort_unless($group->isAdminOrLeader($request->user()->id), 403);

        $recurrence = EventRecurrence::findOrFail($recurrenceId);
        abort_if($recurrence->event->group_id !== $group->id, 403);

        // Verificar que no esté ya materializado
        $existing = Event::where('recurrence_id', $recurrenceId)
            ->where('original_date', $date)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => __('events.occurrence_already_materialized'),
                'data'    => new EventResource($existing),
            ]);
        }

        $event = $this->recurrenceService->materialize($recurrence, $date);

        return response()->json([
            'message' => __('events.occurrence_ready'),
            'data'    => new EventResource($event->load('roles.user', 'attendees.user')),
        ], 201);
    }

    /**
     * Update an occurrence with scope
     * 
     * @param UpdateOccurrenceRequest $request
     * @param Group $group
     * @param int $recurrenceId
     * @param string $date
     * @return JsonResponse
     */
    public function updateOccurrence(
        UpdateOccurrenceRequest $request,
        Group $group,
        int $recurrenceId,
        string $date
    ): JsonResponse {
        abort_unless($group->isAdminOrLeader($request->user()->id), 403);

        $recurrence = EventRecurrence::with('event')->findOrFail($recurrenceId);
        abort_if($recurrence->event->group_id !== $group->id, 403);

        match ($request->scope) {
            'this' => $this->handleThisOccurrence($request, $recurrence, $date),

            'this_and_following' => $this->recurrenceService->editFromHere(
                $recurrence,
                $date,
                $request->only(['title', 'description', 'location', 'start_time', 'end_time', 'color']),
                $recurrence->only(['frequency', 'interval', 'days_of_week', 'day_of_month', 'ends_at'])
            ),

            'all' => $recurrence->event->update(
                $request->only(['title', 'description', 'location', 'color'])
            ),
        };

        return response()->json([
            'message' => __('events.occurrence_updated'),
        ]);
    }

    /**
     * Handle this occurrence
     * 
     * @param UpdateOccurrenceRequest $request
     * @param EventRecurrence $recurrence
     * @param string $date
     * @return void
     */
    private function handleThisOccurrence(
        UpdateOccurrenceRequest $request,
        EventRecurrence $recurrence,
        string $date
    ): void {
        if ($request->status === 'cancelled') {
            $this->recurrenceService->cancelOccurrence($recurrence, $date);
            return;
        }

        // Materializar y actualizar
        $existing = Event::where('recurrence_id', $recurrence->id)
            ->where('original_date', $date)
            ->first();

        $event = $existing ?? $this->recurrenceService->materialize($recurrence, $date);
        $event->update($request->only(['title', 'description', 'location', 'color']));
    }
}
