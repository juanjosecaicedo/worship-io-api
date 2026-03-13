<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SetlistSong extends Model
{
    protected $fillable = [
        'setlist_id',
        'group_song_id',
        'order',
        'key_override',
        'duration_override',
        'notes',
    ];

    protected $casts = [
        'order'             => 'integer',
        'duration_override' => 'integer',
    ];

    public function setlist()
    {
        return $this->belongsTo(Setlist::class);
    }

    public function groupSong()
    {
        return $this->belongsTo(GroupSong::class);
    }

    public function vocalists()
    {
        return $this->hasMany(SetlistSongVocalist::class);
    }

    // ─── Helpers ──────────────────────────────────────────

    /**
     * Effective tone: override of the setlist or the tone of the group
     *
     * @return string|null
     */
    public function effectiveKey(): ?string
    {
        return $this->key_override ?? $this->groupSong->custom_key;
    }
}
