<?php

namespace App\Http\Requests\GlobalSong;

use App\Models\GlobalSong;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGlobalSongRequest extends FormRequest
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
            /**
             * The song title.
             * @example Amazing Grace
             */
            'title'            => ['sometimes', 'string', 'max:200'],

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
             * Whether the song is active.
             * @example true
             */
            'is_active'        => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'original_key.in'      => __('global_songs.original_key_in'),
            'tempo.min'            => __('global_songs.tempo_min'),
            'tempo.max'            => __('global_songs.tempo_max'),
            'time_signature.in'    => __('global_songs.time_signature_in'),
        ];
    }
}
