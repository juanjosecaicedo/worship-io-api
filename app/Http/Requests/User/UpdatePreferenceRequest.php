<?php

namespace App\Http\Requests\User;

use App\Models\UserPreference;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePreferenceRequest extends FormRequest
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
            'key'   => ['required', 'string', 'in:' . implode(',', array_keys(UserPreference::DEFAULTS))],
            'value' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'key.required' => 'La clave de preferencia es obligatoria.',
            'key.in'       => 'La clave de preferencia no es válida.',
            'value.required' => 'El valor es obligatorio.',
        ];
    }
}
