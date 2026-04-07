<?php

namespace App\Http\Requests\GlobalSong;

use App\Models\GlobalSong;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GlobalSongFilterRequest extends FormRequest
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
             * Search by title or author.
             * @example Amazing Grace
             */
            'search'  => ['nullable', 'string', 'max:100'],

            /**
             * Filter by original key.
             * @example G
             */
            'key'     => ['nullable', 'string', Rule::in(GlobalSong::VALID_KEYS)],

            /**
             * Filter by genre.
             * @example Hymn
             */
            'genre'   => ['nullable', 'string', 'max:50'],

            /**
             * Filter by tag.
             * @example worship
             */
            'tag'     => ['nullable', 'string', 'max:30'],

            /**
             * Number of items per page.
             * @example 15
             */
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'key.in'      => __('global_songs.original_key_in'),
            'per_page.min' => __('global_songs.per_page_min'),
            'per_page.max' => __('global_songs.per_page_max'),
        ];
    }
}
