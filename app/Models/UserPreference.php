<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPreference extends Model
{
    protected $fillable = [
        'user_id',
        'key',
        'value',
    ];

    public const DEFAULTS = [
        // Notificaciones
        'notifications_push'         => 'true',
        'notifications_email'        => 'false',
        'notifications_event_created' => 'true',
        'notifications_event_updated' => 'true',
        'notifications_event_cancelled' => 'true',
        'notifications_role_assigned' => 'true',
        'notifications_member_added' => 'true',
        'notifications_setlist_updated' => 'true',

        // Apariencia
        'theme'                      => 'system',   // light | dark | system
        'language'                   => 'es',        // es | en

        // Calendario
        'calendar_sync'              => 'false',
        'calendar_default_reminder'  => '60',        // minutos

        // Setlist
        'setlist_show_chords'        => 'true',
        'setlist_auto_transpose'     => 'true',      // usar tono preferido automáticamente
        'setlist_font_size'          => 'medium',    // small | medium | large
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ─── Helpers ──────────────────────────────────────────

    public static function getDefault(string $key): ?string
    {
        $defaults = self::DEFAULTS;
        return $defaults[$key] ?? null;
    }

    public function getBoolValue(): bool
    {
        return $this->value === 'true';
    }

    public function getIntValue(): int
    {
        return (int) $this->value;
    }
}
