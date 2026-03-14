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
            'user_id' => $request->user()->id,
            'group_song_id' => $groupSong->id,
        ]);

        return response()->json([
            'message' => 'Note created successfully.',
            'data' => new SongNoteResource($note->load('section')),
        ], 201);
    }

    public function update(StoreSongNoteRequest $request, Group $group, GroupSong $groupSong, SongNote $note): JsonResponse
    {
        abort_unless($group->hasMember($request->user()->id), 403);
        abort_if($note->user_id !== $request->user()->id, 403, 'You can only edit your own notes.');

        $note->update($request->validated());

        return response()->json([
            'message' => 'Note updated successfully.',
            'data' => new SongNoteResource($note),
        ]);
    }

    public function destroy(Request $request, Group $group, GroupSong $groupSong, SongNote $note): JsonResponse
    {
        abort_unless($group->hasMember($request->user()->id), 403);
        abort_if($note->user_id !== $request->user()->id, 403, 'You can only delete your own notes.');

        $note->delete();

        return response()->json([
            'message' => 'Note deleted successfully.',
        ]);
    }
}
