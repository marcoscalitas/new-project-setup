<?php

namespace Modules\Permission\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Permission\Models\Permission;
use Modules\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['api', 'web'] as $guard) {
            // Admin — todas as permissions
            $admin = Role::firstOrCreate([
                'name'       => 'admin',
                'guard_name' => $guard,
            ]);
            $admin->syncPermissions(
                Permission::where('guard_name', $guard)->get()
            );

            // User — apenas visualização
            $user = Role::firstOrCreate([
                'name'       => 'user',
                'guard_name' => $guard,
            ]);
            $user->syncPermissions(
                Permission::where('guard_name', $guard)
                    ->whereIn('name', ['user.list', 'user.view'])
                    ->get()
            );
        }
    }
}
