<?php

namespace Modules\Authorization\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Authorization\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            'user'       => ['list', 'view', 'create', 'update', 'delete'],
            'role'       => ['list', 'view', 'create', 'update', 'delete'],
            'permission' => ['list', 'view', 'create', 'update', 'delete'],
            'log'        => ['list', 'view'],
            'setting'    => ['list', 'view', 'create', 'update', 'delete'],
        ];

        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                foreach (['api', 'web'] as $guard) {
                    $permission = Permission::withTrashed()->firstOrCreate([
                        'name'       => "{$module}.{$action}",
                        'guard_name' => $guard,
                    ]);

                    if ($permission->trashed()) {
                        $permission->restore();
                    }
                }
            }
        }
    }
}
