<?php

namespace Modules\Authentication\Tests\Web;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\User\Models\User;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class TwoFactorAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    private function confirmPasswordAndEnableTwoFactor(User $user): void
    {
        $this->actingAs($user)
            ->postJson('/auth/user/confirm-password', ['password' => 'password'])
            ->assertStatus(201);

        $this->actingAs($user)
            ->postJson('/auth/user/two-factor-authentication')
            ->assertOk();
    }

    private function confirmTwoFactor(User $user): string
    {
        $user->refresh();
        $secret = decrypt($user->two_factor_secret);
        $code   = (new Google2FA)->getCurrentOtp($secret);

        $this->actingAs($user)
            ->postJson('/auth/user/confirmed-two-factor-authentication', ['code' => $code])
            ->assertOk();

        return $secret;
    }

    public function test_user_can_enable_two_factor_authentication(): void
    {
        $user = User::factory()->create();

        $this->confirmPasswordAndEnableTwoFactor($user);

        $this->assertNotNull($user->fresh()->two_factor_secret);
    }

    public function test_user_can_confirm_two_factor_authentication(): void
    {
        $user = User::factory()->create();

        $this->confirmPasswordAndEnableTwoFactor($user);
        $this->confirmTwoFactor($user);

        $this->assertNotNull($user->fresh()->two_factor_confirmed_at);
    }

    public function test_user_can_retrieve_recovery_codes(): void
    {
        $user = User::factory()->create();

        $this->confirmPasswordAndEnableTwoFactor($user);

        $response = $this->actingAs($user)
            ->getJson('/auth/user/two-factor-recovery-codes');

        $response->assertOk();
        $this->assertCount(8, $response->json());
    }

    public function test_user_can_regenerate_recovery_codes(): void
    {
        $user = User::factory()->create();

        $this->confirmPasswordAndEnableTwoFactor($user);

        $oldCodes = $this->actingAs($user)
            ->getJson('/auth/user/two-factor-recovery-codes')
            ->json();

        $this->actingAs($user)
            ->postJson('/auth/user/two-factor-recovery-codes');

        $newCodes = $this->actingAs($user)
            ->getJson('/auth/user/two-factor-recovery-codes')
            ->json();

        $this->assertNotEquals($oldCodes, $newCodes);
    }

    public function test_user_can_disable_two_factor_authentication(): void
    {
        $user = User::factory()->create();

        $this->confirmPasswordAndEnableTwoFactor($user);

        $response = $this->actingAs($user)
            ->deleteJson('/auth/user/two-factor-authentication');

        $response->assertOk();
        $this->assertNull($user->fresh()->two_factor_secret);
    }

    public function test_guest_cannot_enable_two_factor(): void
    {
        $response = $this->postJson('/auth/user/two-factor-authentication');

        $response->assertUnauthorized();
    }

    public function test_two_factor_challenge_with_valid_code(): void
    {
        $google2fa = new Google2FA;
        $secret = $google2fa->generateSecretKey();

        $user = User::factory()->create([
            'two_factor_secret'       => encrypt($secret),
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => encrypt(json_encode([
                'code1-abcde', 'code2-abcde', 'code3-abcde', 'code4-abcde',
                'code5-abcde', 'code6-abcde', 'code7-abcde', 'code8-abcde',
            ])),
        ]);

        // Login triggers 2FA flow — stores login.id in session
        $this->postJson('/auth/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $code = $google2fa->getCurrentOtp($secret);

        $response = $this->postJson('/auth/two-factor-challenge', [
            'code' => $code,
        ]);

        $response->assertSuccessful();
    }

    public function test_two_factor_challenge_with_recovery_code(): void
    {
        $google2fa = new Google2FA;
        $secret = $google2fa->generateSecretKey();

        $recoveryCodes = ['recover1-abcde', 'recover2-abcde', 'recover3-abcde', 'recover4-abcde'];

        $user = User::factory()->create([
            'two_factor_secret'       => encrypt($secret),
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => encrypt(json_encode($recoveryCodes)),
        ]);

        // Login triggers 2FA flow
        $this->postJson('/auth/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $response = $this->postJson('/auth/two-factor-challenge', [
            'recovery_code' => $recoveryCodes[0],
        ]);

        $response->assertSuccessful();
    }
}
