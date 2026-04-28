<?php

namespace Modules\ActivityLog\Policies;

use Modules\User\Models\User;
use Spatie\Activitylog\Models\Activity;

class ActivityLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('log.list');
    }

    public function view(User $user, Activity $activity): bool
    {
        if ($user->checkPermissionTo('log.view')) {
            return true;
        }

        return $user->id === $activity->causer_id;
    }
}
