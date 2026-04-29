<?php

namespace Modules\Permission\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            'user'       => ['list', 'view', 'create', 'update', 'delete'],
            'role'       => ['list', 'view', 'create', 'update', 'delete'],
            'permission' => ['list', 'view', 'create', 'update', 'delete'],
            'log'        => ['list', 'view'],
            'media'      => ['list', 'view', 'create', 'update', 'delete'],
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
