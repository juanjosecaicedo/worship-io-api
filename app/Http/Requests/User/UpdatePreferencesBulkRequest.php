<?php

namespace App\Http\Requests\User;

use App\Models\UserPreference;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePreferencesBulkRequest extends FormRequest
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
        /**
         * An object containing multiple preferences.
         * @example {"language": "es", "theme": "dark"}
         */
        return [
            'preferences'         => ['required', 'array', 'min:1'],
            'preferences.*.key'   => ['required', 'string', 'in:' . implode(',', array_keys(UserPreference::DEFAULTS))],
            'preferences.*.value' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'preferences.required'         => __('users.preference_value_required'),
            'preferences.array'            => __('users.preferences_array'),
            'preferences.*.key.required'   => 'Cada preferencia debe tener una clave.',
            'preferences.*.key.in'         => 'Una de las claves no es válida.',
            'preferences.*.value.required' => 'Cada preferencia debe tener un valor.',
        ];
    }
}
