<?php

namespace App\Http\Requests\Group;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMemberRequest extends FormRequest
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
            'role' => ['sometimes', 'in:admin,leader,vocalist,musician,choir,instrument,technician'],
            'instrument' => ['nullable', 'string', 'max:50'],
            'joined_at' => ['nullable', 'date'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'role.sometimes' => 'El rol es obligatorio.',
            'role.in' => 'El rol debe ser uno de los siguientes: admin, leader, vocalist, musician, choir, instrument, technician.',
        ];
    }
}
