<?php

namespace App\Http\Requests\Subscription;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ChangePlanRequest extends FormRequest
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
             * The slug of the new plan.
             * @example premium-annual
             */
            'plan_slug' => ['required', 'exists:subscription_plans,slug'],
        ];
    }

    public function messages(): array
    {
        return [
            'plan_slug.required' => __('subscriptions.plan_slug_required'),
            'plan_slug.exists'   => __('subscriptions.plan_slug_exists'),
        ];
    }
}
