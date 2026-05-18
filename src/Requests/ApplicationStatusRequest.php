<?php

namespace Azuriom\Plugin\Jobs\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApplicationStatusRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'status' => ['required', 'in:pending,reviewing,accepted,refused'],
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
