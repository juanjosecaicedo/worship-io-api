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
            'sections'         => ['required', 'array', 'min:1'],
            'sections.*.id'    => ['required', 'integer', 'exists:global_song_sections,id'],
            'sections.*.order' => ['required', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'sections.required'         => 'El listado de secciones es obligatorio.',
            'sections.*.id.exists'      => 'Una de las secciones no existe.',
            'sections.*.order.required' => 'El orden de cada sección es obligatorio.',
        ];
    }
}
