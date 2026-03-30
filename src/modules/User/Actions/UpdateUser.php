<?php

namespace Modules\User\Actions;

use Illuminate\Support\Facades\Hash;
use Modules\User\Models\User;

class UpdateUser
{
    public function execute(User $user, array $data): User
    {
        $fields = [
            'name'  => $data['name'] ?? $user->name,
            'email' => $data['email'] ?? $user->email,
        ];

        if (!empty($data['password'])) {
            $fields['password'] = Hash::make($data['password']);
        }

        $user->update($fields);

        if (array_key_exists('roles', $data)) {
            $user->syncRoles($data['roles'] ?? []);
        }

        return $user->load('roles');
    }
}
