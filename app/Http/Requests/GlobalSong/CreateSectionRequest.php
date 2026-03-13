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
            'type'   => ['required', 'in:intro,verse,pre_chorus,chorus,bridge,outro,instrumental,tag,vamp'],
            'label'  => ['required', 'string', 'max:50'],
            'lyrics' => ['nullable', 'string'],
            'chords' => ['nullable', 'array'],
            'order'  => ['required', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required'  => 'El tipo de sección es obligatorio.',
            'type.in'        => 'El tipo debe ser: intro, verse, pre_chorus, chorus, bridge, outro, instrumental, tag o vamp.',
            'label.required' => 'La etiqueta de la sección es obligatoria.',
        ];
    }
}
