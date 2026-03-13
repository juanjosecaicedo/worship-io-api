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
            'section_id' => ['nullable', 'exists:group_song_sections,id'],
            'type'       => ['required', 'in:intro,verse,pre_chorus,chorus,bridge,outro,instrumental,tag,vamp'],
            'content'    => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required'    => 'El tipo de nota es obligatorio.',
            'content.required' => 'El contenido de la nota es obligatorio.',
        ];
    }
}
