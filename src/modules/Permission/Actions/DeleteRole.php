<?php

namespace Modules\Permission\Actions;

use Modules\Permission\Models\Role;

class DeleteRole
{
    public function execute(Role $role): void
    {
        $role->delete();
    }
}
