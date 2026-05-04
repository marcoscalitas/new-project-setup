<?php

namespace Modules\Authorization\Policies;

use Modules\User\Models\User;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('role.list');
    }

    public function view(User $user, mixed $model): bool
    {
        return $user->checkPermissionTo('role.view');
    }

    public function create(User $user): bool
    {
        return $user->checkPermissionTo('role.create');
    }

    public function update(User $user, mixed $model): bool
    {
        return $user->checkPermissionTo('role.update');
    }

    public function delete(User $user, mixed $model): bool
    {
        return $user->checkPermissionTo('role.delete');
    }

    public function viewTrashed(User $user): bool
    {
        return $user->checkPermissionTo('role.delete');
    }

    public function restore(User $user, mixed $model): bool
    {
        return $user->checkPermissionTo('role.delete');
    }
}
