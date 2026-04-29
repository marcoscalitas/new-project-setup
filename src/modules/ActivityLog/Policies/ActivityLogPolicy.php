<?php

namespace Modules\ActivityLog\Policies;

use Modules\Core\Policies\BasePolicy;
use Modules\User\Models\User;
use Spatie\Activitylog\Models\Activity;

class ActivityLogPolicy extends BasePolicy
{
    protected function permissionPrefix(): string
    {
        return 'log';
    }

    public function view(User $user, mixed $model): bool
    {
        return parent::view($user, $model) || $user->id === $model->causer_id;
    }
}
