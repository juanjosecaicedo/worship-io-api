<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    private string $serverKey;
    private string $fcmUrl = 'https://fcm.googleapis.com/fcm/send';


    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        $this->serverKey = config('services.fcm.server_key');
    }

    /**
     * Send push notification to a device
     * @param string $token
     * @param string $title
     * @param string $body
     * @param array $data
     * @return bool
     */
    public function send(
        string $token,
        string $title,
        string $body,
        array  $data = []
    ): bool {
        try {
            $response = Http::withHeaders([
                'Authorization' => "key={$this->serverKey}",
                'Content-Type'  => 'application/json',
            ])->post($this->fcmUrl, [
                'to'           => $token,
                'notification' => [
                    'title' => $title,
                    'body'  => $body,
                    'sound' => 'default',
                ],
                'data'         => $data,
                'priority'     => 'high',
            ]);

            if (! $response->successful()) {
                Log::warning('FCM send failed', [
                    'token'    => substr($token, 0, 20) . '...',
                    'response' => $response->json(),
                ]);
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('FCM exception', ['message' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Send to multiple devices
     * @param array $tokens
     * @param string $title
     * @param string $body
     * @param array $data
     * @return void
     */
    public function sendMulticast(array $tokens, string $title, string $body, array $data = []): void
    {
        // FCM permite hasta 500 tokens por request
        foreach (array_chunk($tokens, 500) as $chunk) {
            try {
                Http::withHeaders([
                    'Authorization' => "key={$this->serverKey}",
                    'Content-Type'  => 'application/json',
                ])->post($this->fcmUrl, [
                    'registration_ids' => $chunk,
                    'notification'     => [
                        'title' => $title,
                        'body'  => $body,
                        'sound' => 'default',
                    ],
                    'data'     => $data,
                    'priority' => 'high',
                ]);
            } catch (\Exception $e) {
                Log::error('FCM multicast exception', ['message' => $e->getMessage()]);
            }
        }
    }
}
