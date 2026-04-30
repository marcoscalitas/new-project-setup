<?php

namespace Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (!is_array($this->roles)) {
            $this->merge(['roles' => []]);
        }
    }

    public function rules(): array
    {
        return [
            'name'     => ['sometimes', 'string', 'max:255'],
            'email'    => ['sometimes', 'string', 'email', 'max:255', Rule::unique('users')->ignore($this->route('user')?->id)->whereNull('deleted_at')],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'roles'    => ['sometimes', 'array'],
            'roles.*'  => ['string', Rule::exists('roles', 'name')->whereNull('deleted_at')],
        ];
    }
}
