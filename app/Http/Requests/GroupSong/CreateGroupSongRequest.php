<?php

namespace App\Http\Requests\GroupSong;

use App\Models\GlobalSong;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateGroupSongRequest extends FormRequest
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
            'global_song_id'         => ['nullable', 'exists:global_songs,id'],
            'title'                  => ['required', 'string', 'max:200'],
            'author'                 => ['nullable', 'string', 'max:150'],
            'custom_key'             => ['nullable', 'string', Rule::in(GlobalSong::VALID_KEYS)],
            'custom_tempo'           => ['nullable', 'integer', 'min:20', 'max:300'],
            'custom_time_signature'  => ['nullable', 'string', 'in:4/4,3/4,6/8,12/8,2/4,5/4,7/8'],
            'genre'                  => ['nullable', 'string', 'max:50'],
            'tags'                   => ['nullable', 'array'],
            'tags.*'                 => ['string', 'max:30'],
            'youtube_url'            => ['nullable', 'url', 'max:500'],
            'is_public'              => ['sometimes', 'boolean'],

            // Secciones opcionales
            'sections'               => ['nullable', 'array'],
            'sections.*.type'        => ['required', 'in:intro,verse,pre_chorus,chorus,bridge,outro,instrumental,tag,vamp'],
            'sections.*.label'       => ['required', 'string', 'max:50'],
            'sections.*.lyrics'      => ['nullable', 'string'],
            'sections.*.chords'      => ['nullable', 'array'],
            'sections.*.order'       => ['required', 'integer', 'min:0'],
            'sections.*.global_section_id' => ['nullable', 'exists:global_song_sections,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'       => 'El título de la canción es obligatorio.',
            'custom_key.in'        => 'La tonalidad no es válida.',
            'global_song_id.exists' => 'La canción global seleccionada no existe.',
        ];
    }
}
