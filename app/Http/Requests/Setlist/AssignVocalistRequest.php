<?php

namespace App\Http\Requests\Setlist;

use App\Models\GlobalSong;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignVocalistRequest extends FormRequest
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
             * The ID of the group member to assign.
             * @example 5
             */
            'group_member_id' => [
                'required',
                Rule::exists('group_members', 'id')->where('group_id', $this->route('group')->id)
            ],

            /**
             * Notes for the vocalist.
             */
            'notes'           => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'group_member_id.required' => __('setlists.vocalist_id_required'),
            'group_member_id.exists'   => __('setlists.vocalist_id_exists'),
        ];
    }
}
