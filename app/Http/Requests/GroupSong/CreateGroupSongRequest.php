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
            /**
             * The global song ID if based on one.
             * @example 1
             */
            'global_song_id'         => ['nullable', 'exists:global_songs,id'],

            /**
             * The song title.
             * @example Amazing Grace
             */
            'title'                  => ['required', 'string', 'max:200'],

            /**
             * The author of the song.
             * @example John Newton
             */
            'author'                 => ['nullable', 'string', 'max:150'],

            /**
             * The custom key for the group.
             * @example G
             */
            'custom_key'             => ['nullable', 'string', Rule::in(GlobalSong::VALID_KEYS)],

            /**
             * The custom tempo for the group.
             * @example 72
             */
            'custom_tempo'           => ['nullable', 'integer', 'min:20', 'max:300'],

            /**
             * The custom time signature for the group.
             * @example 4/4
             */
            'custom_time_signature'  => ['nullable', 'string', 'in:4/4,3/4,6/8,12/8,2/4,5/4,7/8'],

            /**
             * The genre of the song.
             * @example Hymn
             */
            'genre'                  => ['nullable', 'string', 'max:50'],

            /**
             * List of tags for the song.
             * @example ["classic", "worship"]
             */
            'tags'                   => ['nullable', 'array'],
            'tags.*'                 => ['string', 'max:30'],

            /**
             * YouTube link for the song.
             */
            'youtube_url'            => ['nullable', 'url', 'max:500'],

            /**
             * Whether the song is public within the network.
             * @example true
             */
            'is_public'              => ['sometimes', 'boolean'],

            /**
             * Optional sections to create with the song.
             */
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
            'title.required'       => __('group_songs.title_required'),
            'custom_key.in'        => __('group_songs.custom_key_in'),
            'global_song_id.exists' => __('group_songs.global_song_id_exists'),
        ];
    }
}
