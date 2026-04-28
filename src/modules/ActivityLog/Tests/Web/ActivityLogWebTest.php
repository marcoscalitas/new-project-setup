<?php

namespace Modules\ActivityLog\Tests\Web;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Permission\Models\Permission;
use Modules\User\Models\User;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class ActivityLogWebTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();

        $perms = [];
        foreach (['log.list', 'log.view'] as $name) {
            $perms[] = Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
        $this->admin->givePermissionTo($perms);
    }

    public function test_authenticated_admin_can_view_activity_log_index(): void
    {
        activity()->log('Test log entry');

        $response = $this->actingAs($this->admin)->get('/activity-log');

        $response->assertOk()
            ->assertViewIs('activitylog::activity-log.index')
            ->assertSee('Test log entry');
    }

    public function test_unauthenticated_user_is_redirected_from_activity_log(): void
    {
        $response = $this->get('/activity-log');

        $response->assertRedirect();
    }

    public function test_user_without_permission_is_redirected_from_activity_log(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/activity-log');

        $response->assertRedirect();
    }

    public function test_authenticated_admin_can_view_activity_log_detail(): void
    {
        activity()->log('Detail entry');
        $log = Activity::latest()->first();

        $response = $this->actingAs($this->admin)->get('/activity-log/' . $log->id);

        $response->assertOk()
            ->assertViewIs('activitylog::activity-log.show');
    }

    public function test_activity_log_index_filters_by_log_name(): void
    {
        activity('auth')->log('Login event');
        activity('user')->log('User event');

        $response = $this->actingAs($this->admin)->get('/activity-log?log_name=auth');

        $response->assertOk()
            ->assertSee('Login event')
            ->assertDontSee('User event');
    }
}
