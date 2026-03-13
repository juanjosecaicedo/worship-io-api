<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
