<?php

namespace Modules\Auth\Tests\Web;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Modules\User\Models\User;
use Tests\TestCase;

class UpdatePasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('OldPassword1!'),
        ]);

        $response = $this->actingAs($user)
            ->putJson('/auth/user/password', [
                'current_password'      => 'OldPassword1!',
                'password'              => 'NewPassword1!',
                'password_confirmation' => 'NewPassword1!',
            ]);

        $response->assertOk();
        $this->assertTrue(Hash::check('NewPassword1!', $user->fresh()->password));
    }

    public function test_password_update_requires_current_password(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->putJson('/auth/user/password', [
                'password'              => 'NewPassword1!',
                'password_confirmation' => 'NewPassword1!',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['current_password']);
    }

    public function test_password_update_fails_with_wrong_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('CorrectPassword1!'),
        ]);

        $response = $this->actingAs($user)
            ->putJson('/auth/user/password', [
                'current_password'      => 'WrongPassword1!',
                'password'              => 'NewPassword1!',
                'password_confirmation' => 'NewPassword1!',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['current_password']);
    }

    public function test_password_update_requires_confirmation(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('OldPassword1!'),
        ]);

        $response = $this->actingAs($user)
            ->putJson('/auth/user/password', [
                'current_password' => 'OldPassword1!',
                'password'         => 'NewPassword1!',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    public function test_guest_cannot_update_password(): void
    {
        $response = $this->putJson('/auth/user/password', [
            'current_password'      => 'OldPassword1!',
            'password'              => 'NewPassword1!',
            'password_confirmation' => 'NewPassword1!',
        ]);

        $response->assertUnauthorized();
    }
}
