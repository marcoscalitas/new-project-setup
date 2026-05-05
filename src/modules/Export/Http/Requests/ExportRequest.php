<?php

namespace Modules\Export\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Shared\Contracts\Export\ExportRegistry;

class ExportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $registry = app(ExportRegistry::class);

        return [
            'module' => ['required', 'string', Rule::in(array_keys($registry->all()))],
            'format' => ['required', 'string'],
            'filters' => ['sometimes', 'array'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $registry = app(ExportRegistry::class);
            $module = $this->input('module');
            $format = $this->input('format');

            if (! is_string($module) || ! is_string($format) || ! $registry->has($module)) {
                return;
            }

            if (! in_array($format, $registry->get($module)->allowedFormats(), true)) {
                $validator->errors()->add('format', 'The selected format is invalid for this export module.');
            }
        });
    }
}
