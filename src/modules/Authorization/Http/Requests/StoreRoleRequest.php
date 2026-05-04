<?php

namespace Modules\Authorization\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $guard = auth('api')->check() ? 'api' : 'web';

        return [
            'name'          => ['required', 'string', 'max:255', Rule::unique('roles')->where('guard_name', $guard)->whereNull('deleted_at')],
            'permissions'   => ['sometimes', 'array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')->whereNull('deleted_at')],
        ];
    }
}
