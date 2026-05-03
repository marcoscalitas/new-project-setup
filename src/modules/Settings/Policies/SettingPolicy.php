<?php

namespace Modules\Settings\Policies;

use Modules\User\Models\User;

class SettingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('setting.list');
    }

    // Settings are authorized against the class, not a model instance — $model is optional.

    public function view(User $user, mixed $model = null): bool
    {
        return $user->checkPermissionTo('setting.view');
    }

    public function create(User $user): bool
    {
        return $user->checkPermissionTo('setting.create');
    }

    public function update(User $user, mixed $model = null): bool
    {
        return $user->checkPermissionTo('setting.update');
    }

    public function delete(User $user, mixed $model = null): bool
    {
        return $user->checkPermissionTo('setting.delete');
    }
}
