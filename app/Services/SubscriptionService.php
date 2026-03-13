<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Carbon\Carbon;

class SubscriptionService
{
    /**
     * Create a new class instance.
     */
    public function __construct() {}

    /**
     * Create a subscription (with or without a trial)
     * @param User $user
     * @param SubscriptionPlan $plan
     * @param string $gateway
     * @return Subscription
     */
    public function create(User $user, SubscriptionPlan $plan, string $gateway = 'stripe'): Subscription
    {
        // Cancelar subscripción activa anterior si existe
        $this->cancelActive($user);

        $now      = Carbon::now();
        $isFree   = $plan->price === 0;
        $hasTrial = $plan->trial_days > 0;

        return Subscription::create([
            'user_id'              => $user->id,
            'plan_id'              => $plan->id,
            'status'               => $isFree ? 'active' : ($hasTrial ? 'trialing' : 'active'),
            'trial_ends_at'        => $hasTrial ? $now->copy()->addDays($plan->trial_days) : null,
            'current_period_start' => $now,
            'current_period_end'   => $now->copy()->addMonth(),
            'payment_gateway'      => $isFree ? 'manual' : $gateway,
        ]);
    }

    /**
     * Change the subscription plan
     * @param Subscription $subscription
     * @param SubscriptionPlan $newPlan
     * @return Subscription
     */
    public function changePlan(Subscription $subscription, SubscriptionPlan $newPlan): Subscription
    {
        $subscription->update([
            'plan_id'              => $newPlan->id,
            'status'               => 'active',
            'current_period_start' => now(),
            'current_period_end'   => now()->addMonth(),
        ]);

        // Verificar que los grupos asignados no excedan el nuevo límite
        $this->enforceGroupLimit($subscription);

        return $subscription->fresh('plan.features');
    }

    /**
     * Cancel the subscription
     * @param Subscription $subscription
     * @return Subscription
     */
    public function cancel(Subscription $subscription): Subscription
    {
        $subscription->update([
            'status'       => 'cancelled',
            'cancelled_at' => now(),
            'ends_at'      => $subscription->current_period_end,
        ]);

        return $subscription;
    }

    /**
     * Renew the subscription
     * @param Subscription $subscription
     * @return Subscription
     */
    public function renew(Subscription $subscription): Subscription
    {
        $subscription->update([
            'status'               => 'active',
            'current_period_start' => now(),
            'current_period_end'   => now()->addMonth(),
            'cancelled_at'         => null,
            'ends_at'              => null,
        ]);

        return $subscription;
    }

    /**
     * Check the group limit and unassign if it exceeds it.
     * @param Subscription $subscription
     * @return void
     */
    private function enforceGroupLimit(Subscription $subscription): void
    {
        $limit = $subscription->plan->getLimit('max_groups');

        if ($limit === PHP_INT_MAX) return;

        $groups = $subscription->groups()->latest()->get();

        if ($groups->count() > $limit) {
            $toRemove = $groups->slice($limit);
            $subscription->groups()
                ->whereIn('group_id', $toRemove->pluck('group_id'))
                ->delete();
        }
    }

    /**
     * Cancel user's active subscription
     * @param User $user
     * @return void
     */
    private function cancelActive(User $user): void
    {
        $active = $user->activeSubscription;

        if ($active) {
            $active->update([
                'status'       => 'cancelled',
                'cancelled_at' => now(),
            ]);
        }
    }

    /**
     * Assign group to subscription
     * @param Subscription $subscription
     * @param int $groupId
     * @return bool
     */
    public function assignGroup(Subscription $subscription, int $groupId): bool
    {
        if (! $subscription->canAddGroup()) {
            return false;
        }

        $subscription->groups()->firstOrCreate([
            'group_id'    => $groupId,
            'assigned_at' => now(),
        ]);

        return true;
    }
}
