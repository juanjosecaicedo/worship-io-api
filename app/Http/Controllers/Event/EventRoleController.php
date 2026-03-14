<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use App\Http\Requests\Event\AssignRoleRequest;
use App\Http\Resources\EventRoleResource;
use App\Models\Event;
use App\Models\EventRole;
use App\Models\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventRoleController extends Controller
{
    /**
     * Assign a role to a user in an event
     */
    public function store(AssignRoleRequest $request, Group $group, Event $event): JsonResponse
    {
        abort_unless($group->isAdminOrLeader($request->user()->id), 403);
        abort_if($event->group_id !== $group->id, 404);

        // Verify that the user is a member of the group
        abort_unless(
            $group->hasMember($request->user_id),
            422,
            'The user is not a member of this group.'
        );

        // Only one band director per event is allowed
        if ($request->role === 'band_director') {
            $exists = $event->roles()
                ->where('role', 'band_director')
                ->exists();

            if ($exists) {
                return response()->json([
                    'message' => 'A band director has already been assigned to this event.',
                ], 409);
            }
        }

        // Verify that the user does not have the same role duplicated
        $exists = $event->roles()
            ->where('user_id', $request->user_id)
            ->where('role', $request->role)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'This user already has this role in the event.',
            ], 409);
        }

        $role = $event->roles()->create($request->validated());

        return response()->json([
            'message' => 'Role assigned successfully.',
            'data' => new EventRoleResource($role->load('user')),
        ], 201);
    }

    /**
     * Update a role in an event
     */
    public function update(Request $request, Group $group, Event $event, EventRole $role): JsonResponse
    {
        abort_unless($group->isAdminOrLeader($request->user()->id), 403);
        abort_if($event->group_id !== $group->id, 404);
        abort_if($role->event_id !== $event->id, 404);

        $request->validate([
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $role->update(['notes' => $request->notes]);

        return response()->json([
            'message' => 'Role updated successfully.',
            'data' => new EventRoleResource($role->load('user')),
        ]);
    }

    /**
     * Remove a role from a user in an event
     */
    public function destroy(Request $request, Group $group, Event $event, EventRole $role): JsonResponse
    {
        abort_unless($group->isAdminOrLeader($request->user()->id), 403, '');
        abort_if($event->group_id !== $group->id, 404);
        abort_if($role->event_id !== $event->id, 404);

        $role->delete();

        return response()->json([
            'message' => 'Role removed successfully.',
        ]);
    }
}
