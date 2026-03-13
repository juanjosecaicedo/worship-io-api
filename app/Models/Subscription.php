<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'user_id',
        'plan_id',
        'status',
        'trial_ends_at',
        'current_period_start',
        'current_period_end',
        'cancelled_at',
        'ends_at',
        'stripe_subscription_id',
        'stripe_customer_id',
        'mercadopago_sub_id',
        'paypal_sub_id',
        'payment_gateway',
    ];

    protected $casts = [
        'trial_ends_at'        => 'datetime',
        'current_period_start' => 'datetime',
        'current_period_end'   => 'datetime',
        'cancelled_at'         => 'datetime',
        'ends_at'              => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function invoices()
    {
        return $this->hasMany(SubscriptionInvoice::class);
    }

    public function groups()
    {
        return $this->hasMany(SubscriptionGroup::class);
    }

    // ─── Helpers ──────────────────────────────────────────

    public function isActive(): bool
    {
        return in_array($this->status, ['active', 'trialing']);
    }

    public function isTrialing(): bool
    {
        return $this->status === 'trialing'
            && $this->trial_ends_at
            && $this->trial_ends_at->isFuture();
    }

    public function isCancelled(): bool
    {
        return ! is_null($this->cancelled_at);
    }

    /**
     * Check if you can add more groups according to the plan.
     * @return bool
     */
    public function canAddGroup(): bool
    {
        $limit = $this->plan->getLimit('max_groups');

        if ($limit === PHP_INT_MAX) return true;

        return $this->groups()->count() < $limit;
    }

    /**
     * Check if it has an enabled feature
     * @param string $feature
     * @return bool
     */
    public function hasFeature(string $feature): bool
    {
        return $this->plan->hasFeature($feature);
    }
}
