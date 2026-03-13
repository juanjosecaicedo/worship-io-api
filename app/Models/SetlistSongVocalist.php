<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SetlistSongVocalist extends Model
{
    protected $fillable = [
        'setlist_song_id',
        'user_id',
        'vocal_role',
        'key_override',
        'notes',
    ];

    public function setlistSong()
    {
        return $this->belongsTo(SetlistSong::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
