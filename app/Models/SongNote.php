<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SongNote extends Model
{
    protected $fillable = [
        'user_id',
        'group_song_id',
        'section_id',
        'type',
        'content',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function groupSong()
    {
        return $this->belongsTo(GroupSong::class);
    }

    public function section()
    {
        return $this->belongsTo(GroupSongSection::class, 'section_id');
    }
}
