<?php

namespace App\Http\Controllers\Setlist;

use App\Http\Controllers\Controller;
use App\Http\Requests\Setlist\AddSongToSetlistRequest;
use App\Http\Requests\Setlist\ReorderSetlistRequest;
use App\Http\Requests\Setlist\UpdateSetlistSongRequest;
use App\Http\Resources\SetlistSongResource;
use App\Models\Event;
use App\Models\Group;
use App\Models\GroupSong;
use App\Models\Setlist;
use App\Models\SetlistSong;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SetlistSongController extends Controller
{
    /**
     * Add song to setlist
     */
    public function store(AddSongToSetlistRequest $request, Group $group, Event $event, Setlist $setlist): JsonResponse
    {
        abort_unless($group->isAdminOrLeader($request->user()->id), 403);
        abort_if($event->group_id !== $group->id, 404);
        abort_if($setlist->event_id !== $event->id, 404);

        // Verify that the song belongs to the group
        $songBelongsToGroup = $group->id === GroupSong::find($request->group_song_id)?->group_id;
        abort_unless($songBelongsToGroup, 422, 'The song does not belong to this group.');

        // Verify that the song is not duplicated in the setlist
        $exists = $setlist->songs()
            ->where('group_song_id', $request->group_song_id)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'This song is already in the setlist.',
            ], 409);
        }

        $setlistSong = $setlist->songs()->create($request->validated());

        return response()->json([
            'message' => 'Song added to setlist successfully.',
            'data' => new SetlistSongResource($setlistSong->load('groupSong')),
        ], 201);
    }

    /**
     * Update setlist song
     */
    public function update(
        UpdateSetlistSongRequest $request,
        Group $group,
        Event $event,
        Setlist $setlist,
        SetlistSong $setlistSong
    ): JsonResponse {
        abort_unless($group->isAdminOrLeader($request->user()->id), 403);
        abort_if($event->group_id !== $group->id, 404);
        abort_if($setlist->event_id !== $event->id, 404);
        abort_if($setlistSong->setlist_id !== $setlist->id, 404);

        $setlistSong->update($request->validated());

        return response()->json([
            'message' => 'Setlist song updated successfully.',
            'data' => new SetlistSongResource($setlistSong->load('groupSong', 'vocalists.user')),
        ]);
    }

    /**
     * Reorder setlist songs
     */
    public function reorder(ReorderSetlistRequest $request, Group $group, Event $event, Setlist $setlist): JsonResponse
    {
        abort_unless($group->isAdminOrLeader($request->user()->id), 403);
        abort_if($event->group_id !== $group->id, 404);
        abort_if($setlist->event_id !== $event->id, 404);

        foreach ($request->songs as $item) {
            SetlistSong::where('id', $item['id'])
                ->where('setlist_id', $setlist->id)
                ->update(['order' => $item['order']]);
        }

        return response()->json([
            'message' => 'Setlist reordered successfully.',
            'data' => SetlistSongResource::collection(
                $setlist->songs()->with('groupSong')->get()
            ),
        ]);
    }

    /**
     * Delete setlist song
     */
    public function destroy(
        Request $request,
        Group $group,
        Event $event,
        Setlist $setlist,
        SetlistSong $setlistSong
    ): JsonResponse {
        abort_unless($group->isAdminOrLeader($request->user()->id), 403);
        abort_if($event->group_id !== $group->id, 404);
        abort_if($setlist->event_id !== $event->id, 404);
        abort_if($setlistSong->setlist_id !== $setlist->id, 404);

        $setlistSong->delete();

        return response()->json([
            'message' => 'Song deleted from setlist successfully.',
        ]);
    }
}
