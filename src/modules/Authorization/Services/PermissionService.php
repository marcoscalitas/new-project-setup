<?php

namespace Modules\Authorization\Services;

use Modules\Authorization\Events\PermissionCreated;
use Modules\Authorization\Events\PermissionDeleted;
use Modules\Authorization\Events\PermissionUpdated;
use Modules\Authorization\Models\Permission;

class PermissionService
{
    private function resolveGuardName(): string
    {
        return auth('api')->check() ? 'api' : 'web';
    }

    public function getAll(?int $perPage = 15, ?string $search = null, string $sort = 'name', string $direction = 'asc')
    {
        $allowed   = ['name', 'guard_name', 'created_at'];
        $sort      = in_array($sort, $allowed) ? $sort : 'name';
        $direction = $direction === 'desc' ? 'desc' : 'asc';

        $query = Permission::query()
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy($sort, $direction);

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

    public function getTrashed(int $perPage = 15, ?string $search = null)
    {
        return Permission::onlyTrashed()
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->paginate($perPage);
    }

    public function restore(string $ulid): Permission
    {
        $permission = Permission::withTrashed()->where('ulid', $ulid)->firstOrFail();
        $permission->restore();
        return $permission;
    }
}
