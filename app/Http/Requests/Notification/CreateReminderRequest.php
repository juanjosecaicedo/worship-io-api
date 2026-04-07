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
            /**
             * Minutes before the event to send the reminder.
             * @example 30
             */
            'minutes_before' => ['required', 'integer', 'min:5', 'max:20160'],

            /**
             * The channel to send the notification through.
             * @example push
             */
            'channel'        => ['required', 'in:push,email,sms,in_app,whatsapp,both'],
        ];
    }

    public function messages(): array
    {
        return [
            'minutes_before.required' => __('notifications.minutes_required'),
            'minutes_before.min'      => __('notifications.minutes_min'),
            'minutes_before.max'      => __('notifications.minutes_max'),
            'channel.required'        => __('notifications.channel_required'),
            'channel.in'              => __('notifications.channel_in'),
        ];
    }
}
