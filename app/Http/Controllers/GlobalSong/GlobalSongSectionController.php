<?php

namespace App\Http\Controllers\GlobalSong;

use App\Http\Controllers\Controller;
use App\Http\Requests\GlobalSong\CreateSectionRequest;
use App\Http\Requests\GlobalSong\ReorderSectionsRequest;
use App\Http\Resources\GlobalSongSectionResource;
use App\Models\GlobalSong;
use App\Models\GlobalSongSection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GlobalSongSectionController extends Controller
{
    public function store(CreateSectionRequest $request, GlobalSong $globalSong): JsonResponse
    {
        abort_if(
            $globalSong->created_by !== $request->user()->id,
            403,
            'Solo el creador puede agregar secciones.'
        );

        $section = $globalSong->sections()->create($request->validated());

        return response()->json([
            'message' => 'Sección agregada correctamente.',
            'data'    => new GlobalSongSectionResource($section),
        ], 201);
    }

    public function update(
        CreateSectionRequest $request,
        GlobalSong $globalSong,
        GlobalSongSection $section
    ): JsonResponse {
        abort_if(
            $globalSong->created_by !== $request->user()->id,
            403,
            'Solo el creador puede editar secciones.'
        );

        abort_if(
            $section->global_song_id !== $globalSong->id,
            404,
            'Sección no encontrada en esta canción.'
        );

        $section->update($request->validated());

        return response()->json([
            'message' => 'Sección actualizada correctamente.',
            'data'    => new GlobalSongSectionResource($section),
        ]);
    }

    public function reorder(
        ReorderSectionsRequest $request,
        GlobalSong $globalSong
    ): JsonResponse {
        abort_if(
            $globalSong->created_by !== $request->user()->id,
            403,
            'Solo el creador puede reordenar secciones.'
        );

        foreach ($request->sections as $item) {
            GlobalSongSection::where('id', $item['id'])
                ->where('global_song_id', $globalSong->id)
                ->update(['order' => $item['order']]);
        }

        return response()->json([
            'message' => 'Secciones reordenadas correctamente.',
            'data'    => GlobalSongSectionResource::collection(
                $globalSong->sections()->get()
            ),
        ]);
    }

    public function destroy(
        Request $request,
        GlobalSong $globalSong,
        GlobalSongSection $section
    ): JsonResponse {
        abort_if(
            $globalSong->created_by !== $request->user()->id,
            403,
            'Solo el creador puede eliminar secciones.'
        );

        abort_if(
            $section->global_song_id !== $globalSong->id,
            404,
            'Sección no encontrada en esta canción.'
        );

        $section->delete();

        return response()->json([
            'message' => 'Sección eliminada correctamente.',
        ]);
    }
}
