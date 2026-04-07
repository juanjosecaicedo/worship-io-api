<?php

namespace App\Http\Requests\Group;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateGroupRequest extends FormRequest
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
             * The group name.
             * @example Worship Team
             */
            'name' => ['required', 'string', 'max:100'],

            /**
             * A description of the group.
             * @example The main worship team for the church.
             */
            'description' => ['nullable', 'string', 'max:1000'],

            /**
             * The hex color code for the group UI.
             * @example #6366F1
             */
            'color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],

            /**
             * The avatar URL for the group.
             */
            'avatar' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('groups.name_required'),
            'color.regex'   => __('groups.color_regex'),
        ];
    }
}
