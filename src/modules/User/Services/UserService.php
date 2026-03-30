<?php

namespace Modules\User\Services;

use Modules\Permission\Events\RoleAssigned;
use Modules\User\Actions\CreateUser;
use Modules\User\Actions\DeleteUser;
use Modules\User\Actions\UpdateUser;
use Modules\User\Events\UserDeleted;
use Modules\User\Events\UserUpdated;
use Modules\User\Models\User;
use Spatie\Permission\Models\Role;

class UserService
{
    public function __construct(
        private CreateUser $createUser,
        private UpdateUser $updateUser,
        private DeleteUser $deleteUser,
    ) {}

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
        $user = $this->createUser->execute($data);

        if (!empty($data['roles'])) {
            $this->dispatchRoleAssignedEvents($user, $data['roles']);
        }

        return $user;
    }

    public function update(int $id, array $data): User
    {
        $user = User::findOrFail($id);
        $user = $this->updateUser->execute($user, $data);

        if (array_key_exists('roles', $data) && !empty($data['roles'])) {
            $this->dispatchRoleAssignedEvents($user, $data['roles']);
        }

        UserUpdated::dispatch($user);

        return $user;
    }

    public function delete(int $id): void
    {
        $user = User::findOrFail($id);
        $userId = $user->id;
        $userEmail = $user->email;

        $this->deleteUser->execute($user);

        UserDeleted::dispatch($userId, $userEmail);
    }

    /**
     * Dispatch RoleAssigned events for each assigned role.
     */
    private function dispatchRoleAssignedEvents(User $user, array $roleNames): void
    {
        foreach ($roleNames as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                RoleAssigned::dispatch($user, $role);
            }
        }
    }
}
