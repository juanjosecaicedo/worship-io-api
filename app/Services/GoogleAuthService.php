<?php

namespace App\Services;

use App\Models\User;
use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class GoogleAuthService
{

    private GoogleClient $client;

    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        $this->client = new GoogleClient([
            'client_id' => config('services.google.client_id'),
        ]);
    }

    /**
     * Verify the idToken and return the Google payload
     * @param string $idToken
     * @return array|bool
     */
    public function verifyIdToken(string $idToken): array
    {
        try {
            $payload = $this->client->verifyIdToken($idToken);

            if (! $payload) {
                throw ValidationException::withMessages([
                    'token' => ["Google's token is not valid."],
                ]);
            }

            return $payload;
        } catch (\Exception $e) {
            Log::warning("Google token verification failed", [
                'error' => $e->getMessage(),
            ]);

            throw ValidationException::withMessages([
                'token' => ["Google's token is not valid."],
            ]);
        }
    }

    /**
     * Find or create a user based on the Google payload
     * @param array $payload
     * @return User
     */
    public function findOrCreateUser(array $payload): User
    {
        $googleId = $payload['sub'];
        $email    = $payload['email'];
        $name     = $payload['name'];
        $avatar   = $payload['picture'] ?? null;
        $verified = $payload['email_verified'] ?? false;

        // 1. Search by google_id
        $user = User::where('google_id', $googleId)->first();

        if ($user) {
            // Update avatar if changed
            $user->update([
                'avatar_url'   => $avatar ?? $user->avatar_url,
                'last_login_at' => now(),
            ]);
            return $user;
        }

        // 2. Search by email (user already registered with email/password)
        $user = User::where('email', $email)->first();

        if ($user) {
            // Link Google account to existing user
            $user->update([
                'google_id'    => $googleId,
                'avatar_url'   => $avatar ?? $user->avatar_url,
                'last_login_at' => now(),
                'email_verified_at' => $verified
                    ? ($user->email_verified_at ?? now())
                    : $user->email_verified_at,
            ]);
            return $user;
        }

        // 3. Create new user with Google
        return User::create([
            'name'              => $name,
            'email'             => $email,
            'google_id'         => $googleId,
            'avatar_url'        => $avatar,
            'password'          => null,
            'has_password'      => false,
            'is_active'         => true,
            'email_verified_at' => $verified ? now() : null,
            'last_login_at'     => now(),
        ]);
    }
}
