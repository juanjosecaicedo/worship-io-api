<?php

namespace App\Http\Requests\Setlist;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ReorderSetlistRequest extends FormRequest
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
             * List of song IDs in their new order.
             * @example [5, 2, 8]
             */
            'songs'         => ['required', 'array', 'min:1'],

            /**
             * The setlist song ID.
             */
            'songs.*.id'    => ['required', 'integer', 'exists:setlist_songs,id'],

            /**
             * The new order for the song.
             */
            'songs.*.order' => ['required', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'songs.required'         => __('setlists.song_id_required'),
            'songs.*.id.exists'      => __('setlists.song_id_exists'),
            'songs.*.order.required' => __('setlists.order_required'),
        ];
    }
}
