<?php

namespace App\Http\Controllers\GroupSong;

use App\Http\Controllers\Controller;
use App\Http\Requests\GroupSong\StoreSongNoteRequest;
use App\Http\Resources\SongNoteResource;
use App\Models\Group;
use App\Models\GroupSong;
use App\Models\SongNote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SongNoteController extends Controller
{

    /**
     * List user notes for a song
     * @param Request $request
     * @param Group $group
     * @param GroupSong $groupSong
     * @return JsonResponse
     */
    public function index(Request $request, Group $group, GroupSong $groupSong): JsonResponse
    {
        abort_unless($group->hasMember($request->user()->id), 403);
        abort_if($groupSong->group_id !== $group->id, 404);

        $notes = SongNote::where('group_song_id', $groupSong->id)
            ->where('user_id', $request->user()->id)
            ->with('section')
            ->get();

        return response()->json([
            'data' => SongNoteResource::collection($notes),
        ]);
    }

    public function store(StoreSongNoteRequest $request, Group $group, GroupSong $groupSong): JsonResponse
    {
        abort_unless($group->hasMember($request->user()->id), 403);
        abort_if($groupSong->group_id !== $group->id, 404);

        $note = SongNote::create([
            ...$request->validated(),
            'user_id'      => $request->user()->id,
            'group_song_id' => $groupSong->id,
        ]);

        return response()->json([
            'message' => 'Nota creada correctamente.',
            'data'    => new SongNoteResource($note->load('section')),
        ], 201);
    }

    public function update(StoreSongNoteRequest $request, Group $group, GroupSong $groupSong, SongNote $note): JsonResponse
    {
        abort_unless($group->hasMember($request->user()->id), 403);
        abort_if($note->user_id !== $request->user()->id, 403, 'Solo puedes editar tus propias notas.');

        $note->update($request->validated());

        return response()->json([
            'message' => 'Nota actualizada correctamente.',
            'data'    => new SongNoteResource($note),
        ]);
    }

    public function destroy(Request $request, Group $group, GroupSong $groupSong, SongNote $note): JsonResponse
    {
        abort_unless($group->hasMember($request->user()->id), 403);
        abort_if($note->user_id !== $request->user()->id, 403, 'Solo puedes eliminar tus propias notas.');

        $note->delete();

        return response()->json([
            'message' => 'Nota eliminada correctamente.',
        ]);
    }
}
