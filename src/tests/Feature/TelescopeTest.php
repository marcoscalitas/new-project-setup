<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\User\Models\User;
use Tests\TestCase;

class TelescopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_telescope_routes_are_not_registered_outside_local_environment(): void
    {
        // APP_ENV=testing — AppServiceProvider só regista Telescope em 'local'
        // portanto as rotas não existem e devolvem 404
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/telescope/requests');

        $response->assertNotFound();
    }

    public function test_telescope_api_is_not_accessible_outside_local_environment(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson('/telescope/telescope-api/requests');

        $response->assertNotFound();
    }
}
