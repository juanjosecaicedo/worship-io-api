<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\UpdateProfileRequest;
use App\Http\Requests\User\UpdateVocalProfileRequest;
use App\Http\Resources\UserResource;
use App\Http\Resources\VocalProfileResource;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserController extends Controller
{
    /**
     * Update user profile
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $request->user()->update($request->validated());

        return response()->json([
            'message' => 'Profile updated successfully.',
            'data' => new UserResource($request->user()->fresh()),
        ]);
    }

    /**
     * Update user vocal profile
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateVocalProfile(UpdateVocalProfileRequest $request): JsonResponse
    {
        $profile = $request->user()->vocalProfile()->updateOrCreate(
            ['user_id' => $request->user()->id],
            $request->validated()
        );

        return response()->json([
            'message' => 'Vocal profile updated successfully.',
            'data' => new VocalProfileResource($profile),
        ]);
    }

    /**
     * Delete user account
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->tokens()->delete();

        $user->update([
            'is_active' => false,
        ]);

        return response()->json([
            'message' => 'Account deactivated successfully.',
        ]);
    }
}
