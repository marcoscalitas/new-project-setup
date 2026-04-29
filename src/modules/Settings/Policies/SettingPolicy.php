<?php

namespace Modules\Settings\Policies;

use Modules\User\Models\User;

class SettingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('setting.list');
    }

    public function view(User $user): bool
    {
        return $user->hasPermissionTo('setting.view');
    }

    public function update(User $user): bool
    {
        return $user->hasPermissionTo('setting.update');
    }

    public function delete(User $user): bool
    {
        return $user->hasPermissionTo('setting.delete');
    }
}
