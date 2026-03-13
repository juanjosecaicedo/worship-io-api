<?php

namespace App\Http\Requests\Notification;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateReminderRequest extends FormRequest
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
            'minutes_before' => ['required', 'integer', 'min:5', 'max:20160'], // max 2 semanas
            'channel'        => ['required', 'in:push,email,sms,in_app,whatsapp,both'],
        ];
    }

    public function messages(): array
    {
        return [
            'minutes_before.required' => 'Los minutos son obligatorios.',
            'minutes_before.min'      => 'El recordatorio debe ser al menos 5 minutos antes.',
            'minutes_before.max'      => 'El recordatorio no puede ser más de 2 semanas antes.',
            'channel.required'        => 'El canal es obligatorio.',
            'channel.in'              => 'El canal debe ser: push, email o both.',
        ];
    }
}
