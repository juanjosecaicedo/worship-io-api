<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    public const UPDATED_AT = null; // Solo tiene created_at

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'body',
        'data',
        'channel',
        'read_at',
    ];

    protected $casts = [
        'data'    => 'array',
        'read_at' => 'datetime',
    ];


    /**
     * Types of system notifications
     * @var array
     */
    const TYPES = [
        'event_reminder'    => 'Recordatorio de evento',
        'event_created'     => 'Nuevo evento creado',
        'event_cancelled'   => 'Evento cancelado',
        'event_updated'     => 'Evento actualizado',
        'setlist_updated'   => 'Setlist actualizado',
        'role_assigned'     => 'Rol asignado en evento',
        'member_added'      => 'Nuevo miembro en el grupo',
        'song_added'        => 'Nueva canción agregada',
        'attendance_request' => 'Solicitud de asistencia',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ─── Scopes ───────────────────────────────────────────

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead(Builder $query): Builder
    {
        return $query->whereNotNull('read_at');
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    // ─── Helpers ──────────────────────────────────────────

    public function isRead(): bool
    {
        return !is_null($this->read_at);
    }

    public function markAsRead(): void
    {
        if (! $this->isRead()) {
            $this->update(['read_at' => now()]);
        }
    }
}
