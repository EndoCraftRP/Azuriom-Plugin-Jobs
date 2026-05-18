<?php

namespace Azuriom\Plugin\Jobs\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PositionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'is_open' => ['nullable', 'boolean'],
            'max_pending' => ['nullable', 'integer', 'min:1'],
            'order' => ['nullable', 'integer', 'min:0'],
            'published_at' => ['nullable', 'date'],
            'closed_at' => ['nullable', 'date', 'after_or_equal:published_at'],
            'show_applications_count' => ['nullable', 'boolean'],
            'keywords' => ['nullable', 'string'],
            'fields' => ['nullable', 'array'],
        ];
    }
}
