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
     * Get all members of a group
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
     * Add a new member to a group
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
                    'message' => 'Member reactivated successfully.',
                    'data' => new GroupMemberResource($existing->load('user')),
                ]);
            }

            return response()->json([
                'message' => 'The user is already an active member of the group.',
            ], 409);
        }

        $member = $group->members()->create([
            ...$request->validated(),
            'joined_at' => $request->joined_at ?? now()->toDateString(),
        ]);

        return response()->json([
            'message' => 'Member added successfully.',
            'data' => new GroupMemberResource($member->load('user')),
        ], 201);
    }

    /**
     * Update a member
     */
    public function update(
        UpdateMemberRequest $request,
        Group $group,
        GroupMember $member
    ): JsonResponse {
        Gate::authorize('manageMembers', $group);

        // Verificar que el miembro pertenece al grupo
        abort_if($member->group_id !== $group->id, 404, 'Member not found in this group.');

        $member->update($request->validated());

        return response()->json([
            'message' => 'Member updated successfully.',
            'data' => new GroupMemberResource($member->load('user')),
        ]);
    }

    public function destroy(
        Request $request,
        Group $group,
        GroupMember $member
    ): JsonResponse {
        Gate::authorize('manageMembers', $group);

        abort_if($member->group_id !== $group->id, 404, 'Member not found in this group.');

        // Do not allow the creator of the group to delete himself
        abort_if(
            $member->user_id === $group->created_by,
            403,
            'You cannot delete the creator of the group.'
        );

        $member->update(['is_active' => false]);

        return response()->json([
            'message' => 'Member deleted successfully.',
        ]);
    }
}
