<?php

namespace App\Http\Requests\GlobalSong;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ReorderSectionsRequest extends FormRequest
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
             * List of sections with their new order.
             */
            'sections'         => ['required', 'array', 'min:1'],

            /**
             * The section ID.
             * @example 1
             */
            'sections.*.id'    => ['required', 'integer', 'exists:global_song_sections,id'],

            /**
             * The new order for the section.
             * @example 0
             */
            'sections.*.order' => ['required', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'sections.required'         => __('global_songs.sections_required'),
            'sections.*.id.exists'      => __('global_songs.section_id_exists'),
            'sections.*.order.required' => __('global_songs.section_order_required'),
        ];
    }
}
