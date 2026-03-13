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
                    'message' => 'Miembro reactivado correctamente.',
                    'data'    => new GroupMemberResource($existing->load('user')),
                ]);
            }

            return response()->json([
                'message' => 'El usuario ya es miembro activo del grupo.',
            ], 409);
        }

        $member = $group->members()->create([
            ...$request->validated(),
            'joined_at' => $request->joined_at ?? now()->toDateString(),
        ]);

        return response()->json([
            'message' => 'Miembro agregado correctamente.',
            'data'    => new GroupMemberResource($member->load('user')),
        ], 201);
    }

    public function update(
        UpdateMemberRequest $request,
        Group $group,
        GroupMember $member
    ): JsonResponse {
        Gate::authorize('manageMembers', $group);

        // Verificar que el miembro pertenece al grupo
        abort_if($member->group_id !== $group->id, 404, 'Miembro no encontrado en este grupo.');

        $member->update($request->validated());

        return response()->json([
            'message' => 'Miembro actualizado correctamente.',
            'data'    => new GroupMemberResource($member->load('user')),
        ]);
    }

    public function destroy(
        Request $request,
        Group $group,
        GroupMember $member
    ): JsonResponse {
        Gate::authorize('manageMembers', $group);

        abort_if($member->group_id !== $group->id, 404, 'Miembro no encontrado en este grupo.');

        // No permitir que el creador del grupo se elimine a sí mismo
        abort_if(
            $member->user_id === $group->created_by,
            403,
            'No puedes eliminar al creador del grupo.'
        );

        $member->update(['is_active' => false]);

        return response()->json([
            'message' => 'Miembro eliminado del grupo correctamente.',
        ]);
    }
}
