<?php

namespace App\Http\Requests\GroupSong;

use App\Models\GlobalSong;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGroupSongRequest extends FormRequest
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
            'title'                 => ['sometimes', 'string', 'max:200'],

            /**
             * The author of the song.
             * @example John Newton
             */
            'author'                => ['nullable', 'string', 'max:150'],

            /**
             * The custom key for the group.
             * @example G
             */
            'custom_key'            => ['nullable', 'string', Rule::in(GlobalSong::VALID_KEYS)],

            /**
             * The custom tempo for the group.
             * @example 72
             */
            'custom_tempo'          => ['nullable', 'integer', 'min:20', 'max:300'],

            /**
             * The custom time signature for the group.
             * @example 4/4
             */
            'custom_time_signature' => ['nullable', 'string', 'in:4/4,3/4,6/8,12/8,2/4,5/4,7/8'],

            /**
             * The genre of the song.
             * @example Hymn
             */
            'genre'                 => ['nullable', 'string', 'max:50'],

            /**
             * List of tags for the song.
             * @example ["classic", "worship"]
             */
            'tags'                  => ['nullable', 'array'],
            'tags.*'                => ['string', 'max:30'],

            /**
             * YouTube link for the song.
             */
            'youtube_url'           => ['nullable', 'url', 'max:500'],

            /**
             * Whether the song is public within the network.
             * @example true
             */
            'is_public'             => ['sometimes', 'boolean'],

            /**
             * Optional array to update sections order.
             */
            'sections_order'        => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'custom_key.in'      => __('group_songs.custom_key_in'),
            'custom_tempo.min'   => __('group_songs.custom_tempo_min'),
            'custom_tempo.max'   => __('group_songs.custom_tempo_max'),
        ];
    }
}
