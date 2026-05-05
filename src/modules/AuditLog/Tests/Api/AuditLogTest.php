<?php

namespace Modules\AuditLog\Tests\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Client;
use Modules\AuditLog\Models\AuditLog;
use Modules\Authorization\Models\Permission;
use Modules\User\Models\User;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private string $adminToken;

    private User $regularUser;

    private string $userToken;

    protected function setUp(): void
    {
        parent::setUp();

        if (! file_exists(storage_path('oauth-private.key'))) {
            $this->artisan('passport:keys', ['--force' => true]);
        }

        Client::create([
            'name' => 'Test Personal Client',
            'secret' => null,
            'redirect_uris' => [],
            'grant_types' => ['personal_access'],
            'provider' => 'users',
            'revoked' => false,
        ]);

        $this->admin = User::factory()->create();
        $this->adminToken = $this->admin->createToken('test')->accessToken;

        $listPerm = Permission::firstOrCreate(['name' => 'audit-log.list', 'guard_name' => 'api']);
        $viewPerm = Permission::firstOrCreate(['name' => 'audit-log.view', 'guard_name' => 'api']);
        $this->admin->givePermissionTo([$listPerm, $viewPerm]);

        $this->regularUser = User::factory()->create();
        $this->userToken = $this->regularUser->createToken('test')->accessToken;
    }

    private function adminHeaders(): array
    {
        return ['Authorization' => 'Bearer '.$this->adminToken];
    }

    private function userHeaders(): array
    {
        return ['Authorization' => 'Bearer '.$this->userToken];
    }

    private function createAuditLog(User $causer, array $overrides = []): AuditLog
    {
        return activity()
            ->causedBy($causer)
            ->performedOn($causer)
            ->log($overrides['description'] ?? 'updated');
    }

    // == LIST ==

    public function test_admin_can_list_audit_log(): void
    {
        AuditLog::truncate();

        $this->createAuditLog($this->admin);
        $this->createAuditLog($this->regularUser);

        $response = $this->getJson('/api/v1/audit-logs', $this->adminHeaders());

        $response->assertOk()
            ->assertJsonStructure(['data', 'links', 'meta'])
            ->assertJsonCount(2, 'data');
    }

    public function test_unauthenticated_user_cannot_list_audit_log(): void
    {
        $response = $this->getJson('/api/v1/audit-logs');

        $response->assertUnauthorized();
    }

    public function test_unauthenticated_user_cannot_view_audit_log_detail(): void
    {
        $activity = $this->createAuditLog($this->admin);
        $this->getJson("/api/v1/audit-logs/{$activity->id}")->assertUnauthorized();
    }

    public function test_user_without_permission_cannot_list_audit_log(): void
    {
        $response = $this->getJson('/api/v1/audit-logs', $this->userHeaders());

        $response->assertForbidden();
    }

    public function test_list_can_be_filtered_by_causer(): void
    {
        $this->createAuditLog($this->admin);
        $this->createAuditLog($this->regularUser);

        $response = $this->getJson(
            '/api/v1/audit-logs?causer_id='.$this->admin->id,
            $this->adminHeaders()
        );

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    // == SHOW ==

    public function test_admin_can_view_audit_log_detail(): void
    {
        $activity = $this->createAuditLog($this->admin);

        $response = $this->getJson("/api/v1/audit-logs/{$activity->id}", $this->adminHeaders());

        $response->assertOk()
            ->assertJsonStructure(['id', 'description', 'causer', 'properties', 'created_at']);
    }

    public function test_user_can_view_own_audit_log_detail(): void
    {
        $activity = $this->createAuditLog($this->regularUser);

        $response = $this->getJson("/api/v1/audit-logs/{$activity->id}", $this->userHeaders());

        $response->assertOk();
    }

    public function test_user_cannot_view_another_users_audit_log_detail(): void
    {
        $activity = $this->createAuditLog($this->admin);

        $response = $this->getJson("/api/v1/audit-logs/{$activity->id}", $this->userHeaders());

        $response->assertForbidden();
    }

    public function test_show_returns_404_for_nonexistent_audit_log(): void
    {
        $response = $this->getJson('/api/v1/audit-logs/99999', $this->adminHeaders());

        $response->assertNotFound();
    }

    // == USER AUDIT LOG ==

    public function test_user_can_view_own_audit_log(): void
    {
        $this->createAuditLog($this->regularUser);

        $response = $this->getJson(
            "/api/v1/audit-logs/users/{$this->regularUser->ulid}",
            $this->userHeaders()
        );

        $response->assertOk()
            ->assertJsonStructure(['data', 'links', 'meta'])
            ->assertJsonCount(1, 'data');
    }

    public function test_admin_can_view_any_users_audit_log(): void
    {
        $this->createAuditLog($this->regularUser);

        $response = $this->getJson(
            "/api/v1/audit-logs/users/{$this->regularUser->ulid}",
            $this->adminHeaders()
        );

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_user_cannot_view_another_users_audit_log(): void
    {
        $other = User::factory()->create();

        $response = $this->getJson(
            "/api/v1/audit-logs/users/{$other->ulid}",
            $this->userHeaders()
        );

        $response->assertForbidden();
    }

    public function test_unauthenticated_user_cannot_view_user_audit_log(): void
    {
        $response = $this->getJson("/api/v1/audit-logs/users/{$this->regularUser->ulid}");

        $response->assertUnauthorized();
    }

    public function test_audit_log_is_logged_when_user_is_updated(): void
    {
        $this->admin->update(['name' => 'Updated Name']);

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => User::class,
            'subject_id' => $this->admin->id,
            'description' => 'updated',
        ]);
    }

    public function test_sensitive_fields_are_not_logged(): void
    {
        $this->admin->update(['password' => bcrypt('newpassword')]);

        $activities = AuditLog::where('subject_id', $this->admin->id)->get();

        foreach ($activities as $activity) {
            $this->assertArrayNotHasKey('password', $activity->properties->get('attributes', []));
            $this->assertArrayNotHasKey('password', $activity->properties->get('old', []));
        }
    }
}
