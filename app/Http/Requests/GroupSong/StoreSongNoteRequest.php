<?php

namespace App\Http\Requests\GroupSong;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSongNoteRequest extends FormRequest
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
             * The section ID to attach the note to.
             * @example 1
             */
            'section_id' => ['nullable', 'exists:group_song_sections,id'],

            /**
             * The type of note.
             * @example verse
             */
            'type'       => ['required', 'in:intro,verse,pre_chorus,chorus,bridge,outro,instrumental,tag,vamp'],

            /**
             * The content of the note.
             * @example Play with light touch.
             */
            'content'    => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required'    => __('group_songs.note_type_required'),
            'content.required' => __('group_songs.note_content_required'),
        ];
    }
}
