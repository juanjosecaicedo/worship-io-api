<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    protected $fillable = [
        'event_id',
        'minutes_before',
        'channel',
        'is_sent',
        'sent_at',
    ];

    protected $casts = [
        'is_sent'  => 'boolean',
        'sent_at'  => 'datetime',
    ];

    /**
     * Predefined reminder options
     * @var array
     */
    const PRESET_OPTIONS = [
        ['label' => '15 minutos antes',  'minutes' => 15],
        ['label' => '30 minutos antes',  'minutes' => 30],
        ['label' => '1 hora antes',      'minutes' => 60],
        ['label' => '3 horas antes',     'minutes' => 180],
        ['label' => '1 día antes',       'minutes' => 1440],
        ['label' => '3 días antes',      'minutes' => 4320],
        ['label' => '1 semana antes',    'minutes' => 10080],
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    // ─── Scopes ───────────────────────────────────────────

    public function scopePending(Builder $query): Builder
    {
        return $query->where('is_sent', false);
    }

    // ─── Helpers ──────────────────────────────────────────

    /**
     * Date and time the reminder should be triggered
     * @return \Carbon\Carbon
     */
    public function scheduledAt(): \Carbon\Carbon
    {
        return $this->event->start_datetime
            ->subMinutes($this->minutes_before);
    }

    /**
     * Check if it's time to send
     * @return bool
     */
    public function isDue(): bool
    {
        return ! $this->is_sent && $this->scheduledAt()->isPast();
    }
}
