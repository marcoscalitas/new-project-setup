<?php

namespace Modules\Core\Policies;

use Modules\User\Models\User;

abstract class BasePolicy
{
    abstract protected function permissionPrefix(): string;

    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo($this->permissionPrefix() . '.list');
    }

    public function view(User $user, mixed $model): bool
    {
        return $user->checkPermissionTo($this->permissionPrefix() . '.view');
    }

    public function create(User $user): bool
    {
        return $user->checkPermissionTo($this->permissionPrefix() . '.create');
    }

    public function update(User $user, mixed $model): bool
    {
        return $user->checkPermissionTo($this->permissionPrefix() . '.update');
    }

    public function delete(User $user, mixed $model): bool
    {
        return $user->checkPermissionTo($this->permissionPrefix() . '.delete');
    }

    public function viewTrashed(User $user): bool
    {
        return $user->checkPermissionTo($this->permissionPrefix() . '.delete');
    }

    public function restore(User $user, mixed $model): bool
    {
        return $user->checkPermissionTo($this->permissionPrefix() . '.delete');
    }
}
