<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlanFeature extends Model
{
    protected $fillable = [
        'plan_id',
        'feature',
        'value',
    ];

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }
}
