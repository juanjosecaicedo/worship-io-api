<?php

namespace App\Http\Requests\GroupSong;

use App\Models\GlobalSong;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserSongKeyRequest extends FormRequest
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
            'preferred_key' => ['required', 'string', Rule::in(GlobalSong::VALID_KEYS)],
            'capo'          => ['nullable', 'integer', 'min:0', 'max:12'],
            'notes'         => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'preferred_key.required' => 'El tono preferido es obligatorio.',
            'preferred_key.in'       => 'La tonalidad no es válida.',
            'capo.max'               => 'El capo no puede superar el traste 12.',
        ];
    }
}
