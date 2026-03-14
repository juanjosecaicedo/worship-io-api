<?php

namespace App\Http\Controllers\GlobalSong;

use App\Http\Controllers\Controller;
use App\Http\Requests\GlobalSong\CreateGlobalSongRequest;
use App\Http\Requests\GlobalSong\GlobalSongFilterRequest;
use App\Http\Requests\GlobalSong\UpdateGlobalSongRequest;
use App\Http\Resources\GlobalSongResource;
use App\Models\GlobalSong;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GlobalSongController extends Controller
{
    /**
     * Get all global songs
     */
    public function index(GlobalSongFilterRequest $request): JsonResponse
    {
        $query = GlobalSong::active()->withCount('sections');

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('key')) {
            $query->byKey($request->key);
        }

        if ($request->filled('genre')) {
            $query->byGenre($request->genre);
        }

        if ($request->filled('tag')) {
            $query->byTag($request->tag);
        }

        $songs = $query->latest()->paginate($request->per_page ?? 20);

        return response()->json([
            'data' => GlobalSongResource::collection($songs),
            'meta' => [
                'current_page' => $songs->currentPage(),
                'last_page' => $songs->lastPage(),
                'per_page' => $songs->perPage(),
                'total' => $songs->total(),
            ],
        ]);
    }

    /**
     * Create a new global song
     */
    public function store(CreateGlobalSongRequest $request): JsonResponse
    {
        $song = GlobalSong::create([
            ...$request->except('sections'),
            'created_by' => $request->user()->id,
        ]);

        // Crear secciones si vienen en el request
        if ($request->filled('sections')) {
            foreach ($request->sections as $section) {
                $song->sections()->create($section);
            }
        }

        return response()->json([
            'message' => 'Song added to the global library.',
            'data' => new GlobalSongResource($song->load('sections', 'creator')),
        ], 201);
    }

    /**
     * Get a global song by ID
     */
    public function show(GlobalSong $globalSong): JsonResponse
    {
        $globalSong->load(['sections', 'creator']);

        return response()->json([
            'data' => new GlobalSongResource($globalSong),
        ]);
    }

    /**
     * Update a global song
     */
    public function update(UpdateGlobalSongRequest $request, GlobalSong $globalSong): JsonResponse
    {
        // Only the creator can edit
        abort_if(
            $globalSong->created_by !== $request->user()->id,
            403,
            'Only the creator can edit this song.'
        );

        $globalSong->update($request->validated());

        return response()->json([
            'message' => 'Song updated successfully.',
            'data' => new GlobalSongResource($globalSong->load('sections')),
        ]);
    }

    /**
     * Delete a global song
     */
    public function destroy(Request $request, GlobalSong $globalSong): JsonResponse
    {
        abort_if(
            $globalSong->created_by !== $request->user()->id,
            403,
            'Only the creator can delete this song.'
        );

        $globalSong->update(['is_active' => false]);

        return response()->json([
            'message' => 'Song deleted from the global library.',
        ]);
    }
}
