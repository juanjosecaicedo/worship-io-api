<?php

namespace App\Http\Controllers\Setlist;

use App\Http\Controllers\Controller;
use App\Http\Requests\Setlist\AssignVocalistRequest;
use App\Http\Resources\SetlistVocalistResource;
use App\Models\Event;
use App\Models\Group;
use App\Models\Setlist;
use App\Models\SetlistSong;
use App\Models\SetlistSongVocalist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SetlistVocalistController extends Controller
{
    /**
     * Assign vocalist to a song in the setlist
     */
    public function store(
        AssignVocalistRequest $request,
        Group $group,
        Event $event,
        Setlist $setlist,
        SetlistSong $setlistSong
    ): JsonResponse {
        abort_unless($group->isAdminOrLeader($request->user()->id), 403);
        abort_if($event->group_id !== $group->id, 404);
        abort_if($setlist->event_id !== $event->id, 404);
        abort_if($setlistSong->setlist_id !== $setlist->id, 404);

        // Verify that the vocalist is a member of the group
        abort_unless(
            $group->hasMember($request->user_id),
            422,
            'The user is not a member of this group.'
        );

        // Verify that the vocalist is not duplicated
        $exists = $setlistSong->vocalists()
            ->where('user_id', $request->user_id)
            ->where('vocal_role', $request->vocal_role)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'This vocalist already has this role in this song.',
            ], 409);
        }

        // If the key does not come in the request, search for the vocalist's preferred key
        $keyOverride = $request->key_override;

        if (! $keyOverride) {
            $userKey = $setlistSong->groupSong
                ->userSongKeys()
                ->where('user_id', $request->user_id)
                ->first();

            $keyOverride = $userKey?->preferred_key;
        }

        $vocalist = $setlistSong->vocalists()->create([
            ...$request->validated(),
            'key_override' => $keyOverride,
        ]);

        return response()->json([
            'message' => 'Vocalist assigned successfully.',
            'data' => new SetlistVocalistResource($vocalist->load('user')),
        ], 201);
    }

    /**
     * Update vocalist assignment
     */
    public function update(
        AssignVocalistRequest $request,
        Group $group,
        Event $event,
        Setlist $setlist,
        SetlistSong $setlistSong,
        SetlistSongVocalist $vocalist
    ): JsonResponse {
        abort_unless($group->isAdminOrLeader($request->user()->id), 403);
        abort_if($event->group_id !== $group->id, 404);
        abort_if($setlist->event_id !== $event->id, 404);
        abort_if($setlistSong->setlist_id !== $setlist->id, 404);
        abort_if($vocalist->setlist_song_id !== $setlistSong->id, 404);

        $vocalist->update($request->validated());

        return response()->json([
            'message' => 'Vocalist assignment updated successfully.',
            'data' => new SetlistVocalistResource($vocalist->load('user')),
        ]);
    }

    /**
     * Remove vocalist from song
     */
    public function destroy(
        Request $request,
        Group $group,
        Event $event,
        Setlist $setlist,
        SetlistSong $setlistSong,
        SetlistSongVocalist $vocalist
    ): JsonResponse {
        abort_unless($group->isAdminOrLeader($request->user()->id), 403);
        abort_if($event->group_id !== $group->id, 404);
        abort_if($setlist->event_id !== $event->id, 404);
        abort_if($setlistSong->setlist_id !== $setlist->id, 404);
        abort_if($vocalist->setlist_song_id !== $setlistSong->id, 404);

        $vocalist->delete();

        return response()->json([
            'message' => 'Vocalist removed from song successfully.',
        ]);
    }
}
