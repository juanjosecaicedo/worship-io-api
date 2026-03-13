<?php

namespace App\Http\Requests\Event;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateEventRequest extends FormRequest
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
            'title'          => ['required', 'string', 'max:150'],
            'type'           => ['required', 'in:service,rehearsal,concert,meeting,other'],
            'description'    => ['nullable', 'string'],
            'location'       => ['nullable', 'string', 'max:200'],
            'start_datetime' => ['required', 'date', 'after:now'],
            'end_datetime'   => ['required', 'date', 'after:start_datetime'],
            'color'          => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'          => 'El título del evento es obligatorio.',
            'type.required'           => 'El tipo de evento es obligatorio.',
            'type.in'                 => 'El tipo debe ser: service, rehearsal, concert, meeting u other.',
            'start_datetime.required' => 'La fecha de inicio es obligatoria.',
            'start_datetime.after'    => 'El evento debe ser en una fecha futura.',
            'end_datetime.required'   => 'La fecha de fin es obligatoria.',
            'end_datetime.after'      => 'La fecha de fin debe ser posterior a la de inicio.',
            'color.regex'             => 'El color debe ser un código hexadecimal válido.',
        ];
    }
}
