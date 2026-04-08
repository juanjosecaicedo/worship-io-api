<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\GoogleLoginRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\GoogleAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\VerifyCodeRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Mail\PasswordResetCodeMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    /**
     * Register a new user
     * 
     * Creates a new user account and returns an authentication token.
     * 
     * @unauthenticated
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username ?? Str::before($request->email, '@'),
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
        ]);

        $token = $user->createToken($request->device_name ?? 'worship-io-app')->plainTextToken;

        return response()->json([
            'message' => __('auth.register_success'),
            'data' => new UserResource($user),
            'token' => $token,
        ], 201);
    }

    /**
     * Login a user
     * 
     * Authenticates a user with email and password and returns a Sanctum token.
     * 
     * @unauthenticated
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.invalid_credentials')],
            ]);
        }

        if (! $user->is_active) {
            return response()->json([
                'message' => __('auth.account_deactivated'),
            ], 403);
        }

        // Update last login
        $user->update(['last_login_at' => now()]);

        // Revoke all tokens of the user from the same device
        $deviceName = $request->device_name ?? 'worship-io-app';
        $user->tokens()->where('name', $deviceName)->delete();

        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'message' => __('auth.login_success'),
            'data' => new UserResource($user),
            'token' => $token,
        ]);
    }

    /**
     * Send password reset code
     * 
     * Generates a 6-digit code and sends it to the user's email.
     * 
     * @unauthenticated
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => $code,
                'created_at' => now(),
            ]
        );

        Mail::to($request->email)->send(new PasswordResetCodeMail($code));

        return response()->json([
            'message' => __('auth.password_reset_code_sent'),
        ]);
    }

    /**
     * Verify reset code
     * 
     * Checks if the provided 6-digit code is valid and not expired.
     * 
     * @unauthenticated
     */
    public function verifyCode(VerifyCodeRequest $request): JsonResponse
    {
        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->code)
            ->first();

        if (!$record || now()->parse($record->created_at)->addMinutes(60)->isPast()) {
            throw ValidationException::withMessages([
                'code' => [__('auth.invalid_reset_code')],
            ]);
        }

        return response()->json([
            'message' => __('auth.code_verified_success'),
        ]);
    }

    /**
     * Reset password
     * 
     * Resets the user's password using the verified 6-digit code.
     * 
     * @unauthenticated
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->code)
            ->first();

        if (!$record || now()->parse($record->created_at)->addMinutes(60)->isPast()) {
            throw ValidationException::withMessages([
                'code' => [__('auth.invalid_reset_code')],
            ]);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Revoke all tokens of the user to force relogin
        $user->tokens()->delete();

        // Delete the reset token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json([
            'message' => __('auth.password_reset_success'),
        ]);
    }


    /**
     * Logout a user
     * 
     * Revokes the current access token.
     */
    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();

        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }

        return response()->json([
            'message' => __('auth.logout_success'),
        ]);
    }

    /**
     * Login with Google
     * 
     * Authenticates or creates a user using a Google ID token.
     * 
     * @unauthenticated
     */
    public function googleLogin(
        GoogleLoginRequest $request,
        GoogleAuthService $googleAuth
    ): JsonResponse {
        // 1. Verify the token with Google
        $payload = $googleAuth->verifyIdToken($request->id_token);

        // 2. Find or create the user
        $user = $googleAuth->findOrCreateUser($payload);

        if (! $user->is_active) {
            return response()->json([
                'message' => __('auth.account_disabled'),
            ], 403);
        }

        // 3. Revoke previous tokens from the same device
        $deviceName = $request->device_name ?? 'worship-io-app';
        $user->tokens()->where('name', $deviceName)->delete();

        // 4. Create Sanctum token
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'message'      => __('auth.google_login_success'),
            'data'         => new UserResource($user->load('vocalProfile')),
            'token'        => $token,
            'is_new_user'  => is_null($user->getOriginal('google_id')),
        ]);
    }


    /**
     * Get current user profile
     * 
     * Returns detailed information about the authenticated user.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => new UserResource($request->user()->load(['preferences', 'vocalProfile', 'groups.members.user'])),
        ]);
    }
}
