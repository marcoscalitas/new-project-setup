<?php

namespace Modules\User\Tests\Web;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\User\Models\User;
use Tests\TestCase;

class ProfileWebTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name'     => 'Test User',
            'email'    => 'test@example.com',
            'password' => bcrypt('current-password'),
        ]);
    }

    public function test_authenticated_user_can_view_profile(): void
    {
        $response = $this->actingAs($this->user)->get('/profile');

        $response->assertOk()
            ->assertViewIs('user::profile.edit')
            ->assertSee('Test User');
    }

    public function test_unauthenticated_user_is_redirected_from_profile(): void
    {
        $response = $this->get('/profile');

        $response->assertRedirect();
    }

    public function test_user_can_update_profile_name_and_email(): void
    {
        $response = $this->actingAs($this->user)->put('/profile', [
            'name'  => 'Updated Name',
            'email' => 'updated@example.com',
        ]);

        $response->assertRedirect(route('profile.edit'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id'    => $this->user->id,
            'name'  => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    }

    public function test_profile_update_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)->put('/profile', []);

        $response->assertSessionHasErrors(['name', 'email']);
    }

    public function test_user_can_change_password(): void
    {
        $response = $this->actingAs($this->user)->put('/profile/password', [
            'current_password'      => 'current-password',
            'password'              => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ]);

        $response->assertRedirect(route('profile.edit'))
            ->assertSessionHas('success');
    }

    public function test_password_change_fails_with_wrong_current_password(): void
    {
        $response = $this->actingAs($this->user)->put('/profile/password', [
            'current_password'      => 'wrong-password',
            'password'              => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ]);

        $response->assertSessionHasErrors('current_password');
    }

    public function test_password_confirmation_must_match(): void
    {
        $response = $this->actingAs($this->user)->put('/profile/password', [
            'current_password'      => 'current-password',
            'password'              => 'new-password-123',
            'password_confirmation' => 'different-password',
        ]);

        $response->assertSessionHasErrors('password');
    }
}
