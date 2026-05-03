<?php

namespace Modules\Media\Policies;

use Modules\User\Models\User;

class MediaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('media.list');
    }

    public function view(User $user, mixed $model): bool
    {
        return $user->checkPermissionTo('media.view');
    }

    public function create(User $user): bool
    {
        return $user->checkPermissionTo('media.create');
    }

    public function update(User $user, mixed $model): bool
    {
        return $user->checkPermissionTo('media.update');
    }

    public function delete(User $user, mixed $model): bool
    {
        return $user->checkPermissionTo('media.delete');
    }
}
