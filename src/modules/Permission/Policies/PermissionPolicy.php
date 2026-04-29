<?php

namespace Modules\Permission\Policies;

use Modules\Core\Policies\BasePolicy;

class PermissionPolicy extends BasePolicy
{
    protected function permissionPrefix(): string
    {
        return 'permission';
    }
}
