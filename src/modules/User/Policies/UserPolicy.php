<?php

namespace Modules\User\Policies;

use Modules\User\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('user.list');
    }

    public function view(User $user, mixed $model): bool
    {
        return $user->id === $model->id || $user->checkPermissionTo('user.view');
    }

    public function create(User $user): bool
    {
        return $user->checkPermissionTo('user.create');
    }

    public function update(User $user, mixed $model): bool
    {
        return $user->id === $model->id || $user->checkPermissionTo('user.update');
    }

    public function delete(User $user, mixed $model): bool
    {
        return $user->checkPermissionTo('user.delete');
    }

    public function viewTrashed(User $user): bool
    {
        return $user->checkPermissionTo('user.delete');
    }

    public function restore(User $user, mixed $model): bool
    {
        return $user->checkPermissionTo('user.delete');
    }
}
