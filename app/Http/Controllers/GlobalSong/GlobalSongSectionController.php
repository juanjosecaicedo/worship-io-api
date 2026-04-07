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
     * Add a new section to a global song
     */
    public function store(CreateSectionRequest $request, GlobalSong $globalSong): JsonResponse
    {
        abort_if(
            $globalSong->created_by !== $request->user()->id,
            403,
            __('global_songs.only_creator_add_sections')
        );

        $section = $globalSong->sections()->create($request->validated());

        return response()->json([
            'message' => __('global_songs.section_added_success'),
            'data' => new GlobalSongSectionResource($section),
        ], 201);
    }

    /**
     * Update a global song section
     */
    public function update(
        CreateSectionRequest $request,
        GlobalSong $globalSong,
        GlobalSongSection $section
    ): JsonResponse {
        abort_if(
            $globalSong->created_by !== $request->user()->id,
            403,
            __('global_songs.only_creator_edit_sections')
        );

        abort_if(
            $section->global_song_id !== $globalSong->id,
            404,
            __('global_songs.section_not_found')
        );

        $section->update($request->validated());

        return response()->json([
            'message' => __('global_songs.section_updated_success'),
            'data' => new GlobalSongSectionResource($section),
        ]);
    }

    /**
     * Reorder global song sections
     */
    public function reorder(
        ReorderSectionsRequest $request,
        GlobalSong $globalSong
    ): JsonResponse {
        abort_if(
            $globalSong->created_by !== $request->user()->id,
            403,
            __('global_songs.only_creator_reorder_sections')
        );

        foreach ($request->sections as $item) {
            GlobalSongSection::where('id', $item['id'])
                ->where('global_song_id', $globalSong->id)
                ->update(['order' => $item['order']]);
        }

        return response()->json([
            'message' => __('global_songs.section_reordered_success'),
            'data' => GlobalSongSectionResource::collection(
                $globalSong->sections()->get()
            ),
        ]);
    }

    /**
     * Delete a global song section
     */
    public function destroy(
        Request $request,
        GlobalSong $globalSong,
        GlobalSongSection $section
    ): JsonResponse {
        abort_if(
            $globalSong->created_by !== $request->user()->id,
            403,
            __('global_songs.only_creator_delete_sections')
        );

        abort_if(
            $section->global_song_id !== $globalSong->id,
            404,
            __('global_songs.section_not_found')
        );

        $section->delete();

        return response()->json([
            'message' => __('global_songs.section_deleted_success'),
        ]);
    }
}
