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
     * See my preferred ringtone for this song
     * @param Request $request
     * @param Group $group
     * @param GroupSong $groupSong
     * @return JsonResponse
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
     * Create or update my favorite ringtone
     * @param StoreUserSongKeyRequest $request
     * @param Group $group
     * @param GroupSong $groupSong
     * @return JsonResponse
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
            'message' => 'Tono preferido guardado correctamente.',
            'data'    => new UserSongKeyResource($key),
        ]);
    }

    /**
     * Delete my favorite ringtone
     * @param Request $request
     * @param Group $group
     * @param GroupSong $groupSong
     * @return JsonResponse
     */
    public function destroy(Request $request, Group $group, GroupSong $groupSong): JsonResponse
    {
        abort_unless($group->hasMember($request->user()->id), 403);
        abort_if($groupSong->group_id !== $group->id, 404);

        $groupSong->userSongKeys()
            ->where('user_id', $request->user()->id)
            ->delete();

        return response()->json([
            'message' => 'Tono preferido eliminado correctamente.',
        ]);
    }
}
