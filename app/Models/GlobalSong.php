<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class GlobalSong extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'author',
        'original_key',
        'tempo',
        'time_signature',
        'duration_seconds',
        'genre',
        'tags',
        'youtube_url',
        'spotify_url',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'tags'      => 'array',
        'is_active' => 'boolean',
    ];

    const VALID_KEYS = [
        'C',
        'C#',
        'Db',
        'D',
        'D#',
        'Eb',
        'E',
        'F',
        'F#',
        'Gb',
        'G',
        'G#',
        'Ab',
        'A',
        'A#',
        'Bb',
        'B',
        'Cm',
        'C#m',
        'Dm',
        'D#m',
        'Ebm',
        'Em',
        'Fm',
        'F#m',
        'Gm',
        'G#m',
        'Am',
        'A#m',
        'Bbm',
        'Bm',
    ];

    // ─── Relations ───────────────────────────────────────────
    public function sections()
    {
        return $this->hasMany(GlobalSongSection::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Songs by the group that derive from this global song
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<GroupSong, GlobalSong>
     */
    public function groupSongs()
    {
        return $this->hasMany(GroupSong::class);
    }

    // ─── Scopes ───────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->whereFullText(['title', 'author'], $term);
    }

    public function scopeByKey(Builder $query, string $key): Builder
    {
        return $query->where('original_key', $key);
    }

    public function scopeByGenre(Builder $query, string $genre): Builder
    {
        return $query->where('genre', $genre);
    }

    public function scopeByTag(Builder $query, string $tag): Builder
    {
        return $query->whereJsonContains('tags', $tag);
    }
}
