<?php

namespace Modules\Identity\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TwoFactorChallengeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'two_factor_token' => ['required', 'string', 'uuid'],
            'code'             => ['nullable', 'string', 'size:6'],
            'recovery_code'    => ['nullable', 'string'],
        ];
    }
}
