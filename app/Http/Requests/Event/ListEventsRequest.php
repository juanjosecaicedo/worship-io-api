<?php

namespace App\Http\Requests\Event;

use Illuminate\Foundation\Http\FormRequest;

class ListEventsRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'from' => 'nullable|date',
            'to' => 'nullable|date',
            'type' => 'nullable|string',
            'status' => 'nullable|string',
            'month' => 'nullable|integer|min:1|max:12',
            'year' => 'nullable|integer',
            'upcoming' => 'nullable|boolean',
            'past' => 'nullable|boolean',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ];
    }
}
