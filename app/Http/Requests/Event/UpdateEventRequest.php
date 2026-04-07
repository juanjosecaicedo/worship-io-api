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
            /**
             * The title of the event.
             * @example Sunday Morning Service
             */
            'title'          => ['sometimes', 'string', 'max:150'],

            /**
             * The type of the event.
             * @example service
             */
            'type'           => ['sometimes', 'in:service,rehearsal,concert,meeting,other'],

            /**
             * A detailed description of the event.
             */
            'description'    => ['nullable', 'string'],

            /**
             * The location where the event takes place.
             */
            'location'       => ['nullable', 'string', 'max:200'],

            /**
             * The start date and time of the event.
             */
            'start_datetime' => ['sometimes', 'date'],

            /**
             * The end date and time of the event.
             */
            'end_datetime'   => ['sometimes', 'date', 'after:start_datetime'],

            /**
             * The status of the event.
             * @example scheduled
             */
            'status'         => ['sometimes', 'in:scheduled,in_progress,completed,cancelled,postponed'],

            /**
             * Hexadecimal color code for the event.
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
            'type.in'              => __('events.type_in'),
            'end_datetime.after'   => __('events.end_datetime_after'),
            'color.regex'          => __('events.color_regex'),
        ];
    }
}
