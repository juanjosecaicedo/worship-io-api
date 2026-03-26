<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\FcmService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendPushNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int  $tries   = 3;
    public int  $timeout = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public User   $user,
        public string $title,
        public string $body,
        public array  $data = []
    ) {
        $this->onQueue('notifications');
    }

    /**
     * Execute the job.
     */
    public function handle(FcmService $fcm): void
    {
        if (!$this->user->fcm_token) return;

        $fcm->send(
            token: $this->user->fcm_token,
            title: $this->title,
            body: $this->body,
            data: $this->data,
        );
    }
}
