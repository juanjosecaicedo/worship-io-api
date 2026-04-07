<?php

namespace App\Http\Requests\GlobalSong;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateSectionRequest extends FormRequest
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
             * The type of section.
             * @example chorus
             */
            'type'   => ['required', 'in:intro,verse,pre_chorus,chorus,bridge,outro,instrumental,tag,vamp'],

            /**
             * Custom label for the section.
             * @example Chorus 1
             */
            'label'  => ['required', 'string', 'max:50'],

            /**
             * Lyrics for this section.
             */
            'lyrics' => ['nullable', 'string'],

            /**
             * Chords data for this section.
             */
            'chords' => ['nullable', 'array'],

            /**
             * Order of the section in the song structure.
             * @example 1
             */
            'order'  => ['required', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required'  => __('global_songs.section_type_required'),
            'type.in'        => __('global_songs.section_type_in'),
            'label.required' => __('global_songs.section_label_required'),
        ];
    }
}
