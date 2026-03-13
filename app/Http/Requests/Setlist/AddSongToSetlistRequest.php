<?php

namespace App\Http\Requests\Setlist;

use App\Models\GlobalSong;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddSongToSetlistRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'group_song_id'     => ['required', 'exists:group_songs,id'],
            'order'             => ['required', 'integer', 'min:0'],
            'key_override'      => ['nullable', 'string', Rule::in(GlobalSong::VALID_KEYS)],
            'duration_override' => ['nullable', 'integer', 'min:1'],
            'notes'             => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'group_song_id.required' => 'La canción es obligatoria.',
            'group_song_id.exists'   => 'La canción seleccionada no existe.',
            'order.required'         => 'El orden es obligatorio.',
        ];
    }
}
