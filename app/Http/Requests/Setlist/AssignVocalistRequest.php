<?php

namespace App\Http\Requests\Setlist;

use App\Models\GlobalSong;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignVocalistRequest extends FormRequest
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
            'user_id'      => ['required', 'exists:users,id'],
            'vocal_role'   => ['required', 'in:lead,harmony,choir'],
            'key_override' => ['nullable', 'string', Rule::in(GlobalSong::VALID_KEYS)],
            'notes'        => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required'    => 'El vocalista es obligatorio.',
            'user_id.exists'      => 'El usuario no existe.',
            'vocal_role.required' => 'El rol vocal es obligatorio.',
            'vocal_role.in'       => 'El rol debe ser: lead, harmony o choir.',
        ];
    }
}
