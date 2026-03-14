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
            'name' => ['sometimes', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'username' => ['sometimes', 'string', 'max:100', Rule::unique('users')->ignore($this->user()->id)],
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
            'name.sometimes' => 'The name must be a string.',
            'name.string' => 'The name must be a string.',
            'name.max' => 'The name must be at most 100 characters.',
            'phone.nullable' => 'The phone must be a string.',
            'phone.string' => 'The phone must be a string.',
            'phone.max' => 'The phone must be at most 20 characters.',
            'email.sometimes' => 'The email must be a string.',
            'email.email' => 'The email must be valid.',
            'email.max' => 'The email must be at most 150 characters.',
            'email.unique' => 'The email already exists.',
            'username.sometimes' => 'The username must be a string.',
            'username.string' => 'The username must be a string.',
            'username.max' => 'The username must be at most 100 characters.',
            'username.unique' => 'The username already exists.',
        ];
    }
}
