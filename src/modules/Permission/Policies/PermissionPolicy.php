<?php

namespace Modules\Permission\Policies;

use Modules\User\Models\User;
use Modules\Permission\Models\Permission;

class PermissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('permission.list');
    }

    public function view(User $user, Permission $permission): bool
    {
        return $user->hasPermissionTo('permission.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('permission.create');
    }

    public function update(User $user, Permission $permission): bool
    {
        return $user->hasPermissionTo('permission.update');
    }

    public function delete(User $user, Permission $permission): bool
    {
        return $user->hasPermissionTo('permission.delete');
    }
}
