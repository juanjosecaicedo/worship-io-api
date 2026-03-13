<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionGroup extends Model
{
    protected $fillable = [
        'subscription_id',
        'group_id',
        'assigned_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
    ];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}
