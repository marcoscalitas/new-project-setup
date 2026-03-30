<?php

namespace Modules\Permission\Services;

use Modules\Permission\Actions\CreatePermission;
use Modules\Permission\Actions\DeletePermission;
use Modules\Permission\Actions\UpdatePermission;
use Modules\Permission\Events\PermissionCreated;
use Modules\Permission\Events\PermissionDeleted;
use Modules\Permission\Events\PermissionUpdated;
use Modules\Permission\Models\Permission;

class PermissionService
{
    public function __construct(
        private CreatePermission $createPermission,
        private UpdatePermission $updatePermission,
        private DeletePermission $deletePermission,
    ) {}

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
        $permission = $this->createPermission->execute($data);

        PermissionCreated::dispatch($permission);

        return $permission;
    }

    public function update(int $id, array $data): Permission
    {
        $permission = Permission::findOrFail($id);
        $oldName = $permission->name;

        $permission = $this->updatePermission->execute($permission, $data);

        PermissionUpdated::dispatch($permission->name, $oldName);

        return $permission;
    }

    public function delete(int $id): void
    {
        $permission = Permission::findOrFail($id);
        $permissionId = $permission->id;
        $permissionName = $permission->name;

        $this->deletePermission->execute($permission);

        PermissionDeleted::dispatch($permissionId, $permissionName);
    }
}
