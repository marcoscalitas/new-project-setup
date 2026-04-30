<?php

namespace Modules\User\Services;

use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Modules\Auth\Events\UserCreated;
use Modules\Permission\Events\RoleAssigned;
use Modules\User\Events\UserDeleted;
use Modules\User\Events\UserUpdated;
use Modules\User\Models\User;
use Modules\Permission\Models\Role;

class UserService
{
    public function getAll(?int $perPage = 15)
    {
        $query = User::with('roles');
        return $perPage === null ? $query->get() : $query->paginate($perPage);
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

        UserCreated::dispatch($user->ulid, $user->name, $user->email);

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
            $this->guardAgainstLastAdminRoleRemoval($user, $data['roles'] ?? []);
            $this->assignRolesToUser($user, $data['roles'] ?? []);
        }

        UserUpdated::dispatch($user->ulid, $user->name, $user->email);

        return $user->load('roles');
    }

    public function delete(int $id): void
    {
        $user = User::findOrFail($id);

        $this->guardAgainstLastAdminDeletion($user);

        $userUlid = $user->ulid;
        $userEmail = $user->email;

        $user->delete();

        UserDeleted::dispatch($userUlid, $userEmail);
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
                RoleAssigned::dispatch($user->ulid, $user->email, $role->name);
            }
        }
    }

    private function guardAgainstLastAdminRoleRemoval(User $user, array $newRoles): void
    {
        if (!$user->hasRole('admin')) {
            return;
        }

        if (in_array('admin', $newRoles)) {
            return;
        }

        $guardName = $user->roles->firstWhere('name', 'admin')?->guard_name ?? 'web';
        $adminCount = User::role('admin', $guardName)->count();

        if ($adminCount <= 1) {
            throw ValidationException::withMessages([
                'roles' => __('users.cannot_remove_last_admin_role'),
            ]);
        }
    }

    private function guardAgainstLastAdminDeletion(User $user): void
    {
        if (!$user->hasRole('admin')) {
            return;
        }

        $guardName = $user->roles->firstWhere('name', 'admin')?->guard_name ?? 'web';
        $adminCount = User::role('admin', $guardName)->count();

        if ($adminCount <= 1) {
            throw ValidationException::withMessages([
                'user' => __('users.cannot_delete_last_admin'),
            ]);
        }
    }
}
