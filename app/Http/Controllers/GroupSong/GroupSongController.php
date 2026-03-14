<?php

namespace App\Http\Controllers\GroupSong;

use App\Http\Controllers\Controller;
use App\Http\Requests\GroupSong\CreateGroupSongRequest;
use App\Http\Requests\GroupSong\ForkGlobalSongRequest;
use App\Http\Requests\GroupSong\UpdateGroupSongRequest;
use App\Http\Resources\GroupSongResource;
use App\Models\GlobalSong;
use App\Models\Group;
use App\Models\GroupSong;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GroupSongController extends Controller
{
    /**
     * List songs by the group
     */
    public function index(Request $request, Group $group): JsonResponse
    {
        abort_unless($group->hasMember($request->user()->id), 403);

        $query = GroupSong::forGroup($group->id)
            ->withCount('sections')
            ->with('creator');

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('key')) {
            $query->byKey($request->key);
        }

        $songs = $query->latest()->paginate($request->per_page ?? 20);

        return response()->json([
            'data' => GroupSongResource::collection($songs),
            'meta' => [
                'current_page' => $songs->currentPage(),
                'last_page' => $songs->lastPage(),
                'per_page' => $songs->perPage(),
                'total' => $songs->total(),
            ],
        ]);
    }

    /**
     * Create a new song for the group
     */
    public function store(CreateGroupSongRequest $request, Group $group): JsonResponse
    {
        abort_unless($group->isAdminOrLeader($request->user()->id), 403);

        $song = GroupSong::create([
            ...$request->except('sections'),
            'group_id' => $group->id,
            'created_by' => $request->user()->id,
        ]);

        if ($request->filled('sections')) {
            foreach ($request->sections as $section) {
                $song->sections()->create($section);
            }
        }

        return response()->json([
            'message' => 'Song created successfully.',
            'data' => new GroupSongResource($song->load('sections', 'creator')),
        ], 201);
    }

    /**
     * Fork a global song for the group
     */
    public function fork(ForkGlobalSongRequest $request, Group $group, GlobalSong $globalSong): JsonResponse
    {
        abort_unless($group->isAdminOrLeader($request->user()->id), 403);

        // Verificar que no exista ya un fork de esta canción en el grupo
        $exists = GroupSong::forGroup($group->id)
            ->where('global_song_id', $globalSong->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'This song has already been personalized for this group.',
            ], 409);
        }

        // Create the group song based on the global song
        $song = GroupSong::create([
            'group_id' => $group->id,
            'global_song_id' => $globalSong->id,
            'created_by' => $request->user()->id,
            'title' => $globalSong->title,
            'author' => $globalSong->author,
            'custom_key' => $request->custom_key ?? $globalSong->original_key,
            'custom_tempo' => $request->custom_tempo ?? $globalSong->tempo,
            'custom_time_signature' => $request->custom_time_signature ?? $globalSong->time_signature,
            'genre' => $globalSong->genre,
            'tags' => $globalSong->tags,
            'youtube_url' => $globalSong->youtube_url,
            'is_public' => $request->is_public ?? false,
        ]);

        // Copiar las secciones de la canción global
        foreach ($globalSong->sections as $section) {
            $song->sections()->create([
                'global_section_id' => $section->id,
                'type' => $section->type,
                'label' => $section->label,
                'lyrics' => $section->lyrics,
                'chords' => $section->chords,
                'order' => $section->order,
            ]);
        }

        return response()->json([
            'message' => 'Song personalized successfully.',
            'data' => new GroupSongResource($song->load('sections', 'globalSong')),
        ], 201);
    }

    /**
     * Show a song by the group
     */
    public function show(Request $request, Group $group, GroupSong $groupSong): JsonResponse
    {
        abort_unless($group->hasMember($request->user()->id), 403);
        abort_if($groupSong->group_id !== $group->id, 404);

        $groupSong->load([
            'sections.notes',
            'globalSong',
            'creator',
            'userSongKeys' => fn ($q) => $q->where('user_id', $request->user()->id),
        ]);

        return response()->json([
            'data' => new GroupSongResource($groupSong),
        ]);
    }

    /**
     * Update a song by the group
     */
    public function update(UpdateGroupSongRequest $request, Group $group, GroupSong $groupSong): JsonResponse
    {
        abort_unless($group->isAdminOrLeader($request->user()->id), 403);
        abort_if($groupSong->group_id !== $group->id, 404);

        $groupSong->update($request->validated());

        return response()->json([
            'message' => 'Song updated successfully.',
            'data' => new GroupSongResource($groupSong->load('sections')),
        ]);
    }

    /**
     * Delete a song by the group
     */
    public function destroy(Request $request, Group $group, GroupSong $groupSong): JsonResponse
    {
        abort_unless($group->isAdminOrLeader($request->user()->id), 403);
        abort_if($groupSong->group_id !== $group->id, 404);

        $groupSong->delete();

        return response()->json([
            'message' => 'Song deleted successfully.',
        ]);
    }
}
