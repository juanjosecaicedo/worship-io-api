<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Create a new class instance.
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
             * The user's full name.
             * @example John Doe
             */
            'name'     => ['required', 'string', 'max:100'],

            /**
             * The user's email address.
             * @example john@example.com
             */
            'email'    => ['required', 'email', 'max:150', 'unique:users,email'],

            /**
             * The user's unique username.
             * @example johndoe
             */
            'username' => ['required', 'string', 'max:100', 'unique:users,username'],

            /**
             * The user's password.
             * @example secret123
             */
            'password' => ['required', 'string', 'min:8', 'confirmed'],

            /**
             * The user's phone number.
             * @example +1234567890
             */
            'phone'    => ['nullable', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('auth.name_required'),
            'name.max' => __('auth.name_max'),
            'email.required' => __('auth.email_required'),
            'email.email' => __('auth.email_valid'),
            'email.max' => __('auth.email_max'),
            'email.unique' => __('auth.email_unique'),
            'username.required' => __('auth.username_required'),
            'username.max' => __('auth.username_max'),
            'username.unique' => __('auth.username_unique'),
            'password.required' => __('auth.password_required'),
            'password.min' => __('auth.password_min'),
            'password.confirmed' => __('auth.password_confirmed'),
            'phone.max' => __('auth.phone_max'),
        ];
    }
}
