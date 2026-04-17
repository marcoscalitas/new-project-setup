<?php

namespace Modules\User\Services;

use Illuminate\Support\Facades\Hash;
use Modules\Auth\Events\UserCreated;
use Modules\Permission\Events\RoleAssigned;
use Modules\User\Events\UserDeleted;
use Modules\User\Events\UserUpdated;
use Modules\User\Models\User;
use Spatie\Permission\Models\Role;

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
            $this->assignRolesToUser($user, $data['roles']);
        }

        UserCreated::dispatch($user);

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
            $this->assignRolesToUser($user, $data['roles'] ?? []);
        }

        UserUpdated::dispatch($user);

        return $user->load('roles');
    }

    public function delete(int $id): void
    {
        $user = User::findOrFail($id);
        $userId = $user->id;
        $userEmail = $user->email;

        $user->delete();

        UserDeleted::dispatch($userId, $userEmail);
    }

    /**
     * Assign roles to a user and dispatch events.
     */
    private function assignRolesToUser(User $user, array $roleNames): void
    {
        $user->syncRoles($roleNames);

        foreach ($roleNames as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                RoleAssigned::dispatch($user, $role);
            }
        }
    }
}
