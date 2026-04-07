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
            /**
             * The group song ID to add.
             * @example 1
             */
            'group_song_id'     => ['required', 'exists:group_songs,id'],

            /**
             * The order in the setlist.
             * @example 1
             */
            'order'             => ['required', 'integer', 'min:0'],

            /**
             * Key override for this specific setlist item.
             * @example G
             */
            'key_override'      => ['nullable', 'string', Rule::in(GlobalSong::VALID_KEYS)],

            /**
             * Duration override in seconds.
             * @example 300
             */
            'duration_override' => ['nullable', 'integer', 'min:1'],

            /**
             * Public notes for the worship team.
             */
            'notes'             => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'group_song_id.required' => __('setlists.song_id_required'),
            'group_song_id.exists'   => __('setlists.song_id_exists'),
            'order.required'         => __('setlists.order_required'),
        ];
    }
}
