<?php

namespace Modules\Permission\Policies;

use Modules\Core\Policies\BasePolicy;

class RolePolicy extends BasePolicy
{
    protected function permissionPrefix(): string
    {
        return 'role';
    }
}
