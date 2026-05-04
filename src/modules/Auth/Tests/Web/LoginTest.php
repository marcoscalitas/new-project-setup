<?php

namespace Modules\Auth\Tests\Web;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Modules\User\Models\User;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/auth/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk();
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_wrong_password(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/auth/login', [
            'email'    => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertUnprocessable();
        $this->assertGuest();
    }

    public function test_user_cannot_login_with_nonexistent_email(): void
    {
        $response = $this->postJson('/auth/login', [
            'email'    => 'ghost@example.com',
            'password' => 'password',
        ]);

        $response->assertUnprocessable();
        $this->assertGuest();
    }

    public function test_login_requires_email(): void
    {
        $response = $this->postJson('/auth/login', [
            'password' => 'password',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_requires_password(): void
    {
        $response = $this->postJson('/auth/login', [
            'email' => 'user@example.com',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/auth/logout');

        $response->assertNoContent();
        $this->assertGuest();
    }

    public function test_login_is_rate_limited(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/auth/login', [
                'email'    => $user->email,
                'password' => 'wrong-password',
            ]);
        }

        $response = $this->postJson('/auth/login', [
            'email'    => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(429);
    }

    // == Unverified user flow ==

    public function test_unverified_user_web_login_is_blocked_and_resend_triggered(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        $response = $this->postJson('/auth/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        // Login must fail — no session created
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['activation']);

        $this->assertGuest();

        // Verification email must have been resent
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_unverified_user_resend_is_rate_limited_to_once_per_two_minutes(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        $payload = ['email' => $user->email, 'password' => 'password'];

        // First attempt → resend fires
        $this->postJson('/auth/login', $payload)->assertUnprocessable();
        // Second attempt within 2 minutes → resend is suppressed
        $this->postJson('/auth/login', $payload)->assertUnprocessable();

        Notification::assertSentToTimes($user, VerifyEmail::class, 1);
    }

    public function test_verification_notice_logs_out_unverified_web_session(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->get(route('verification.notice'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['activation']);

        $this->assertGuest();
    }

    // == Public verification link (no auth needed) ==

    public function test_verification_activate_link_verifies_email_without_being_logged_in(): void
    {
        $user = User::factory()->unverified()->create();
        $user->sendEmailVerificationNotification();
        $user->refresh();

        $url = URL::temporarySignedRoute(
            'verification.activate',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->getEmailForVerification())],
        );

        // Not authenticated — click the link directly
        $this->get($url)->assertRedirect('/');

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_verification_activate_link_logs_user_in(): void
    {
        $user = User::factory()->unverified()->create();
        $user->sendEmailVerificationNotification();
        $user->refresh();

        $url = URL::temporarySignedRoute(
            'verification.activate',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->getEmailForVerification())],
        );

        $this->assertGuest();
        $this->get($url);
        $this->assertAuthenticatedAs($user);
    }

    public function test_verification_activate_link_with_wrong_hash_is_rejected(): void
    {
        $user = User::factory()->unverified()->create();

        $url = URL::temporarySignedRoute(
            'verification.activate',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => 'wronghash'],
        );

        $this->get($url)->assertForbidden();

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_expired_verification_activate_link_is_rejected(): void
    {
        $user = User::factory()->unverified()->create();
        $user->sendEmailVerificationNotification();
        $user->refresh();

        $url = URL::temporarySignedRoute(
            'verification.activate',
            now()->subMinute(),
            ['id' => $user->id, 'hash' => sha1($user->getEmailForVerification())],
        );

        $this->get($url)->assertForbidden();

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }
}
