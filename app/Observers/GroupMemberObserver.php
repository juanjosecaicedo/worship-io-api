<?php

namespace App\Observers;

use App\Models\GroupMember;
use App\Services\NotificationService;

class GroupMemberObserver
{
    public function __construct(protected NotificationService $service) {}


    /**
     * Handle the GroupMember "created" event.
     */
    public function created(GroupMember $groupMember): void
    {
        $group = $groupMember->group;

        $this->service->send(
            user: $groupMember->user,
            type: 'member_added',
            title: '🎸 Bienvenido al grupo',
            body: "Has sido agregado al grupo \"{$group->name}\" como {$groupMember->role}.",
            data: [
                'group_id' => $group->id,
                'role'     => $groupMember->role,
            ],
            channel: 'push',
        );
    }

    /**
     * Handle the GroupMember "updated" event.
     */
    public function updated(GroupMember $groupMember): void
    {
        //
    }

    /**
     * Handle the GroupMember "deleted" event.
     */
    public function deleted(GroupMember $groupMember): void
    {
        //
    }

    /**
     * Handle the GroupMember "restored" event.
     */
    public function restored(GroupMember $groupMember): void
    {
        //
    }

    /**
     * Handle the GroupMember "force deleted" event.
     */
    public function forceDeleted(GroupMember $groupMember): void
    {
        //
    }
}
