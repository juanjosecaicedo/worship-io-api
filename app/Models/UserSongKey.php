<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSongKey extends Model
{
    protected $fillable = [
        'user_id',
        'group_song_id',
        'preferred_key',
        'capo',
        'notes',
    ];

    protected $casts = [
        'capo' => 'integer',
    ];

    // ─── Relations ───────────────────────────────────────
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function groupSong()
    {
        return $this->belongsTo(GroupSong::class);
    }
}
