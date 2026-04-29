<?php

namespace Modules\User\Policies;

use Modules\Core\Policies\BasePolicy;
use Modules\User\Models\User;

class UserPolicy extends BasePolicy
{
    protected function permissionPrefix(): string
    {
        return 'user';
    }

    public function view(User $user, mixed $model): bool
    {
        return $user->id === $model->id || parent::view($user, $model);
    }

    public function update(User $user, mixed $model): bool
    {
        return $user->id === $model->id || parent::update($user, $model);
    }
}
