<?php

namespace Modules\Auth\Tests\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Modules\User\Models\User;
use Tests\TestCase;

class ResetPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_reset_password_via_api(): void
    {
        $user  = User::factory()->create();
        $token = Password::createToken($user);

        $response = $this->postJson('/api/auth/reset-password', [
            'token'                 => $token,
            'email'                 => $user->email,
            'password'              => 'NewSecurePass1!',
            'password_confirmation' => 'NewSecurePass1!',
        ]);

        $response->assertOk()
            ->assertJson(['message' => 'Senha redefinida com sucesso.']);

        $this->assertTrue(Hash::check('NewSecurePass1!', $user->fresh()->password));
    }

    public function test_reset_password_fails_with_invalid_token(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/auth/reset-password', [
            'token'                 => 'invalid-token',
            'email'                 => $user->email,
            'password'              => 'NewSecurePass1!',
            'password_confirmation' => 'NewSecurePass1!',
        ]);

        $response->assertStatus(400)
            ->assertJson(['message' => 'Token inválido ou expirado.']);
    }

    public function test_reset_password_requires_token(): void
    {
        $response = $this->postJson('/api/auth/reset-password', [
            'email'                 => 'user@example.com',
            'password'              => 'NewSecurePass1!',
            'password_confirmation' => 'NewSecurePass1!',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['token']);
    }

    public function test_reset_password_requires_email(): void
    {
        $response = $this->postJson('/api/auth/reset-password', [
            'token'                 => 'some-token',
            'password'              => 'NewSecurePass1!',
            'password_confirmation' => 'NewSecurePass1!',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_reset_password_requires_password(): void
    {
        $response = $this->postJson('/api/auth/reset-password', [
            'token' => 'some-token',
            'email' => 'user@example.com',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    public function test_reset_password_requires_password_confirmation(): void
    {
        $user  = User::factory()->create();
        $token = Password::createToken($user);

        $response = $this->postJson('/api/auth/reset-password', [
            'token'    => $token,
            'email'    => $user->email,
            'password' => 'NewSecurePass1!',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    public function test_reset_password_rejects_short_password(): void
    {
        $user  = User::factory()->create();
        $token = Password::createToken($user);

        $response = $this->postJson('/api/auth/reset-password', [
            'token'                 => $token,
            'email'                 => $user->email,
            'password'              => 'abc',
            'password_confirmation' => 'abc',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }
}
