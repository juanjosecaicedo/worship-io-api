<?php

namespace App\Http\Requests\Setlist;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSetlistRequest extends FormRequest
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
             * The name of the setlist.
             * @example New Setlist Name
             */
            'name' => ['required', 'string', 'max:150'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('setlists.setlist_name_required'),
        ];
    }
}
