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
    /**
     * Create a new section
     */
    public function store(CreateSectionRequest $request, GlobalSong $globalSong): JsonResponse
    {
        abort_if(
            $globalSong->created_by !== $request->user()->id,
            403,
            'Only the creator can add sections.'
        );

        $section = $globalSong->sections()->create($request->validated());

        return response()->json([
            'message' => 'Section added successfully.',
            'data' => new GlobalSongSectionResource($section),
        ], 201);
    }

    /**
     * Update a section
     */
    public function update(
        CreateSectionRequest $request,
        GlobalSong $globalSong,
        GlobalSongSection $section
    ): JsonResponse {
        abort_if(
            $globalSong->created_by !== $request->user()->id,
            403,
            'Only the creator can edit sections.'
        );

        abort_if(
            $section->global_song_id !== $globalSong->id,
            404,
            'Section not found in this song.'
        );

        $section->update($request->validated());

        return response()->json([
            'message' => 'Section updated successfully.',
            'data' => new GlobalSongSectionResource($section),
        ]);
    }

    /**
     * Reorder sections
     */
    public function reorder(
        ReorderSectionsRequest $request,
        GlobalSong $globalSong
    ): JsonResponse {
        abort_if(
            $globalSong->created_by !== $request->user()->id,
            403,
            'Only the creator can reorder sections.'
        );

        foreach ($request->sections as $item) {
            GlobalSongSection::where('id', $item['id'])
                ->where('global_song_id', $globalSong->id)
                ->update(['order' => $item['order']]);
        }

        return response()->json([
            'message' => 'Sections reordered successfully.',
            'data' => GlobalSongSectionResource::collection(
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
            'Only the creator can delete sections.'
        );

        abort_if(
            $section->global_song_id !== $globalSong->id,
            404,
            'Section not found in this song.'
        );

        $section->delete();

        return response()->json([
            'message' => 'Section deleted successfully.',
        ]);
    }
}
