<?php

namespace Modules\User\Services;

use Illuminate\Support\Facades\Hash;
use Modules\User\Models\User;

class UserService
{
    public function getAll()
    {
        return User::with('roles')->get();
    }

    public function findById(int $id): User
    {
        return User::with('roles')->findOrFail($id);
    }

    public function create(array $data): User
    {
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        if (!empty($data['roles'])) {
            $user->syncRoles($data['roles']);
        }

        return $user->load('roles');
    }

    public function update(int $id, array $data): User
    {
        $user = User::findOrFail($id);

        $fields = ['name' => $data['name'] ?? $user->name, 'email' => $data['email'] ?? $user->email];

        if (!empty($data['password'])) {
            $fields['password'] = Hash::make($data['password']);
        }

        $user->update($fields);

        if (array_key_exists('roles', $data)) {
            $user->syncRoles($data['roles'] ?? []);
        }

        return $user->load('roles');
    }

    public function delete(int $id): void
    {
        User::findOrFail($id)->delete();
    }
}
