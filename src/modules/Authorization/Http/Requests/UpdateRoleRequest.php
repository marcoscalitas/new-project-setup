<?php

namespace Modules\Authorization\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (!is_array($this->permissions)) {
            $this->merge(['permissions' => []]);
        }
    }

    public function rules(): array
    {
        $role = $this->route('role');

        return [
            'name'          => ['required', 'string', 'max:255', Rule::unique('roles')->ignore($role->id)->where('guard_name', $role->guard_name)->whereNull('deleted_at')],
            'permissions'   => ['sometimes', 'array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')->whereNull('deleted_at')],
        ];
    }
}
