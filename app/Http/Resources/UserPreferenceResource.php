<?php

namespace App\Http\Resources;

use App\Models\UserPreference;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserPreferenceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'key'   => $this->key,
            'value' => $this->value,
        ];
    }

    public static function full($user): array
    {
        // Preferencias guardadas en BD
        $saved = $user->preferences
            ->pluck('value', 'key')
            ->toArray();

        // Mezclar con los defaults: lo guardado tiene prioridad
        $merged = array_merge(UserPreference::DEFAULTS, $saved);

        // Agrupar por categoría para mejor legibilidad en el cliente
        return [
            'notifications' => [
                'push'               => $merged['notifications_push'],
                'email'              => $merged['notifications_email'],
                'event_created'      => $merged['notifications_event_created'],
                'event_updated'      => $merged['notifications_event_updated'],
                'event_cancelled'    => $merged['notifications_event_cancelled'],
                'role_assigned'      => $merged['notifications_role_assigned'],
                'member_added'       => $merged['notifications_member_added'],
                'setlist_updated'    => $merged['notifications_setlist_updated'],
            ],
            'appearance' => [
                'theme'    => $merged['theme'],
                'language' => $merged['language'],
            ],
            'calendar' => [
                'sync'             => $merged['calendar_sync'],
                'default_reminder' => $merged['calendar_default_reminder'],
            ],
            'setlist' => [
                'show_chords'      => $merged['setlist_show_chords'],
                'auto_transpose'   => $merged['setlist_auto_transpose'],
                'font_size'        => $merged['setlist_font_size'],
            ],
        ];
    }
}
