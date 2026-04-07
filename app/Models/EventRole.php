<?php

namespace App\Models;

use App\Observers\EventRolesObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(EventRolesObserver::class)]
class EventRole extends Model
{
    protected $fillable = [
        'event_id',
        'user_id',
        'role',
        'notes',
    ];

    // ─── Relationships ───────────────────────────────────────
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
