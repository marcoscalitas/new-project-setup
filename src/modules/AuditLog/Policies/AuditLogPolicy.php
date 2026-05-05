<?php

namespace Modules\AuditLog\Policies;

use Modules\User\Models\User;

class AuditLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('audit-log.list');
    }

    public function view(User $user, mixed $model): bool
    {
        return $user->checkPermissionTo('audit-log.view') || $user->id === $model->causer_id;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, mixed $model): bool
    {
        return false;
    }

    public function delete(User $user, mixed $model): bool
    {
        return false;
    }
}
