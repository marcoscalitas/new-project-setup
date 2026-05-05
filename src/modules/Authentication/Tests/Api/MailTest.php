<?php

namespace Modules\Authentication\Tests\Api;

use App\Contracts\MailSenderInterface;
use App\Mail\MailMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Client;
use Mockery\MockInterface;
use Modules\User\Events\UserCreated;
use Modules\Authentication\Listeners\SendWelcomeEmail;
use Modules\User\Models\User;
use Tests\TestCase;

class MailTest extends TestCase
{
    use RefreshDatabase;

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
    }

    public function test_welcome_email_is_queued_on_user_created_event(): void
    {
        $this->mock(MailSenderInterface::class, function (MockInterface $mock) {
            $mock->shouldReceive('queue')
                ->once()
                ->withArgs(fn(MailMessage $msg) =>
                    $msg->to === 'john@example.com' &&
                    $msg->subject === 'Welcome to ' . config('app.name') &&
                    $msg->view === 'auth::emails.welcome'
                );
        });

        (new SendWelcomeEmail(app(MailSenderInterface::class)))->handle(
            new UserCreated('01FAKE', 'John Doe', 'john@example.com')
        );
    }

    public function test_welcome_email_contains_correct_user_data(): void
    {
        $this->mock(MailSenderInterface::class, function (MockInterface $mock) {
            $mock->shouldReceive('queue')
                ->once()
                ->withArgs(fn(MailMessage $msg) =>
                    $msg->to === 'maria@example.com' &&
                    $msg->data['user']->name === 'Maria Silva'
                );
        });

        (new SendWelcomeEmail(app(MailSenderInterface::class)))->handle(
            new UserCreated('01FAKE', 'Maria Silva', 'maria@example.com')
        );
    }

    public function test_password_reset_email_is_queued_on_forgot_password(): void
    {
        $user = User::factory()->create();

        $this->mock(MailSenderInterface::class, function (MockInterface $mock) use ($user) {
            $mock->shouldReceive('queue')
                ->once()
                ->withArgs(fn(MailMessage $msg) =>
                    $msg->to === $user->email &&
                    $msg->subject === 'Reset Your Password' &&
                    $msg->view === 'auth::emails.password-reset'
                );
        });

        $this->postJson('/api/v1/auth/forgot-password', [
            'email' => $user->email,
        ])->assertOk();
    }

    public function test_password_reset_email_contains_reset_url(): void
    {
        $user = User::factory()->create();

        $this->mock(MailSenderInterface::class, function (MockInterface $mock) use ($user) {
            $mock->shouldReceive('queue')
                ->once()
                ->withArgs(fn(MailMessage $msg) =>
                    isset($msg->data['resetUrl']) &&
                    str_contains($msg->data['resetUrl'], 'reset-password') &&
                    str_contains($msg->data['resetUrl'], rawurlencode($user->email)) &&
                    isset($msg->data['user']) &&
                    $msg->data['user']->is($user)
                );
        });

        $this->postJson('/api/v1/auth/forgot-password', [
            'email' => $user->email,
        ])->assertOk();
    }

    public function test_password_reset_email_not_queued_for_unknown_email(): void
    {
        $this->mock(MailSenderInterface::class, function (MockInterface $mock) {
            $mock->shouldReceive('queue')->never();
        });

        $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'nobody@example.com',
        ])->assertOk();
    }
}
