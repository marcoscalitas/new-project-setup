<?php

namespace Modules\Authentication\Tests\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Passport\Client;
use Modules\User\Models\User;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class TwoFactorChallengeTest extends TestCase
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

    private function userWithTwoFactor(): array
    {
        $user = User::factory()->create();

        $google2fa = new Google2FA;
        $secret    = $google2fa->generateSecretKey();

        $user->forceFill([
            'two_factor_secret'       => encrypt($secret),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1', 'recovery-code-2'])),
            'two_factor_confirmed_at' => now(),
        ])->save();

        return [$user, $secret];
    }

    public function test_login_returns_two_factor_flag_when_2fa_is_enabled(): void
    {
        [$user] = $this->userWithTwoFactor();

        $response = $this->postJson('/api/v1/auth/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['two_factor', 'two_factor_token'])
            ->assertJsonPath('two_factor', true);
    }

    public function test_login_without_2fa_still_returns_token_directly(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/v1/auth/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'user']);
    }

    public function test_two_factor_challenge_succeeds_with_valid_code(): void
    {
        [$user, $secret] = $this->userWithTwoFactor();

        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $pendingToken = $loginResponse->json('two_factor_token');
        $code         = (new Google2FA)->getCurrentOtp($secret);

        $response = $this->postJson('/api/v1/auth/two-factor-challenge', [
            'two_factor_token' => $pendingToken,
            'code'             => $code,
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'user']);
    }

    public function test_two_factor_challenge_fails_with_invalid_code(): void
    {
        [$user] = $this->userWithTwoFactor();

        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $response = $this->postJson('/api/v1/auth/two-factor-challenge', [
            'two_factor_token' => $loginResponse->json('two_factor_token'),
            'code'             => '000000',
        ]);

        $response->assertUnprocessable()
            ->assertJson(['message' => 'Código inválido.']);
    }

    public function test_two_factor_challenge_fails_with_expired_token(): void
    {
        $response = $this->postJson('/api/v1/auth/two-factor-challenge', [
            'two_factor_token' => '00000000-0000-4000-8000-000000000000',
            'code'             => '123456',
        ]);

        $response->assertUnprocessable()
            ->assertJson(['message' => 'Token inválido ou expirado.']);
    }

    public function test_two_factor_challenge_succeeds_with_valid_recovery_code(): void
    {
        [$user] = $this->userWithTwoFactor();

        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $response = $this->postJson('/api/v1/auth/two-factor-challenge', [
            'two_factor_token' => $loginResponse->json('two_factor_token'),
            'recovery_code'    => 'recovery-code-1',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'user']);
    }

    public function test_recovery_code_is_invalidated_after_use(): void
    {
        [$user] = $this->userWithTwoFactor();

        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $pendingToken = $loginResponse->json('two_factor_token');

        $this->postJson('/api/v1/auth/two-factor-challenge', [
            'two_factor_token' => $pendingToken,
            'recovery_code'    => 'recovery-code-1',
        ])->assertOk();

        // Login again to get a new pending token
        $loginResponse2 = $this->postJson('/api/v1/auth/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        // Try to use the same recovery code again
        $response = $this->postJson('/api/v1/auth/two-factor-challenge', [
            'two_factor_token' => $loginResponse2->json('two_factor_token'),
            'recovery_code'    => 'recovery-code-1',
        ]);

        $response->assertUnprocessable()
            ->assertJson(['message' => 'Código de recuperação inválido.']);
    }

    public function test_two_factor_pending_token_is_consumed_after_success(): void
    {
        [$user, $secret] = $this->userWithTwoFactor();

        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $pendingToken = $loginResponse->json('two_factor_token');
        $code         = (new Google2FA)->getCurrentOtp($secret);

        $this->postJson('/api/v1/auth/two-factor-challenge', [
            'two_factor_token' => $pendingToken,
            'code'             => $code,
        ])->assertOk();

        // Same pending token should no longer work
        $response = $this->postJson('/api/v1/auth/two-factor-challenge', [
            'two_factor_token' => $pendingToken,
            'code'             => $code,
        ]);

        $response->assertUnprocessable()
            ->assertJson(['message' => 'Token inválido ou expirado.']);
    }
}
