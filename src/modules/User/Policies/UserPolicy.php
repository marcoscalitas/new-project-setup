<?php

namespace Modules\User\Policies;

use Modules\User\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('user.list');
    }

    public function view(User $user, User $model): bool
    {
        return $user->id === $model->id || $user->hasPermissionTo('user.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('user.create');
    }

    public function update(User $user, User $model): bool
    {
        return $user->id === $model->id || $user->hasPermissionTo('user.update');
    }

    public function delete(User $user, User $model): bool
    {
        return $user->hasPermissionTo('user.delete');
    }
}
