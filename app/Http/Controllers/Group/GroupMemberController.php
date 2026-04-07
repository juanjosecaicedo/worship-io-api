<?php

namespace App\Http\Controllers\Group;

use App\Http\Controllers\Controller;
use App\Http\Requests\Group\AddMemberRequest;
use App\Http\Requests\Group\UpdateMemberRequest;
use App\Http\Resources\GroupMemberResource;
use App\Models\Group;
use App\Models\GroupMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class GroupMemberController extends Controller
{
    /**
     * List group members
     */
    public function index(Request $request, Group $group): JsonResponse
    {
        Gate::authorize('view', $group);

        $members = $group->members()
            ->with('user')
            ->where('is_active', true)
            ->orderBy('role')
            ->get();

        return response()->json([
            'data' => GroupMemberResource::collection($members),
        ]);
    }

    /**
     * Add member to group
     * 
     * Adds a new member to the group with a specific role. 
     * If the user was previously a member but was inactive, they will be reactivated.
     */
    public function store(AddMemberRequest $request, Group $group): JsonResponse
    {
        Gate::authorize('manageMembers', $group);

        // Verificar si ya es miembro
        $existing = $group->members()
            ->where('user_id', $request->user_id)
            ->first();

        if ($existing) {
            // Si estaba inactivo, reactivarlo
            if (! $existing->is_active) {
                $existing->update([
                    ...$request->validated(),
                    'is_active' => true,
                ]);

                return response()->json([
                    'message' => __('groups.member_reactivated'),
                    'data' => new GroupMemberResource($existing->load('user')),
                ]);
            }

            return response()->json([
                'message' => __('groups.member_already_active'),
            ], 409);
        }

        $member = $group->members()->create([
            ...$request->validated(),
            'joined_at' => $request->joined_at ?? now()->toDateString(),
        ]);

        return response()->json([
            'message' => __('groups.member_added_success'),
            'data' => new GroupMemberResource($member->load('user')),
        ], 201);
    }

    /**
     * Update group member
     */
    public function update(
        UpdateMemberRequest $request,
        Group $group,
        GroupMember $member
    ): JsonResponse {
        Gate::authorize('manageMembers', $group);

        // Verificar que el miembro pertenece al grupo
        abort_if(
            $member->group_id !== $group->id,
            404,
            __('groups.member_not_found')
        );

        $member->update($request->validated());

        return response()->json([
            'message' => __('groups.member_updated_success'),
            'data' => new GroupMemberResource($member->load('user')),
        ]);
    }

    /**
     * Remove member from group
     * 
     * Setting the member as inactive. Group creators cannot be removed.
     */
    public function destroy(
        Request $request,
        Group $group,
        GroupMember $member
    ): JsonResponse {
        Gate::authorize('manageMembers', $group);

        abort_if(
            $member->group_id !== $group->id,
            404,
            __('groups.member_not_found')
        );

        // Do not allow the creator of the group to delete himself
        abort_if(
            $member->user_id === $group->created_by,
            403,
            __('groups.cannot_delete_creator')
        );

        $member->update(['is_active' => false]);

        return response()->json([
            'message' => __('groups.member_deleted_success'),
        ]);
    }
}
