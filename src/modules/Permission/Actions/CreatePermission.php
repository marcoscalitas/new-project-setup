<?php

namespace Modules\Permission\Actions;

use Modules\Permission\Models\Permission;

class CreatePermission
{
    public function execute(array $data): Permission
    {
        return Permission::create([
            'name'       => $data['name'],
            'guard_name' => 'api',
        ]);
    }
}
