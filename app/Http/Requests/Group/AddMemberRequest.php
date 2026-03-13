<?php

namespace App\Http\Requests\Group;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AddMemberRequest extends FormRequest
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
            'user_id' => ['required', 'exists:users,id'],
            'role' => ['required', 'in:admin,leader,vocalist,musician,choir,instrument,technician'],
            'instrument' => ['nullable', 'string', 'max:50'],
            'joined_at' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'El usuario es obligatorio.',
            'user_id.exists' => 'El usuario no existe.',
            'role.required' => 'El rol es obligatorio.',
            'role.in' => 'El rol debe ser uno de los siguientes: admin, leader, vocalist, musician, choir, instrument, technician.',
        ];
    }
}
