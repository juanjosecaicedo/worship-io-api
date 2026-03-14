<?php

namespace App\Http\Controllers\Group;

use App\Http\Controllers\Controller;
use App\Http\Requests\Group\CreateGroupRequest;
use App\Http\Resources\GroupResource;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\JsonResponse;

class GroupController extends Controller
{
    /**
     * Get all groups
     */
    public function index(Request $request): JsonResponse
    {
        $groups = Group::whereHas('members', function ($q) use ($request) {
            $q->where('user_id', $request->user()->id)
                ->where('is_active', true);
        })
            ->where('is_active', true)
            ->withCount('members')
            ->with('creator')
            ->get();

        return response()->json([
            'data' => GroupResource::collection($groups),
        ]);
    }

    /**
     * Create a new group
     *
     * @authenticated
     * @param Request $request
     * @return JsonResponse
     */
    public function store(CreateGroupRequest $request): JsonResponse
    {
        $group = Group::create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        // El creador se agrega automáticamente como admin
        $group->members()->create([
            'user_id' => $request->user()->id,
            'role' => 'admin',
            'joined_at' => now()->toDateString(),
        ]);

        return response()->json([
            'message' => 'Group created successfully.',
            'data' => new GroupResource($group->load('creator')),
        ], 201);
    }

    /**
     * Get a group by ID
     *
     * @authenticated
     * @param Request $request
     * @param Group $group
     * @return JsonResponse
     */
    public function show(Request $request, Group $group): JsonResponse
    {
        Gate::authorize('view', $group);

        $group->load(['creator', 'members.user']);

        return response()->json([
            'data' => new GroupResource($group),
        ]);
    }

    /**
     * Delete a group
     */
    public function destroy(Request $request, Group $group): JsonResponse
    {
        Gate::authorize('delete', $group);

        $group->update(['is_active' => false]);

        return response()->json([
            'message' => 'Group deleted successfully.',
        ]);
    }
}
