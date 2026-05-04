<?php

namespace Modules\Identity\Tests\Web;

use App\Contracts\MailSenderInterface;
use App\Mail\MailMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Password;
use Mockery\MockInterface;
use Modules\User\Models\User;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_request_password_reset_link(): void
    {
        $user = User::factory()->create();

        $this->mock(MailSenderInterface::class, function (MockInterface $mock) use ($user) {
            $mock->shouldReceive('queue')
                ->once()
                ->withArgs(fn(MailMessage $msg) =>
                    $msg->to === $user->email &&
                    $msg->view === 'auth::emails.password-reset'
                );
        });

        $response = $this->postJson('/auth/forgot-password', [
            'email' => $user->email,
        ]);

        $response->assertOk();
    }

    public function test_forgot_password_requires_email(): void
    {
        $response = $this->postJson('/auth/forgot-password', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_user_can_reset_password_with_valid_token(): void
    {
        $user  = User::factory()->create();
        $token = Password::createToken($user);

        $response = $this->postJson('/auth/reset-password', [
            'token'                 => $token,
            'email'                 => $user->email,
            'password'              => 'NewSecurePass1!',
            'password_confirmation' => 'NewSecurePass1!',
        ]);

        $response->assertOk();
    }

    public function test_reset_password_fails_with_invalid_token(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/auth/reset-password', [
            'token'                 => 'invalid-token',
            'email'                 => $user->email,
            'password'              => 'NewSecurePass1!',
            'password_confirmation' => 'NewSecurePass1!',
        ]);

        $response->assertUnprocessable();
    }

    public function test_reset_password_requires_all_fields(): void
    {
        $response = $this->postJson('/auth/reset-password', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['token', 'email', 'password']);
    }

    public function test_reset_password_requires_password_confirmation(): void
    {
        $user  = User::factory()->create();
        $token = Password::createToken($user);

        $response = $this->postJson('/auth/reset-password', [
            'token'    => $token,
            'email'    => $user->email,
            'password' => 'NewSecurePass1!',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }
}
