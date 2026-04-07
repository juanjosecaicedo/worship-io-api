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
            /**
             * The song title.
             * @example Amazing Grace
             */
            'title'            => ['required', 'string', 'max:200'],

            /**
             * The author of the song.
             * @example John Newton
             */
            'author'           => ['nullable', 'string', 'max:150'],

            /**
             * The original key of the song.
             * @example G
             */
            'original_key'     => ['nullable', 'string', Rule::in(GlobalSong::VALID_KEYS)],

            /**
             * The tempo in BPM.
             * @example 72
             */
            'tempo'            => ['nullable', 'integer', 'min:20', 'max:300'],

            /**
             * The time signature.
             * @example 4/4
             */
            'time_signature'   => ['nullable', 'string', 'in:4/4,3/4,6/8,12/8,2/4,5/4,7/8'],

            /**
             * The duration of the song in seconds.
             * @example 240
             */
            'duration_seconds' => ['nullable', 'integer', 'min:1'],

            /**
             * The genre of the song.
             * @example Hymn
             */
            'genre'            => ['nullable', 'string', 'max:50'],

            /**
             * List of tags for the song.
             * @example ["classic", "worship"]
             */
            'tags'             => ['nullable', 'array'],
            'tags.*'           => ['string', 'max:30'],

            /**
             * YouTube link for the song.
             */
            'youtube_url'      => ['nullable', 'url', 'max:500'],

            /**
             * Spotify link for the song.
             */
            'spotify_url'      => ['nullable', 'url', 'max:500'],

            /**
             * Optional sections to create with the song.
             */
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
            'title.required'       => __('global_songs.title_required'),
            'original_key.in'      => __('global_songs.original_key_in'),
            'tempo.min'            => __('global_songs.tempo_min'),
            'tempo.max'            => __('global_songs.tempo_max'),
            'time_signature.in'    => __('global_songs.time_signature_in'),
            'sections.*.type.in'   => __('global_songs.section_type_in'),
            'sections.*.label.required' => __('global_songs.section_label_required'),
        ];
    }
}
