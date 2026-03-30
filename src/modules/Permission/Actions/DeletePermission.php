<?php

namespace Modules\Permission\Actions;

use Modules\Permission\Models\Permission;

class DeletePermission
{
    public function execute(Permission $permission): void
    {
        $permission->delete();
    }
}
