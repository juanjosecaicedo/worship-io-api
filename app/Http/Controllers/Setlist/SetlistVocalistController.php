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
     * @param AssignVocalistRequest $request
     * @param Group $group
     * @param Event $event
     * @param Setlist $setlist
     * @param SetlistSong $setlistSong
     * @return JsonResponse
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

        // Verificar que el vocalista es miembro del grupo
        abort_unless(
            $group->hasMember($request->user_id),
            422,
            'El usuario no es miembro de este grupo.'
        );

        // Verificar que no esté duplicado
        $exists = $setlistSong->vocalists()
            ->where('user_id', $request->user_id)
            ->where('vocal_role', $request->vocal_role)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Este vocalista ya tiene este rol en esta canción.',
            ], 409);
        }

        // Si el tono no viene en el request, buscar el tono preferido del vocalista
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
            'message' => 'Vocalista asignado correctamente.',
            'data'    => new SetlistVocalistResource($vocalist->load('user')),
        ], 201);
    }


    /**
     * Update vocalist assignment
     * @param AssignVocalistRequest $request
     * @param Group $group
     * @param Event $event
     * @param Setlist $setlist
     * @param SetlistSong $setlistSong
     * @param SetlistSongVocalist $vocalist
     * @return JsonResponse
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
            'message' => 'Asignación vocal actualizada.',
            'data'    => new SetlistVocalistResource($vocalist->load('user')),
        ]);
    }


    /**
     * Remove vocalist from song
     * @param Request $request
     * @param Group $group
     * @param Event $event
     * @param Setlist $setlist
     * @param SetlistSong $setlistSong
     * @param SetlistSongVocalist $vocalist
     * @return JsonResponse
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
            'message' => 'Vocalista eliminado de la canción.',
        ]);
    }
}
