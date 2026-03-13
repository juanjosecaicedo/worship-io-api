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
            'songs'         => ['required', 'array', 'min:1'],
            'songs.*.id'    => ['required', 'integer', 'exists:setlist_songs,id'],
            'songs.*.order' => ['required', 'integer', 'min:0'],
        ];
    }


    public function messages(): array
    {
        return [
            'songs.required'         => 'El listado de canciones es obligatorio.',
            'songs.*.id.exists'      => 'Una de las canciones del setlist no existe.',
            'songs.*.order.required' => 'El orden de cada canción es obligatorio.',
        ];
    }
}
