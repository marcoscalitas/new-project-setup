<?php

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\User\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::withTrashed()->firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin', 'password' => bcrypt('password')]
        );
        if ($admin->trashed()) {
            $admin->restore();
        }
        $admin->syncRoles('admin');

        $user = User::withTrashed()->firstOrCreate(
            ['email' => 'user@example.com'],
            ['name' => 'User', 'password' => bcrypt('password')]
        );
        if ($user->trashed()) {
            $user->restore();
        }
        $user->syncRoles('user');
    }
}
