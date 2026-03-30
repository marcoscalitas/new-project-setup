<?php

namespace Modules\Permission\Actions;

use Modules\Permission\Models\Role;

class UpdateRole
{
    public function execute(Role $role, array $data): Role
    {
        $role->update(['name' => $data['name']]);

        if (array_key_exists('permissions', $data)) {
            $role->syncPermissions($data['permissions'] ?? []);
        }

        return $role->load('permissions');
    }
}
