<?php

namespace App\Http\Requests\Event;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
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
            'title'          => ['sometimes', 'string', 'max:150'],
            'type'           => ['sometimes', 'in:service,rehearsal,concert,meeting,other'],
            'description'    => ['nullable', 'string'],
            'location'       => ['nullable', 'string', 'max:200'],
            'start_datetime' => ['sometimes', 'date'],
            'end_datetime'   => ['sometimes', 'date', 'after:start_datetime'],
            'status'         => ['sometimes', 'in:scheduled,in_progress,completed,cancelled,postponed'],
            'color'          => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ];
    }
}
