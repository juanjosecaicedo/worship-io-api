<?php

namespace App\Http\Controllers\Setlist;

use App\Http\Controllers\Controller;
use App\Http\Requests\Setlist\CreateSetlistRequest;
use App\Http\Requests\Setlist\UpdateSetlistRequest;
use App\Http\Resources\SetlistResource;
use App\Models\Event;
use App\Models\Group;
use App\Models\Setlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SetlistController extends Controller
{
    /**
     * List setlists for the event
     */
    public function index(Request $request, Group $group, Event $event): JsonResponse
    {
        abort_unless($group->hasMember($request->user()->id), 403);
        abort_if($event->group_id !== $group->id, 404);

        $setlists = Setlist::where('event_id', $event->id)
            ->withCount('songs')
            ->with('creator')
            ->get();

        return response()->json([
            'data' => SetlistResource::collection($setlists),
        ]);
    }

    /**
     * Create setlist for the event
     */
    public function store(CreateSetlistRequest $request, Group $group, Event $event): JsonResponse
    {
        abort_unless($group->isAdminOrLeader($request->user()->id), 403);
        abort_if($event->group_id !== $group->id, 404);

        $setlist = Setlist::create([
            ...$request->validated(),
            'event_id' => $event->id,
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Setlist created successfully.',
            'data' => new SetlistResource($setlist->load('creator')),
        ], 201);
    }

    /**
     * See the full setlist with all songs and vocalists
     */
    public function show(Request $request, Group $group, Event $event, Setlist $setlist): JsonResponse
    {
        abort_unless($group->hasMember($request->user()->id), 403);
        abort_if($event->group_id !== $group->id, 404);
        abort_if($setlist->event_id !== $event->id, 404);

        $setlist->load([
            'creator',
            'songs.groupSong.sections',
            'songs.vocalists.user',
        ]);

        return response()->json([
            'data' => new SetlistResource($setlist),
        ]);
    }

    /**
     * Update setlist
     */
    public function update(UpdateSetlistRequest $request, Group $group, Event $event, Setlist $setlist): JsonResponse
    {
        abort_unless($group->isAdminOrLeader($request->user()->id), 403);
        abort_if($event->group_id !== $group->id, 404);
        abort_if($setlist->event_id !== $event->id, 404);

        $setlist->update($request->validated());

        return response()->json([
            'message' => 'Setlist updated successfully.',
            'data' => new SetlistResource($setlist->fresh('creator')),
        ]);
    }

    /**
     * Delete setlist
     */
    public function destroy(Request $request, Group $group, Event $event, Setlist $setlist): JsonResponse
    {
        abort_unless($group->isAdminOrLeader($request->user()->id), 403);
        abort_if($event->group_id !== $group->id, 404);
        abort_if($setlist->event_id !== $event->id, 404);

        $setlist->delete();

        return response()->json([
            'message' => 'Setlist deleted successfully.',
        ]);
    }
}
