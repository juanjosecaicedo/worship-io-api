<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class GroupSong extends Model
{
    protected $fillable = [
        'group_id',
        'global_song_id',
        'created_by',
        'title',
        'author',
        'custom_key',
        'custom_tempo',
        'custom_time_signature',
        'genre',
        'tags',
        'youtube_url',
        'is_public',
        'sections_order',
    ];

    protected $casts = [
        'tags'           => 'array',
        'sections_order' => 'array',
        'is_public'      => 'boolean',
    ];

    // ─── Relations ───────────────────────────────────

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function globalSong()
    {
        return $this->belongsTo(GlobalSong::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sections()
    {
        return $this->hasMany(GroupSongSection::class)->orderBy('order');
    }

    public function notes()
    {
        return $this->hasMany(SongNote::class);
    }

    public function userSongKeys()
    {
        return $this->hasMany(UserSongKey::class);
    }

    // ─── Helpers ───────────────────────────────────

    /**
     * Indicates if the song is a customization of a global song.
     * @return bool
     */
    public function isForked(): bool
    {
        return !is_null($this->global_song_id);
    }


    // ─── Scopes ───────────────────────────────────────────
    public function scopeForGroup(Builder $query, int $groupId): Builder
    {
        return $query->where('group_id', $groupId);
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->whereFullText(['title', 'author'], $term);
    }

    public function scopeByKey(Builder $query, string $key): Builder
    {
        return $query->where('custom_key', $key);
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }
}
