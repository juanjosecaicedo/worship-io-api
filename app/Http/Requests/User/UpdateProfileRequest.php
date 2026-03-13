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
            'name'   => ['sometimes', 'string', 'max:100'],
            'phone'  => ['nullable', 'string', 'max:20'],
            'username' => ['sometimes', 'string', 'max:100', Rule::unique('users')->ignore($this->user()->id)],
            'email'  => [
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
            'name.sometimes' => 'El nombre debe ser una cadena de texto.',
            'name.string' => 'El nombre debe ser una cadena de texto.',
            'name.max' => 'El nombre debe tener como máximo 100 caracteres.',
            'phone.nullable' => 'El teléfono debe ser una cadena de texto.',
            'phone.string' => 'El teléfono debe ser una cadena de texto.',
            'phone.max' => 'El teléfono debe tener como máximo 20 caracteres.',
            'email.sometimes' => 'El correo electrónico debe ser una cadena de texto.',
            'email.email' => 'El correo electrónico debe ser válido.',
            'email.max' => 'El correo electrónico debe tener como máximo 150 caracteres.',
            'email.unique' => 'El correo electrónico ya existe.',
            'username.sometimes' => 'El nombre de usuario debe ser una cadena de texto.',
            'username.string' => 'El nombre de usuario debe ser una cadena de texto.',
            'username.max' => 'El nombre de usuario debe tener como máximo 100 caracteres.',
            'username.unique' => 'El nombre de usuario ya existe.',
        ];
    }
}
