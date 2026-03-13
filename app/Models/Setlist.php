<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setlist extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'created_by',
        'name',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function songs()
    {
        return $this->hasMany(SetlistSong::class)->orderBy('order');
    }


    // ─── Helpers ──────────────────────────────────────────

    /**
     * Total setlist duration in seconds
     * @return int
     */
    public function totalDuration(): int
    {
        return $this->songs->sum(function ($setlistSong) {
            return $setlistSong->duration_override
                ?? $setlistSong->groupSong->custom_tempo
                ?? 0;
        });
    }
}
