<?php

namespace Modules\Permission\Actions;

use Modules\Permission\Models\Permission;

class UpdatePermission
{
    public function execute(Permission $permission, array $data): Permission
    {
        $permission->update(['name' => $data['name']]);

        return $permission;
    }
}
