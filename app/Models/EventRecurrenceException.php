<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventRecurrenceException extends Model
{
    protected $fillable = [
        'event_recurrence_id',
        'original_date',
        'type',
        'event_id',
    ];

    protected $casts = [
        'original_date' => 'date',
    ];

    // ─── Relationships ───────────────────────────────────────

    public function recurrence()
    {
        return $this->belongsTo(EventRecurrence::class, 'event_recurrence_id');
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
