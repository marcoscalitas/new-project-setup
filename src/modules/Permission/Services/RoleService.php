<?php

namespace Modules\Permission\Services;

use Modules\Permission\Models\Role;

class RoleService
{
    public function getAll()
    {
        return Role::with('permissions')->get();
    }

    public function findById(int $id): Role
    {
        return Role::with('permissions')->findOrFail($id);
    }

    public function create(array $data): Role
    {
        $role = Role::create(['name' => $data['name'], 'guard_name' => 'api']);

        if (!empty($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        return $role->load('permissions');
    }

    public function update(int $id, array $data): Role
    {
        $role = Role::findOrFail($id);
        $role->update(['name' => $data['name']]);

        if (array_key_exists('permissions', $data)) {
            $role->syncPermissions($data['permissions'] ?? []);
        }

        return $role->load('permissions');
    }

    public function delete(int $id): void
    {
        Role::findOrFail($id)->delete();
    }
}
