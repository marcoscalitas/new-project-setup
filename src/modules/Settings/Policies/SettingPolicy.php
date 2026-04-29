<?php

namespace Modules\Settings\Policies;

use Modules\Core\Policies\BasePolicy;
use Modules\User\Models\User;

class SettingPolicy extends BasePolicy
{
    protected function permissionPrefix(): string
    {
        return 'setting';
    }

    // Settings are managed by key — actions are authorized against the class,
    // not a model instance, so these overrides make $model optional.

    public function view(User $user, mixed $model = null): bool
    {
        return $user->checkPermissionTo('setting.view');
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
