<?php

namespace Modules\Permission\Policies;

use Modules\User\Models\User;
use Modules\Permission\Models\Role;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('role.list');
    }

    public function view(User $user, Role $role): bool
    {
        return $user->checkPermissionTo('role.view');
    }

    public function create(User $user): bool
    {
        return $user->checkPermissionTo('role.create');
    }

    public function update(User $user, Role $role): bool
    {
        return $user->checkPermissionTo('role.update');
    }

    public function delete(User $user, Role $role): bool
    {
        return $user->checkPermissionTo('role.delete');
    }
}
