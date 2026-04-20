<?php

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\User\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name'  => 'Admin',
            'email' => 'admin@example.com',
        ])->assignRole('admin');

        User::factory()->create([
            'name'  => 'User',
            'email' => 'user@example.com',
        ])->assignRole('user');
    }
}
