<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'currency',
        'interval',
        'trial_days',
        'is_active',
        'is_featured',
        'stripe_price_id',
        'mercadopago_plan_id',
        'paypal_plan_id',
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'is_featured' => 'boolean',
        'trial_days'  => 'integer',
    ];

    public function features()
    {
        return $this->hasMany(SubscriptionPlanFeature::class, 'subscription_plan_id');
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'subscription_plan_id');
    }

    // ─── Helpers ──────────────────────────────────────────

    /**
     * Extracting value from a specific feature
     * @param string $feature
     */
    public function getFeature(string $feature): ?string
    {
        return $this->features
            ->where('feature', $feature)
            ->first()
            ?->value;
    }

    /**
     * Check if the plan has a feature enabled.
     * @param string $feature
     * @return bool
     */
    public function hasFeature(string $feature): bool
    {
        return $this->getFeature($feature) === 'true';
    }

    /**
     * Get numerical limit of a feature
     * @param string $feature
     * @return int
     */
    public function getLimit(string $feature): int|string
    {
        $value = $this->getFeature($feature);

        if ($value === 'unlimited') return PHP_INT_MAX;

        return (int) $value;
    }

    /**
     * Formatted price
     * @return string
     */
    public function formattedPrice(): string
    {
        if ($this->price === 0) return 'Gratis';

        return '$' . number_format($this->price / 100, 2) . ' ' . $this->currency;
    }
}
