<?php

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\User\Models\User;

class UserSeeder extends Seeder
{
    private const DEMO_USER_COUNT = 1000;

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

        foreach (range(1, self::DEMO_USER_COUNT) as $number) {
            $demoUser = User::withTrashed()->firstOrCreate(
                ['email' => sprintf('demo%03d@example.com', $number)],
                User::factory()->make([
                    'name' => sprintf('Demo User %03d', $number),
                    'email' => sprintf('demo%03d@example.com', $number),
                ])->getAttributes(),
            );

            if ($demoUser->trashed()) {
                $demoUser->restore();
            }

            $demoUser->syncRoles('user');
        }
    }
}
