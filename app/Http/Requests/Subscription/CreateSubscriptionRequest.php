<?php

namespace App\Http\Requests\Subscription;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateSubscriptionRequest extends FormRequest
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
            'plan_slug' => ['required', 'exists:subscription_plans,slug'],
            'gateway'   => ['sometimes', 'in:stripe,mercadopago'],
        ];
    }

    public function messages(): array
    {
        return [
            'plan_slug.required' => 'El plan es obligatorio.',
            'plan_slug.exists'   => 'El plan seleccionado no existe.',
        ];
    }
}
