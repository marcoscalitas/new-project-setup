<?php

namespace Modules\Auth\Tests\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Laravel\Passport\Client;
use Laravel\Passport\Passport;
use Modules\User\Models\User;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
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

    // == PROTECTED ROUTES ==

    public function test_unverified_user_cannot_access_protected_routes(): void
    {
        $user = User::factory()->unverified()->create();

        Passport::actingAs($user);

        $this->getJson('/api/v1/users')->assertForbidden();
    }

    // == RESEND ==

    public function test_user_can_resend_verification_email(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        Passport::actingAs($user);

        $response = $this->postJson('/api/v1/auth/email/resend');

        $response->assertOk()
            ->assertJson(['message' => 'E-mail de verificação reenviado.']);

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_already_verified_user_gets_422_on_resend(): void
    {
        $user = User::factory()->create();

        Passport::actingAs($user);

        $response = $this->postJson('/api/v1/auth/email/resend');

        $response->assertUnprocessable()
            ->assertJson(['message' => 'E-mail já verificado.']);
    }

    public function test_unauthenticated_user_cannot_resend_verification_email(): void
    {
        $response = $this->postJson('/api/v1/auth/email/resend');

        $response->assertUnauthorized();
    }

    // == REGISTER SENDS VERIFICATION EMAIL ==

    public function test_register_sends_verification_email(): void
    {
        Notification::fake();

        $response = $this->postJson('/api/v1/auth/register', [
            'name'                  => 'John Doe',
            'email'                 => 'john@example.com',
            'password'              => 'SecurePass1!',
            'password_confirmation' => 'SecurePass1!',
        ]);

        $response->assertCreated();

        $user = User::where('email', 'john@example.com')->first();

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_registered_user_has_unverified_email_initially(): void
    {
        Notification::fake();

        $this->postJson('/api/v1/auth/register', [
            'name'                  => 'Jane Doe',
            'email'                 => 'jane@example.com',
            'password'              => 'SecurePass1!',
            'password_confirmation' => 'SecurePass1!',
        ]);

        $user = User::where('email', 'jane@example.com')->first();

        $this->assertNull($user->email_verified_at);
    }
}
