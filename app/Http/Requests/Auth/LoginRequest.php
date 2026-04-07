<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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
            'email'       => ['required', 'email'],

            /**
             * The user's password.
             * @example secret123
             */
            'password'    => ['required', 'string'],

            /**
             * The name of the device making the request.
             * @example iPhone 15
             */
            'device_name' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required'    => __('auth.email_required'),
            'password.required' => __('auth.password_required'),
        ];
    }
}
