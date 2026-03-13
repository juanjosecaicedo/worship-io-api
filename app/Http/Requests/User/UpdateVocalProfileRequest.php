<?php

namespace App\Http\Requests\User;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateVocalProfileRequest extends FormRequest
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
            'voice_type'      => ['nullable', 'in:soprano,mezzo_soprano,alto,contralto,tenor,baritone,bass'],
            'comfortable_key' => ['nullable', 'string', 'max:5'],
            'range_min'       => ['nullable', 'string', 'max:5'],
            'range_max'       => ['nullable', 'string', 'max:5'],
            'notes'           => ['nullable', 'string'],
        ];
    }
}
