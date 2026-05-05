<?php

namespace Modules\Authentication\Tests\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Modules\User\Models\User;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_logout_via_api(): void
    {
        $user = User::factory()->create();

        Passport::actingAs($user);

        $response = $this->postJson('/api/v1/auth/logout');

        $response->assertOk()
            ->assertJson(['message' => 'Sessão encerrada com sucesso.']);
    }

    public function test_unauthenticated_user_cannot_logout_via_api(): void
    {
        $response = $this->postJson('/api/v1/auth/logout');

        $response->assertUnauthorized();
    }
}
