<x-mail::message>
# {{ __('auth.password_reset_title') }}

{{ __('auth.password_reset_intro') }}

<div style="text-align: center; margin: 30px 0;">
    <div style="display: inline-block; padding: 20px 40px; background-color: #f8f9fa; border-radius: 8px; border: 1px solid #e9ecef;">
        <span style="font-size: 32px; font-weight: bold; letter-spacing: 10px; color: #333;">
            {{ $code }}
        </span>
    </div>
</div>

{{ __('auth.password_reset_footer', ['minutes' => 60]) }}

@lang('auth.thanks'),<br>
{{ config('app.name') }}
</x-mail::message>
