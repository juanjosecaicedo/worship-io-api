<?php

namespace App\Http\Controllers\GroupSong;

use App\Http\Controllers\Controller;
use App\Http\Requests\GroupSong\StoreUserSongKeyRequest;
use App\Http\Resources\UserSongKeyResource;
use App\Models\Group;
use App\Models\GroupSong;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserSongKeyController extends Controller
{
    /**
     * Get preferred key
     * 
     * Returns the user's preferred key for a specific group song.
     */
    public function show(Request $request, Group $group, GroupSong $groupSong): JsonResponse
    {
        abort_unless($group->hasMember($request->user()->id), 403);
        abort_if($groupSong->group_id !== $group->id, 404);

        $key = $groupSong->userSongKeys()
            ->where('user_id', $request->user()->id)
            ->first();

        return response()->json([
            'data' => $key ? new UserSongKeyResource($key) : null,
        ]);
    }

    /**
     * Save preferred key
     * 
     * Creates or updates the user's preferred key and capo for a group song.
     */
    public function upsert(StoreUserSongKeyRequest $request, Group $group, GroupSong $groupSong): JsonResponse
    {
        abort_unless($group->hasMember($request->user()->id), 403);
        abort_if($groupSong->group_id !== $group->id, 404);

        $key = $groupSong->userSongKeys()->updateOrCreate(
            ['user_id' => $request->user()->id],
            $request->validated()
        );

        return response()->json([
            'message' => __('group_songs.user_key_saved'),
            'data' => new UserSongKeyResource($key),
        ]);
    }

    /**
     * Delete preferred key
     */
    public function destroy(Request $request, Group $group, GroupSong $groupSong): JsonResponse
    {
        abort_unless($group->hasMember($request->user()->id), 403);
        abort_if($groupSong->group_id !== $group->id, 404);

        $groupSong->userSongKeys()
            ->where('user_id', $request->user()->id)
            ->delete();

        return response()->json([
            'message' => __('group_songs.user_key_deleted'),
        ]);
    }
}
