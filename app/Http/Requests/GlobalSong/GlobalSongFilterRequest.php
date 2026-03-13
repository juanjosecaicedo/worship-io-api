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
            'search'  => ['nullable', 'string', 'max:100'],
            'key'     => ['nullable', 'string', Rule::in(GlobalSong::VALID_KEYS)],
            'genre'   => ['nullable', 'string', 'max:50'],
            'tag'     => ['nullable', 'string', 'max:30'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'key.in'      => 'La tonalidad no es válida.',
            'per_page.min' => 'La cantidad de canciones por página debe ser al menos 5.',
            'per_page.max' => 'La cantidad de canciones por página no puede exceder 100.',
        ];
    }
}
