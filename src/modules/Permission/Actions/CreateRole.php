<?php

namespace Modules\Permission\Actions;

use Modules\Permission\Models\Role;

class CreateRole
{
    public function execute(array $data): Role
    {
        $role = Role::create([
            'name'       => $data['name'],
            'guard_name' => 'api',
        ]);

        if (!empty($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        return $role->load('permissions');
    }
}
