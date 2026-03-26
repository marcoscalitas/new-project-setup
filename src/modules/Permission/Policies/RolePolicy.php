<?php

namespace Modules\Permission\Policies;

use Modules\User\Models\User;
use Modules\Permission\Models\Role;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('role.list');
    }

    public function view(User $user, Role $role): bool
    {
        return $user->hasPermissionTo('role.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('role.create');
    }

    public function update(User $user, Role $role): bool
    {
        return $user->hasPermissionTo('role.update');
    }

    public function delete(User $user, Role $role): bool
    {
        return $user->hasPermissionTo('role.delete');
    }
}
