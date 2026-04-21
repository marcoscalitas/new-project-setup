<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Permission\Models\Role;
use Modules\User\Models\User;
use Tests\TestCase;

class HorizonTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'web']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole($adminRole);

        $this->user = User::factory()->create();
    }

    public function test_admin_can_access_horizon_dashboard(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/horizon/dashboard');

        $response->assertOk();
    }

    public function test_non_admin_cannot_access_horizon_dashboard(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/horizon/dashboard');

        $response->assertForbidden();
    }

    public function test_unauthenticated_user_cannot_access_horizon_dashboard(): void
    {
        $response = $this->get('/horizon/dashboard');

        $response->assertForbidden();
    }

    public function test_admin_can_access_horizon_api_stats(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/horizon/api/stats');

        $response->assertOk();
    }

    public function test_non_admin_cannot_access_horizon_api(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/horizon/api/stats');

        $response->assertForbidden();
    }
}
