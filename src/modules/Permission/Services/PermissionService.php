<?php

namespace Modules\Permission\Services;

use Modules\Permission\Events\PermissionCreated;
use Modules\Permission\Events\PermissionDeleted;
use Modules\Permission\Events\PermissionUpdated;
use Modules\Permission\Models\Permission;

class PermissionService
{
    private function resolveGuardName(): string
    {
        return auth('api')->check() ? 'api' : 'web';
    }

    public function getAll(?int $perPage = 15)
    {
        $query = Permission::query();
        return $perPage === null ? $query->get() : $query->paginate($perPage);
    }

    public function findById(int $id): Permission
    {
        return Permission::findOrFail($id);
    }

    public function create(array $data): Permission
    {
        $permission = Permission::create(['name' => $data['name'], 'guard_name' => $this->resolveGuardName()]);

        PermissionCreated::dispatch($permission);

        return $permission;
    }

    public function update(int $id, array $data): Permission
    {
        $permission = Permission::findOrFail($id);
        $oldName = $permission->name;

        $permission->update(['name' => $data['name']]);

        PermissionUpdated::dispatch($permission->name, $oldName);

        return $permission;
    }

    public function delete(int $id): void
    {
        $permission = Permission::findOrFail($id);
        $permissionId = $permission->id;
        $permissionName = $permission->name;

        $permission->delete();

        PermissionDeleted::dispatch($permissionId, $permissionName);
    }
}
