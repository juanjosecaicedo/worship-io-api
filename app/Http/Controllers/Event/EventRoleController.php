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
    public function store(AssignRoleRequest $request, Group $group, Event $event): JsonResponse
    {
        abort_unless($group->isAdminOrLeader($request->user()->id), 403);
        abort_if($event->group_id !== $group->id, 404);

        // Verificar que el usuario es miembro del grupo
        abort_unless(
            $group->hasMember($request->user_id),
            422,
            'El usuario no es miembro de este grupo.'
        );

        // Solo puede haber 1 director de banda por evento
        if ($request->role === 'band_director') {
            $exists = $event->roles()
                ->where('role', 'band_director')
                ->exists();

            if ($exists) {
                return response()->json([
                    'message' => 'Ya hay un director de banda asignado a este evento.',
                ], 409);
            }
        }

        // Verificar que no tenga el mismo rol duplicado
        $exists = $event->roles()
            ->where('user_id', $request->user_id)
            ->where('role', $request->role)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Este usuario ya tiene este rol en el evento.',
            ], 409);
        }

        $role = $event->roles()->create($request->validated());

        return response()->json([
            'message' => 'Rol asignado correctamente.',
            'data'    => new EventRoleResource($role->load('user')),
        ], 201);
    }

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
            'message' => 'Rol actualizado correctamente.',
            'data'    => new EventRoleResource($role->load('user')),
        ]);
    }

    public function destroy(Request $request, Group $group, Event $event, EventRole $role): JsonResponse
    {
        abort_unless($group->isAdminOrLeader($request->user()->id), 403);
        abort_if($event->group_id !== $group->id, 404);
        abort_if($role->event_id !== $event->id, 404);

        $role->delete();

        return response()->json([
            'message' => 'Rol eliminado del evento correctamente.',
        ]);
    }
}
