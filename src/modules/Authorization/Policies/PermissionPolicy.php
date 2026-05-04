<?php

namespace Modules\Authorization\Policies;

use Modules\User\Models\User;

class PermissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('permission.list');
    }

    public function view(User $user, mixed $model): bool
    {
        return $user->checkPermissionTo('permission.view');
    }

    public function create(User $user): bool
    {
        return $user->checkPermissionTo('permission.create');
    }

    public function update(User $user, mixed $model): bool
    {
        return $user->checkPermissionTo('permission.update');
    }

    public function delete(User $user, mixed $model): bool
    {
        return $user->checkPermissionTo('permission.delete');
    }

    public function viewTrashed(User $user): bool
    {
        return $user->checkPermissionTo('permission.delete');
    }

    public function restore(User $user, mixed $model): bool
    {
        return $user->checkPermissionTo('permission.delete');
    }
}
