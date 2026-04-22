<?php

namespace Modules\Export\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'module'  => ['required', 'string', 'in:users,activity_log'],
            'format'  => ['required', 'string', 'in:csv,xlsx,pdf'],
            'filters' => ['sometimes', 'array'],
        ];
    }
}
