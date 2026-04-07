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
            /**
             * The role of the member in the group.
             * @example leader
             */
            'role' => ['sometimes', 'in:admin,leader,vocalist,musician,choir,instrument,technician'],

            /**
             * The specific instrument played.
             * @example Bass
             */
            'instrument' => ['nullable', 'string', 'max:50'],

            /**
             * Date when the member joined the group.
             * @example 2026-04-01
             */
            'joined_at' => ['nullable', 'date'],

            /**
             * Whether the membership is active.
             * @example true
             */
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'role.sometimes' => __('groups.role_required'),
            'role.in' => __('groups.role_in'),
        ];
    }
}
