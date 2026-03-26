<?php

namespace Modules\Permission\Services;

use Modules\Permission\Models\Permission;

class PermissionService
{
    public function getAll()
    {
        return Permission::all();
    }

    public function findById(int $id): Permission
    {
        return Permission::findOrFail($id);
    }

    public function create(array $data): Permission
    {
        return Permission::create(['name' => $data['name'], 'guard_name' => 'api']);
    }

    public function update(int $id, array $data): Permission
    {
        $permission = Permission::findOrFail($id);
        $permission->update(['name' => $data['name']]);

        return $permission;
    }

    public function delete(int $id): void
    {
        Permission::findOrFail($id)->delete();
    }
}
