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
            /**
             * The user ID to add to the group.
             * @example 1
             */
            'user_id' => ['required', 'exists:users,id'],

            /**
             * The role of the member in the group.
             * @example leader
             */
            'role' => ['required', 'in:admin,leader,vocalist,musician,choir,instrument,technician'],

            /**
             * The specific instrument played (if applicable).
             * @example Guitar
             */
            'instrument' => ['nullable', 'string', 'max:50'],

            /**
             * Date when the member joined the group.
             * @example 2026-04-01
             */
            'joined_at' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => __('groups.member_id_required'),
            'user_id.exists' => __('groups.member_id_exists'),
            'role.required' => __('groups.role_required'),
            'role.in' => __('groups.role_in'),
        ];
    }
}
