<?php

namespace App\Http\Requests\Event;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateRecurringEventRequest extends FormRequest
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
            // Datos del evento plantilla
            'title'          => ['required', 'string', 'max:150'],
            'type'           => ['required', 'in:service,rehearsal,concert,meeting,other'],
            'description'    => ['nullable', 'string'],
            'location'       => ['nullable', 'string', 'max:200'],
            'start_time'     => ['required', 'date_format:H:i'],   // solo hora
            'end_time'       => ['required', 'date_format:H:i', 'after:start_time'],
            'color'          => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],

            // Datos de la recurrencia
            'recurrence.frequency'          => ['required', 'in:daily,weekly,monthly'],
            'recurrence.interval'           => ['sometimes', 'integer', 'min:1', 'max:12'],
            'recurrence.days_of_week'       => ['required_if:recurrence.frequency,weekly', 'array'],
            'recurrence.days_of_week.*'     => ['integer', 'min:0', 'max:6'],
            'recurrence.day_of_month'       => ['required_if:recurrence.frequency,monthly', 'integer', 'min:1', 'max:28'],
            'recurrence.starts_at'          => ['required', 'date', 'after_or_equal:today'],
            'recurrence.ends_at'            => ['nullable', 'date', 'after:recurrence.starts_at'],
            'recurrence.occurrences_limit'  => ['nullable', 'integer', 'min:1', 'max:365'],
        ];
    }

    public function messages(): array
    {
        return [
            'recurrence.frequency.required'       => 'La frecuencia es obligatoria.',
            'recurrence.days_of_week.required_if' => 'Para eventos semanales debes seleccionar al menos un día.',
            'recurrence.day_of_month.required_if' => 'Para eventos mensuales debes indicar el día del mes.',
            'recurrence.starts_at.required'       => 'La fecha de inicio de la recurrencia es obligatoria.',
        ];
    }
}
