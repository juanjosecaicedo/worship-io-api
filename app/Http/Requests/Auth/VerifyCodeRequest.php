<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class VerifyCodeRequest extends FormRequest
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
             * The user's email address.
             * @example john@example.com
             */
            'email' => ['required', 'email', 'exists:users,email'],

            /**
             * The 6-digit code sent to the user's email.
             * @example 123456
             */
            'code'  => ['required', 'string', 'size:6'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => __('auth.email_required'),
            'email.email' => __('auth.email_valid'),
            'email.exists' => __('auth.email_not_found'),
            'code.required' => __('auth.code_required'),
            'code.size' => __('auth.code_size'),
        ];
    }
}
