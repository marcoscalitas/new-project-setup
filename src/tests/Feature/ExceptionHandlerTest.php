<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Laravel\Passport\Client;
use Modules\Authorization\Models\Permission;
use Modules\User\Models\User;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
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

    // == 401 ==

    public function test_unauthenticated_api_request_returns_401_json(): void
    {
        $this->getJson('/api/v1/users')
            ->assertUnauthorized()
            ->assertExactJson(['message' => 'Unauthenticated.']);
    }

    // == 403 ==

    public function test_api_request_without_permission_returns_403_json(): void
    {
        Permission::firstOrCreate(['name' => 'user.list', 'guard_name' => 'api']);

        $this->getJson('/api/v1/users', $this->authHeaders())
            ->assertForbidden()
            ->assertJsonStructure(['message'])
            ->assertJson(['message' => 'Forbidden.']);
    }

    public function test_web_request_without_permission_redirects_back_with_error(): void
    {
        Permission::firstOrCreate(['name' => 'user.list', 'guard_name' => 'web']);

        $this->actingAs($this->user)->get('/users')
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    // == 404 ==

    public function test_api_request_to_unknown_route_returns_404_json(): void
    {
        $this->getJson('/api/v1/this-route-does-not-exist')
            ->assertNotFound()
            ->assertJsonStructure(['message']);
    }

    public function test_api_request_for_missing_model_returns_404_json(): void
    {
        $perm = Permission::firstOrCreate(['name' => 'user.view', 'guard_name' => 'api']);
        $this->user->givePermissionTo($perm);

        $this->getJson('/api/v1/users/999999', $this->authHeaders())
            ->assertNotFound()
            ->assertJsonStructure(['message']);
    }

    // == 405 ==

    public function test_api_request_with_wrong_method_returns_405_json(): void
    {
        $this->patchJson('/api/v1/users')
            ->assertStatus(405)
            ->assertJsonStructure(['message']);
    }

    // == 422 ==

    public function test_api_validation_failure_returns_422_with_errors(): void
    {
        Route::middleware('api')->post('/__test_validation', function () {
            throw ValidationException::withMessages(['field' => 'The field is required.']);
        });

        $this->postJson('/__test_validation')
            ->assertUnprocessable()
            ->assertJsonStructure(['message', 'errors'])
            ->assertJsonPath('errors.field.0', 'The field is required.');
    }

    // == 429 ==

    public function test_api_rate_limit_returns_429_json(): void
    {
        Route::middleware('api')->get('/__test_throttle', function () {
            throw new TooManyRequestsHttpException();
        });

        $this->getJson('/__test_throttle')
            ->assertStatus(429)
            ->assertExactJson(['message' => 'Too many requests.']);
    }

    // == 500 ==

    public function test_api_server_error_returns_500_json_in_production(): void
    {
        Route::middleware('api')->get('/__test_500', function () {
            throw new \RuntimeException('Boom');
        });

        config(['app.debug' => false]);

        $this->getJson('/__test_500')
            ->assertStatus(500)
            ->assertExactJson(['message' => 'Server error.']);
    }

    public function test_api_server_error_exposes_exception_in_debug_mode(): void
    {
        Route::middleware('api')->get('/__test_500_debug', function () {
            throw new \RuntimeException('Debug boom');
        });

        config(['app.debug' => true]);

        // In debug mode the handler does NOT intercept — Laravel renders the full exception
        $this->getJson('/__test_500_debug')
            ->assertStatus(500);
    }
}
