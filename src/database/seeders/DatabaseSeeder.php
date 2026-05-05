<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Modules\Authorization\Database\Seeders\PermissionSeeder;
use Modules\Authorization\Database\Seeders\RoleSeeder;
use Modules\Settings\Database\Seeders\SettingsSeeder;
use Modules\User\Database\Seeders\UserSeeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            SettingsSeeder::class,
        ]);

        if (app()->environment(['local', 'testing'])) {
            $this->call(UserSeeder::class);
        }
    }
}
