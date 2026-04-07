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
            /**
             * The start date for the range filter. Format: YYYY-MM-DD.
             * @example 2026-01-01
             */
            'from' => 'nullable|date',

            /**
             * The end date for the range filter. Format: YYYY-MM-DD.
             * @example 2026-12-31
             */
            'to' => 'nullable|date',

            /**
             * Filter events by type.
             */
            'type' => 'nullable|string',

            /**
             * Filter events by status.
             */
            'status' => 'nullable|string',

            /**
             * Filter events by a specific month (1-12).
             */
            'month' => 'nullable|integer|min:1|max:12',

            /**
             * Filter events by a specific year.
             */
            'year' => 'nullable|integer',

            /**
             * If true, filters for upcoming events only.
             */
            'upcoming' => 'nullable|boolean',

            /**
             * If true, filters for past events only.
             */
            'past' => 'nullable|boolean',

            /**
             * Number of items per page.
             * @example 20
             */
            'per_page' => 'nullable|integer|min:1|max:100',

            /**
             * The page number for pagination.
             * @example 1
             */
            'page' => 'nullable|integer|min:1',
        ];
    }
}
