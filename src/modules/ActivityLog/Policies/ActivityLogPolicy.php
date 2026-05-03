<?php

namespace Modules\ActivityLog\Policies;

use Modules\User\Models\User;

class ActivityLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('log.list');
    }

    public function view(User $user, mixed $model): bool
    {
        return $user->checkPermissionTo('log.view') || $user->id === $model->causer_id;
    }

    public function create(User $user): bool
    {
        return $user->checkPermissionTo('log.create');
    }

    public function update(User $user, mixed $model): bool
    {
        return $user->checkPermissionTo('log.update');
    }

    public function delete(User $user, mixed $model): bool
    {
        return $user->checkPermissionTo('log.delete');
    }
}
