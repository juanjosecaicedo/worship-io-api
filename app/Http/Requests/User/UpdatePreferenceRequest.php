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
            /**
             * The preference key to update.
             * @example language
             */
            'key'   => ['required', 'string'],

            /**
             * The preference value.
             * @example en
             */
            'value' => ['required'],
        ];
    }

    public function messages(): array
    {
        return [
            'key.required'   => __('users.preference_key_required'),
            'value.required' => __('users.preference_value_required'),
        ];
    }
}
