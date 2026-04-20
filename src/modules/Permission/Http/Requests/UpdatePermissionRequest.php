<?php

namespace Modules\Permission\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Permission\Models\Permission;

class UpdatePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $permission = Permission::findOrFail($this->route('id'));

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('permissions')->ignore($permission->id)->where('guard_name', $permission->guard_name)->whereNull('deleted_at')],
        ];
    }
}
