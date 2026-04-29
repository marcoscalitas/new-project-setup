<?php

namespace Modules\Media\Policies;

use Modules\User\Models\User;

class MediaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('media.list');
    }

    public function view(User $user): bool
    {
        return $user->hasPermissionTo('media.list');
    }

    public function delete(User $user): bool
    {
        return $user->hasPermissionTo('media.delete');
    }
}
