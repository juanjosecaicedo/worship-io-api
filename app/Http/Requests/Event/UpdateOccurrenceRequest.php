<?php

namespace App\Http\Requests\Event;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateOccurrenceRequest extends FormRequest
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
            'scope' => ['required', 'in:this|this_and_following|all'],
            // Datos opcionales del evento
            'title'          => ['sometimes', 'string', 'max:150'],
            'description'    => ['nullable', 'string'],
            'location'       => ['nullable', 'string', 'max:200'],
            'start_time'     => ['sometimes', 'date_format:H:i'],
            'end_time'       => ['sometimes', 'date_format:H:i', 'after:start_time'],
            'status'         => ['sometimes', 'in:scheduled,cancelled'],
        ];
    }

    public function messages(): array
    {
        return [
            'scope.required' => 'Debes indicar el alcance de la edición.',
            'scope.in'       => 'El alcance debe ser: this, this_and_following o all.',
        ];
    }
}
