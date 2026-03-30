<?php

namespace Modules\Permission\Services;

use Modules\Permission\Actions\CreateRole;
use Modules\Permission\Actions\DeleteRole;
use Modules\Permission\Actions\UpdateRole;
use Modules\Permission\Models\Role;

class RoleService
{
    public function __construct(
        private CreateRole $createRole,
        private UpdateRole $updateRole,
        private DeleteRole $deleteRole,
    ) {}

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
        return $this->createRole->execute($data);
    }

    public function update(int $id, array $data): Role
    {
        $role = Role::findOrFail($id);

        return $this->updateRole->execute($role, $data);
    }

    public function delete(int $id): void
    {
        $role = Role::findOrFail($id);

        $this->deleteRole->execute($role);
    }
}
