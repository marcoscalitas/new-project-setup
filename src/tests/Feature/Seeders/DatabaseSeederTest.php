<?php

namespace Tests\Feature\Seeders;

use Database\Seeders\DatabaseSeeder;
use Modules\Authorization\Models\Permission;
use Modules\Authorization\Models\Role;
use Modules\Settings\Models\Setting;
use Modules\User\Models\User;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    public function test_database_seeder_creates_base_authorization_data(): void
    {
        $this->seed(DatabaseSeeder::class);

        foreach (['api', 'web'] as $guard) {
            $this->assertDatabaseHas('permissions', [
                'name' => 'audit-log.list',
                'guard_name' => $guard,
            ]);
            $this->assertDatabaseHas('permissions', [
                'name' => 'audit-log.view',
                'guard_name' => $guard,
            ]);
            $this->assertDatabaseHas('roles', [
                'name' => 'admin',
                'guard_name' => $guard,
            ]);
            $this->assertDatabaseHas('roles', [
                'name' => 'user',
                'guard_name' => $guard,
            ]);
        }

        $this->assertFalse(Permission::whereIn('name', [
            'log.create',
            'log.update',
            'log.delete',
        ])->exists());
    }

    public function test_database_seeder_creates_base_settings(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseHas('settings', ['key' => 'app.name']);
        $this->assertDatabaseHas('settings', ['key' => 'app.timezone_default']);

        $this->assertSame(config('app.name'), Setting::find('app.name')->value);
        $this->assertSame(config('app.timezone'), Setting::find('app.timezone_default')->value);
    }

    public function test_database_seeder_creates_development_users_in_testing(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $user = User::where('email', 'user@example.com')->firstOrFail();

        $this->assertTrue($admin->hasRole('admin'));
        $this->assertTrue($user->hasRole('user'));
    }

    public function test_database_seeder_creates_demo_users_from_factory_in_testing(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(298, User::where('email', 'like', 'demo%@example.com')->count());

        $firstDemoUser = User::where('email', 'demo001@example.com')->firstOrFail();

        $this->assertSame('Demo User 001', $firstDemoUser->name);
        $this->assertTrue($firstDemoUser->hasRole('user'));
        $this->assertNotNull($firstDemoUser->email_verified_at);
    }

    public function test_database_seeder_does_not_duplicate_demo_users(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(298, User::where('email', 'like', 'demo%@example.com')->count());
    }

    public function test_admin_role_receives_all_seeded_permissions(): void
    {
        $this->seed(DatabaseSeeder::class);

        foreach (['api', 'web'] as $guard) {
            $admin = Role::where('name', 'admin')->where('guard_name', $guard)->firstOrFail();

            $this->assertSame(
                Permission::where('guard_name', $guard)->count(),
                $admin->permissions()->where('guard_name', $guard)->count(),
            );
        }
    }
}
