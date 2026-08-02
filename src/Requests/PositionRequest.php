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
            'fields.*.id' => ['nullable', 'integer'],
            'fields.*.label' => ['required_unless:fields.*.type,html', 'nullable', 'string', 'max:200'],
            'fields.*.type' => ['required', 'string', 'in:text,textarea,number,select,checkbox,radio,date,date_range,html'],
            'fields.*.col_md' => ['required', 'integer', 'in:12,6,4'],
            'fields.*.options' => ['nullable', 'string'],
            'fields.*.is_required' => ['nullable', 'boolean'],
            'fields.*.allow_other' => ['nullable', 'boolean'],
            'fields.*.min' => ['nullable', 'integer', 'min:0'],
            'fields.*.max' => ['nullable', 'integer', 'min:0'],
            'fields.*.regex' => ['nullable', 'string'],
            'fields.*.html' => ['nullable', 'string'],
        ];
    }
}
