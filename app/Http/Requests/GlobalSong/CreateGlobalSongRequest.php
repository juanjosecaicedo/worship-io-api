<?php

namespace App\Http\Requests\GlobalSong;

use App\Models\GlobalSong;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateGlobalSongRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'            => ['required', 'string', 'max:200'],
            'author'           => ['nullable', 'string', 'max:150'],
            'original_key'     => ['nullable', 'string', Rule::in(GlobalSong::VALID_KEYS)],
            'tempo'            => ['nullable', 'integer', 'min:20', 'max:300'],
            'time_signature'   => ['nullable', 'string', 'in:4/4,3/4,6/8,12/8,2/4,5/4,7/8'],
            'duration_seconds' => ['nullable', 'integer', 'min:1'],
            'genre'            => ['nullable', 'string', 'max:50'],
            'tags'             => ['nullable', 'array'],
            'tags.*'           => ['string', 'max:30'],
            'youtube_url'      => ['nullable', 'url', 'max:500'],
            'spotify_url'      => ['nullable', 'url', 'max:500'],

            // Secciones opcionales al crear
            'sections'               => ['nullable', 'array'],
            'sections.*.type'        => ['required', 'in:intro,verse,pre_chorus,chorus,bridge,outro,instrumental,tag,vamp'],
            'sections.*.label'       => ['required', 'string', 'max:50'],
            'sections.*.lyrics'      => ['nullable', 'string'],
            'sections.*.chords'      => ['nullable', 'array'],
            'sections.*.order'       => ['required', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'       => 'El título de la canción es obligatorio.',
            'original_key.in'      => 'La tonalidad no es válida.',
            'tempo.min'            => 'El tempo mínimo es 20 BPM.',
            'tempo.max'            => 'El tempo máximo es 300 BPM.',
            'time_signature.in'    => 'El compás debe ser: 4/4, 3/4, 6/8, 12/8, 2/4, 5/4 o 7/8.',
            'sections.*.type.in'   => 'El tipo de sección no es válido.',
            'sections.*.label.required' => 'Cada sección debe tener una etiqueta.',
        ];
    }
}
