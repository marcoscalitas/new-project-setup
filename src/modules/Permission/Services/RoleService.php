<?php

namespace Modules\Permission\Services;

use Illuminate\Validation\ValidationException;
use Modules\Permission\Events\RoleCreated;
use Modules\Permission\Events\RoleDeleted;
use Modules\Permission\Events\RoleUpdated;
use Modules\Permission\Models\Role;

class RoleService
{
    private function resolveGuardName(): string
    {
        return auth('api')->check() ? 'api' : 'web';
    }

    public function getAll(int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Role::with('permissions')->paginate($perPage);
    }

    public function findById(int $id): Role
    {
        return Role::with('permissions')->findOrFail($id);
    }

    public function create(array $data): Role
    {
        $role = Role::create(['name' => $data['name'], 'guard_name' => $this->resolveGuardName()]);

        if (!empty($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        RoleCreated::dispatch($role);

        return $role->load('permissions');
    }

    public function update(int $id, array $data): Role
    {
        $role = Role::findOrFail($id);
        $oldName = $role->name;
        $role->update(['name' => $data['name']]);

        if (array_key_exists('permissions', $data)) {
            $role->syncPermissions($data['permissions'] ?? []);
        }

        RoleUpdated::dispatch($role->name, $oldName);

        return $role->load('permissions');
    }

    public function delete(int $id): void
    {
        $role = Role::findOrFail($id);

        if ($role->name === 'admin') {
            throw ValidationException::withMessages([
                'role' => __('permissions.cannot_delete_admin_role'),
            ]);
        }

        $roleId = $role->id;
        $roleName = $role->name;

        $role->delete();

        RoleDeleted::dispatch($roleId, $roleName);
    }
}
