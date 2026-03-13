<?php

namespace App\Http\Requests\GroupSong;

use App\Models\GlobalSong;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGroupSongRequest extends FormRequest
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
            'title'                 => ['sometimes', 'string', 'max:200'],
            'author'                => ['nullable', 'string', 'max:150'],
            'custom_key'            => ['nullable', 'string', Rule::in(GlobalSong::VALID_KEYS)],
            'custom_tempo'          => ['nullable', 'integer', 'min:20', 'max:300'],
            'custom_time_signature' => ['nullable', 'string', 'in:4/4,3/4,6/8,12/8,2/4,5/4,7/8'],
            'genre'                 => ['nullable', 'string', 'max:50'],
            'tags'                  => ['nullable', 'array'],
            'tags.*'                => ['string', 'max:30'],
            'youtube_url'           => ['nullable', 'url', 'max:500'],
            'is_public'             => ['sometimes', 'boolean'],
            'sections_order'        => ['nullable', 'array'],
        ];
    }
}
