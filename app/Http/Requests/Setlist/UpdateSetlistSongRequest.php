<?php

namespace App\Http\Requests\Setlist;

use App\Models\GlobalSong;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSetlistSongRequest extends FormRequest
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
            'order'             => ['sometimes', 'integer', 'min:0'],

            /**
             * Key override for this specific setlist item.
             * @example G
             */
            'key_override'      => ['nullable', 'string', Rule::in(GlobalSong::VALID_KEYS)],

            /**
             * Duration override in seconds.
             */
            'duration_override' => ['nullable', 'integer', 'min:1'],

            /**
             * Public notes for the team.
             */
            'notes'             => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'key_override.in' => __('setlists.key_override_in'),
        ];
    }
}
