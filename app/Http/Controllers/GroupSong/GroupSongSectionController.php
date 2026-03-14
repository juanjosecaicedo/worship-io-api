<?php

namespace App\Http\Controllers\GroupSong;

use App\Http\Controllers\Controller;
use App\Http\Requests\GlobalSong\CreateSectionRequest;
use App\Http\Requests\GlobalSong\ReorderSectionsRequest;
use App\Http\Resources\GroupSongSectionResource;
use App\Models\Group;
use App\Models\GroupSong;
use App\Models\GroupSongSection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GroupSongSectionController extends Controller
{
    /**
     * Add section to song by the group
     */
    public function store(CreateSectionRequest $request, Group $group, GroupSong $groupSong): JsonResponse
    {
        abort_unless($group->isAdminOrLeader($request->user()->id), 403);
        abort_if($groupSong->group_id !== $group->id, 404);

        $section = $groupSong->sections()->create($request->validated());

        return response()->json([
            'message' => 'Section added successfully.',
            'data' => new GroupSongSectionResource($section),
        ], 201);
    }

    /**
     * Update a section of a song by the group
     */
    public function update(CreateSectionRequest $request, Group $group, GroupSong $groupSong, GroupSongSection $section): JsonResponse
    {
        abort_unless($group->isAdminOrLeader($request->user()->id), 403);
        abort_if($groupSong->group_id !== $group->id, 404);
        abort_if($section->group_song_id !== $groupSong->id, 404);

        $section->update($request->validated());

        return response()->json([
            'message' => 'Section updated successfully.',
            'data' => new GroupSongSectionResource($section),
        ]);
    }

    /**
     * Reorder sections of a song by the group
     */
    public function reorder(
        ReorderSectionsRequest $request,
        Group $group,
        GroupSong $groupSong
    ): JsonResponse {
        abort_unless($group->isAdminOrLeader($request->user()->id), 403);
        abort_if($groupSong->group_id !== $group->id, 404);

        foreach ($request->sections as $item) {
            GroupSongSection::where('id', $item['id'])
                ->where('group_song_id', $groupSong->id)
                ->update(['order' => $item['order']]);
        }

        return response()->json([
            'message' => 'Sections reordered successfully.',
            'data' => GroupSongSectionResource::collection(
                $groupSong->sections()->get()
            ),
        ]);
    }

    /**
     * Delete a section of a song by the group
     */
    public function destroy(Request $request, Group $group, GroupSong $groupSong, GroupSongSection $section): JsonResponse
    {
        abort_unless($group->isAdminOrLeader($request->user()->id), 403);
        abort_if($groupSong->group_id !== $group->id, 404);
        abort_if($section->group_song_id !== $groupSong->id, 404);

        $section->delete();

        return response()->json([
            'message' => 'Section deleted successfully.',
        ]);
    }
}
