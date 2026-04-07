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
            /**
             * The title of the event.
             * @example Sunday Morning Service
             */
            'title'          => ['required', 'string', 'max:150'],

            /**
             * The type of the event.
             * @example service
             */
            'type'           => ['required', 'in:service,rehearsal,concert,meeting,other'],

            /**
             * A detailed description of the event.
             * @example Join us for our weekly morning worship service.
             */
            'description'    => ['nullable', 'string'],

            /**
             * The location where the event takes place.
             * @example Main Auditorium
             */
            'location'       => ['nullable', 'string', 'max:200'],

            /**
             * The start date and time of the event (must be in the future).
             * @example 2026-04-12 09:00:00
             */
            'start_datetime' => ['required', 'date', 'after:now'],

            /**
             * The end date and time of the event (must be after the start).
             * @example 2026-04-12 11:00:00
             */
            'end_datetime'   => ['required', 'date', 'after:start_datetime'],

            /**
             * Hexadecimal color code for the event.
             * @example #3B82F6
             */
            'color'          => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required'          => __('events.title_required'),
            'type.required'           => __('events.type_required'),
            'type.in'                 => __('events.type_in'),
            'start_datetime.required' => __('events.start_datetime_required'),
            'start_datetime.after'    => __('events.start_datetime_after'),
            'end_datetime.required'   => __('events.end_datetime_required'),
            'end_datetime.after'      => __('events.end_datetime_after'),
            'color.regex'             => __('events.color_regex'),
        ];
    }
}
