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
                'last_page'    => $songs->lastPage(),
                'per_page'     => $songs->perPage(),
                'total'        => $songs->total(),
            ],
        ]);
    }

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
            'message' => 'Canción agregada a la librería global.',
            'data'    => new GlobalSongResource($song->load('sections', 'creator')),
        ], 201);
    }

    public function show(GlobalSong $globalSong): JsonResponse
    {
        $globalSong->load(['sections', 'creator']);

        return response()->json([
            'data' => new GlobalSongResource($globalSong),
        ]);
    }

    public function update(UpdateGlobalSongRequest $request, GlobalSong $globalSong): JsonResponse
    {
        // Solo el creador puede editar
        abort_if(
            $globalSong->created_by !== $request->user()->id,
            403,
            'Solo el creador puede editar esta canción.'
        );

        $globalSong->update($request->validated());

        return response()->json([
            'message' => 'Canción actualizada correctamente.',
            'data'    => new GlobalSongResource($globalSong->load('sections')),
        ]);
    }

    public function destroy(Request $request, GlobalSong $globalSong): JsonResponse
    {
        abort_if(
            $globalSong->created_by !== $request->user()->id,
            403,
            'Solo el creador puede eliminar esta canción.'
        );

        $globalSong->update(['is_active' => false]);

        return response()->json([
            'message' => 'Canción eliminada de la librería global.',
        ]);
    }
}
