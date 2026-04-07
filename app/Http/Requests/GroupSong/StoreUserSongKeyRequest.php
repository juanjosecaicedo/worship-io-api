<?php

namespace App\Http\Requests\GroupSong;

use App\Models\GlobalSong;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserSongKeyRequest extends FormRequest
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
             * The user's preferred key for this song.
             * @example G
             */
            'preferred_key' => ['required', 'string', Rule::in(GlobalSong::VALID_KEYS)],

            /**
             * Capo position.
             * @example 2
             */
            'capo'          => ['nullable', 'integer', 'min:0', 'max:12'],

            /**
             * Personal notes about the key or capo.
             */
            'notes'         => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'preferred_key.required' => __('group_songs.preferred_key_required'),
            'preferred_key.in'       => __('group_songs.custom_key_in'),
            'capo.max'               => __('group_songs.capo_max'),
        ];
    }
}
