<?php

namespace Modules\Auth\Tests\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Auth\Notifications\VerifyEmail;
use Laravel\Passport\Client;
use Laravel\Passport\Passport;
use Modules\Permission\Models\Permission;
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

    private function verificationUrlFor(User $user): string
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->getEmailForVerification())]
        );
    }

    // == REGISTER ==

    public function test_register_sends_verification_email(): void
    {
        Notification::fake();

        $this->postJson('/api/v1/auth/register', [
            'name'                  => 'John Doe',
            'email'                 => 'john@example.com',
            'password'              => 'SecurePass1!',
            'password_confirmation' => 'SecurePass1!',
        ])->assertCreated();

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

        $this->assertNull(User::where('email', 'jane@example.com')->first()->email_verified_at);
    }

    // == LOGIN ==

    public function test_login_blocked_for_unverified_user(): void
    {
        $user = User::factory()->unverified()->create(['password' => bcrypt('SecurePass1!')]);

        $this->postJson('/api/v1/auth/login', [
            'email'    => $user->email,
            'password' => 'SecurePass1!',
        ])->assertUnprocessable();
    }

    public function test_login_does_not_resend_verification_email_automatically(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create(['password' => bcrypt('SecurePass1!')]);

        $this->postJson('/api/v1/auth/login', [
            'email'    => $user->email,
            'password' => 'SecurePass1!',
        ])->assertUnprocessable();

        Notification::assertNothingSent();
    }

    public function test_login_succeeds_after_email_verified(): void
    {
        $user = User::factory()->create(['password' => bcrypt('SecurePass1!')]);

        $this->postJson('/api/v1/auth/login', [
            'email'    => $user->email,
            'password' => 'SecurePass1!',
        ])->assertOk()->assertJsonStructure(['token']);
    }

    // == VERIFICATION LINK ==

    public function test_clicking_verification_link_marks_email_as_verified(): void
    {
        $user = User::factory()->unverified()->create();

        $url = $this->verificationUrlFor($user);

        $this->actingAs($user)->get($url);

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_verification_link_with_wrong_hash_does_not_verify_email(): void
    {
        $user = User::factory()->unverified()->create();

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => 'wronghash']
        );

        $this->actingAs($user)->get($url);

        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_expired_verification_link_does_not_verify_email(): void
    {
        $user = User::factory()->unverified()->create();

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->subMinute(),
            ['id' => $user->id, 'hash' => sha1($user->getEmailForVerification())]
        );

        $this->actingAs($user)->get($url);

        $this->assertNull($user->fresh()->email_verified_at);
    }

    // == RESEND ==

    public function test_user_can_resend_verification_email(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        Passport::actingAs($user);

        $this->postJson('/api/v1/auth/email/resend')
            ->assertOk()
            ->assertJson(['message' => 'E-mail de verificação reenviado.']);

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_resend_invalidates_previous_verification_link(): void
    {
        $user = User::factory()->unverified()->create();

        $oldUrl = $this->verificationUrlFor($user);

        $user->sendEmailVerificationNotification();
        $user->refresh();

        // old link should no longer verify the email
        $this->actingAs($user)->get($oldUrl);

        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_new_link_after_resend_verifies_email(): void
    {
        $user = User::factory()->unverified()->create();

        $user->sendEmailVerificationNotification();
        $user->refresh();

        $newUrl = $this->verificationUrlFor($user);

        $this->actingAs($user)->get($newUrl);

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_already_verified_user_gets_422_on_resend(): void
    {
        $user = User::factory()->create();

        Passport::actingAs($user);

        $this->postJson('/api/v1/auth/email/resend')
            ->assertUnprocessable()
            ->assertJson(['message' => 'E-mail já verificado.']);
    }

    public function test_unauthenticated_user_cannot_resend_verification_email(): void
    {
        $this->postJson('/api/v1/auth/email/resend')->assertUnauthorized();
    }

    // == PROTECTED ROUTES ==

    public function test_unverified_user_cannot_access_protected_routes(): void
    {
        $user = User::factory()->unverified()->create();

        Passport::actingAs($user);

        $this->getJson('/api/v1/users')->assertForbidden();
    }

    public function test_verified_user_with_permissions_can_access_protected_routes(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(
            Permission::firstOrCreate(['name' => 'user.list', 'guard_name' => 'api'])
        );

        Passport::actingAs($user);

        $this->getJson('/api/v1/users')->assertOk();
    }
}
