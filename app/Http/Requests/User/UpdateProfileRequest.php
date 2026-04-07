<?php

namespace App\Http\Requests\User;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
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
             * The user's full name.
             * @example John Doe
             */
            'name' => ['sometimes', 'string', 'max:100'],

            /**
             * The user's phone number.
             * @example +1234567890
             */
            'phone' => ['nullable', 'string', 'max:20'],

            /**
             * The unique username.
             * @example john_doe
             */
            'username' => ['sometimes', 'string', 'max:100', Rule::unique('users')->ignore($this->user()->id)],

            /**
             * The user's email address.
             * @example john@example.com
             */
            'email' => [
                'sometimes',
                'email',
                'max:150',
                Rule::unique('users')->ignore($this->user()->id),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => __('users.name_string'),
            'name.max' => __('users.name_max'),
            'phone.string' => __('users.phone_string'),
            'phone.max' => __('users.phone_max'),
            'email.email' => __('users.email_email'),
            'email.max' => __('users.email_max'),
            'email.unique' => __('users.email_unique'),
            'username.string' => __('users.username_string'),
            'username.max' => __('users.username_max'),
            'username.unique' => __('users.username_unique'),
        ];
    }
}
