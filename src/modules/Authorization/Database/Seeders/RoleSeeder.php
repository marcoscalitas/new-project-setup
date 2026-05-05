<?php

namespace Modules\Authorization\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Authorization\Models\Permission;
use Modules\Authorization\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['api', 'web'] as $guard) {
            // Admin — all permissions
            $admin = Role::withTrashed()->firstOrCreate([
                'name' => 'admin',
                'guard_name' => $guard,
            ]);
            if ($admin->trashed()) {
                $admin->restore();
            }
            $admin->syncPermissions(
                Permission::where('guard_name', $guard)->get()
            );

            // User — read-only access to own resources and shared read permissions
            $user = Role::withTrashed()->firstOrCreate([
                'name' => 'user',
                'guard_name' => $guard,
            ]);
            if ($user->trashed()) {
                $user->restore();
            }
            $user->syncPermissions(
                Permission::where('guard_name', $guard)
                    ->whereIn('name', [
                        'user.list',
                        'user.view',
                        'audit-log.list',
                        'audit-log.view',
                        'setting.list',
                        'setting.view',
                    ])
                    ->get()
            );
        }
    }
}
