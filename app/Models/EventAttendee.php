<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventAttendee extends Model
{
    protected $fillable = [
        'event_id',
        'user_id',
        'status',
        'responded_at',
        'notes',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
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
