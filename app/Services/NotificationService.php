<?php

namespace App\Services;

use App\Jobs\SendPushNotification;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * Create a new class instance.
     */
    public function __construct(protected FcmService $fcm)
    {
        //
    }

    public function send(
        User   $user,
        string $type,
        string $title,
        string $body,
        array  $data = [],
        string $channel = 'in_app'
    ): Notification {

        // Siempre guardar en la base de datos (in_app)
        $notification = Notification::create([
            'user_id' => $user->id,
            'type'    => $type,
            'title'   => $title,
            'body'    => $body,
            'data'    => $data,
            'channel' => $channel,
        ]);

        // Enviar push si el canal lo requiere
        if (in_array($channel, ['push', 'both']) && $user->fcm_token) {
            SendPushNotification::dispatch($user, $title, $body, $data);
        }

        // Enviar email si el canal lo requiere
        if (in_array($channel, ['email', 'both'])) {
            $this->sendEmail($user, $title, $body);
        }

        return $notification;
    }

    /**
     * Send notification to multiple users
     * @param iterable $users
     * @param string $type
     * @param string $title
     * @param string $body
     * @param array $data
     * @param string $channel
     * @return void
     */
    public function sendToMany(
        iterable $users,
        string   $type,
        string   $title,
        string   $body,
        array    $data = [],
        string   $channel = 'in_app'
    ): void {
        foreach ($users as $user) {
            $this->send($user, $type, $title, $body, $data, $channel);
        }
    }


    /**
     * Notify all members of a group
     * @param int $groupId
     * @param string $type
     * @param string $title
     * @param string $body
     * @param array $data
     * @param string $channel
     * @return void
     */
    public function notifyGroup(
        int    $groupId,
        string $type,
        string $title,
        string $body,
        array  $data = [],
        string $channel = 'in_app'
    ): void {
        $members = \App\Models\GroupMember::where('group_id', $groupId)
            ->where('is_active', true)
            ->with('user')
            ->get();

        foreach ($members as $member) {
            $this->send($member->user, $type, $title, $body, $data, $channel);
        }
    }

    private function sendEmail(User $user, string $title, string $body): void
    {
        // Implementar con Laravel Mail cuando se configure SMTP
        // Mail::to($user->email)->queue(new EventNotificationMail($title, $body));
    }
}
