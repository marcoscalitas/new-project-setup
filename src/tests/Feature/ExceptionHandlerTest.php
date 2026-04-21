<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Client;
use Modules\Permission\Models\Permission;
use Modules\User\Models\User;
use Tests\TestCase;

class ExceptionHandlerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        if (!file_exists(storage_path('oauth-private.key'))) {
            $this->artisan('passport:keys', ['--force' => true]);
        }

        Client::create([
            'name'          => 'Test Personal Client',
            'secret'        => null,
            'redirect_uris' => [],
            'grant_types'   => ['personal_access'],
            'provider'      => 'users',
            'revoked'       => false,
        ]);

        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test')->accessToken;
    }

    private function authHeaders(): array
    {
        return ['Authorization' => 'Bearer ' . $this->token];
    }

    // == 401 Unauthenticated ==

    public function test_unauthenticated_api_request_returns_401_json(): void
    {
        $response = $this->getJson('/api/v1/users');

        $response->assertUnauthorized()
            ->assertJsonStructure(['message'])
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    // == 403 Unauthorized ==

    public function test_api_request_without_permission_returns_403_json(): void
    {
        // Permission exists but user does not have it
        Permission::firstOrCreate(['name' => 'user.list', 'guard_name' => 'api']);

        $response = $this->getJson('/api/v1/users', $this->authHeaders());

        $response->assertForbidden()
            ->assertJsonStructure(['message'])
            ->assertJson(['message' => 'This action is unauthorized.']);
    }

    // == 404 Not Found ==

    public function test_api_request_to_unknown_route_returns_404_json(): void
    {
        $response = $this->getJson('/api/v1/this-route-does-not-exist');

        $response->assertNotFound()
            ->assertJsonStructure(['message']);
    }

    public function test_api_request_for_missing_model_returns_404_json(): void
    {
        $perm = Permission::firstOrCreate(['name' => 'user.view', 'guard_name' => 'api']);
        $this->user->givePermissionTo($perm);

        $response = $this->getJson('/api/v1/users/999999', $this->authHeaders());

        $response->assertNotFound()
            ->assertJsonStructure(['message']);
    }

    // == 405 Method Not Allowed ==

    public function test_api_request_with_wrong_method_returns_405_json(): void
    {
        $response = $this->patchJson('/api/v1/users');

        $response->assertStatus(405)
            ->assertJsonStructure(['message']);
    }

    // == Web 403 redirects back ==

    public function test_web_request_without_permission_redirects_back_with_error(): void
    {
        // Permission exists but user does not have it
        Permission::firstOrCreate(['name' => 'user.list', 'guard_name' => 'web']);

        $response = $this->actingAs($this->user)->get('/users');

        $response->assertRedirect()
            ->assertSessionHas('error');
    }
}
