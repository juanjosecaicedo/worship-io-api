<?php

namespace App\Http\Requests\User;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateVocalProfileRequest extends FormRequest
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
             * The user's vocal type.
             * @example Tenor
             */
            'vocal_type' => ['required', 'string', 'in:Soprano,Mezzo-Soprano,Alto,Tenor,Baritone,Bass'],

            /**
             * Whether the user can do lead vocals.
             * @example true
             */
            'can_lead'   => ['sometimes', 'boolean'],

            /**
             * Whether the user can do harmony.
             * @example true
             */
            'can_harmony' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'vocal_type.in' => __('users.vocal_type_in'),
        ];
    }
}
