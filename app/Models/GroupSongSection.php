<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupSongSection extends Model
{
    protected $fillable = [
        'group_song_id',
        'global_section_id',
        'type',
        'label',
        'lyrics',
        'chords',
        'order',
    ];

    protected $casts = [
        'chords' => 'array',
        'order'  => 'integer',
    ];

    // ─── Relations ───────────────────────────────────
    public function groupSong()
    {
        return $this->belongsTo(GroupSong::class);
    }

    public function globalSection()
    {
        return $this->belongsTo(GlobalSongSection::class, 'global_section_id');
    }

    public function notes()
    {
        return $this->hasMany(SongNote::class, 'section_id');
    }
}
