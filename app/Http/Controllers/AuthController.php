<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    /**
     * Register a new user
     * @unauthenticated
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
        ]);

        $token = $user->createToken($request->device_name ?? 'worship-io-app')->plainTextToken;

        return response()->json([
            'message' => 'Successfully registered user.',
            'data' => new UserResource($user),
            'token' => $token,
        ], 201);
    }

    /**
     * Login a user
     * @unauthenticated
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The credentials are incorrect.'],
            ]);
        }

        if (! $user->is_active) {
            return response()->json([
                'message' => 'Your account is deactivated. Contact the administrator.',
            ], 403);
        }

        // Update last login
        $user->update(['last_login_at' => now()]);

        // Revoke all tokens of the user
        $user->tokens()->where('name', $request->device_name ?? 'worship-io-app')->delete();

        $token = $user->createToken($request->device_name ?? 'worship-io-app')->plainTextToken;

        return response()->json([
            'message' => 'Successfully logged in.',
            'data' => new UserResource($user),
            'token' => $token,
        ]);
    }

    /**
     * Logout a user
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();

        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }

        return response()->json([
            'message' => 'Successfully logged out.',
        ]);
    }

    /**
     * Get current user
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => new UserResource($request->user()->load('vocalProfile')),
        ]);
    }
}
