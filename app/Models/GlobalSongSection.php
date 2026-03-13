<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlobalSongSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'global_song_id',
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

    // ─── Relations ───────────────────────────────────────────
    public function song()
    {
        return $this->belongsTo(GlobalSong::class, 'global_song_id');
    }
}
