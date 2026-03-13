<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'created_by',
        'title',
        'type',
        'description',
        'location',
        'start_datetime',
        'end_datetime',
        'status',
        'gcal_event_id',
        'color',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime'   => 'datetime',
    ];

    // ─── Relationships ───────────────────────────────────────
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function roles()
    {
        return $this->hasMany(EventRole::class);
    }

    public function attendees()
    {
        return $this->hasMany(EventAttendee::class);
    }

    public function setlists()
    {
        return $this->hasMany(Setlist::class);
    }

    // ─── Helpers ──────────────────────────────────────────

    /**
     * Band director assigned to the event
     * @return EventRole|\stdClass|null
     */
    public function bandDirector()
    {
        return $this->roles()
            ->where('role', 'band_director')
            ->with('user')
            ->first();
    }

    /**
     * Vocalists of the event
     * @return \Illuminate\Database\Eloquent\Collection<int, EventRole>
     */
    public function vocalists()
    {
        return $this->roles()
            ->where('role', 'vocalist')
            ->with('user')
            ->get();
    }

    /**
     * Choir of the event
     * @return \Illuminate\Database\Eloquent\Collection<int, EventRole>
     */
    public function choir()
    {
        return $this->roles()
            ->where('role', 'choir')
            ->with('user')
            ->get();
    }

    // ─── Scopes ───────────────────────────────────────────
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('start_datetime', '>=', now())
            ->where('status', 'scheduled')
            ->orderBy('start_datetime');
    }

    public function scopePast(Builder $query): Builder
    {
        return $query->where('start_datetime', '<', now())
            ->orderByDesc('start_datetime');
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeByMonth(Builder $query, int $year, int $month): Builder
    {
        return $query->whereYear('start_datetime', $year)
            ->whereMonth('start_datetime', $month);
    }
}
