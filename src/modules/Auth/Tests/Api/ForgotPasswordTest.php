<?php

namespace Modules\Auth\Tests\Api;

use App\Contracts\MailSenderInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\User\Models\User;
use Tests\TestCase;

class ForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_request_password_reset_via_api(): void
    {
        $this->mock(MailSenderInterface::class)
            ->shouldReceive('queue')->once();

        $user = User::factory()->create();

        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => $user->email,
        ]);

        $response->assertOk()
            ->assertJson(['message' => 'Link de recuperação enviado.']);
    }

    public function test_forgot_password_always_returns_200_for_unknown_email(): void
    {
        $this->mock(MailSenderInterface::class)
            ->shouldReceive('queue')->never();

        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'unknown@example.com',
        ]);

        // OWASP: always 200 to prevent email enumeration
        $response->assertOk()
            ->assertJson(['message' => 'Link de recuperação enviado.']);
    }

    public function test_forgot_password_requires_email(): void
    {
        $response = $this->postJson('/api/v1/auth/forgot-password', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_forgot_password_requires_valid_email_format(): void
    {
        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'not-an-email',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }
}
