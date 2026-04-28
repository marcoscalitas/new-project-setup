<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Modules\User\Models\User;
use Tests\TestCase;

class SetLocaleFromHeaderTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    // == Accept-Language header ==

    public function test_defaults_to_pt_when_no_accept_language_header(): void
    {
        Passport::actingAs($this->user);

        $response = $this->withoutHeader('Accept-Language')
            ->withServerVariables(['HTTP_ACCEPT_LANGUAGE' => ''])
            ->postJson('/api/v1/auth/logout');

        $response->assertOk()
            ->assertJson(['message' => 'Sessão encerrada com sucesso.']);
    }

    public function test_sets_pt_locale_when_accept_language_is_pt(): void
    {
        Passport::actingAs($this->user);

        $response = $this->withHeader('Accept-Language', 'pt')
            ->postJson('/api/v1/auth/logout');

        $response->assertOk()
            ->assertJson(['message' => 'Sessão encerrada com sucesso.']);
    }

    public function test_sets_en_locale_when_accept_language_is_en(): void
    {
        Passport::actingAs($this->user);

        $response = $this->withHeader('Accept-Language', 'en')
            ->postJson('/api/v1/auth/logout');

        $response->assertOk()
            ->assertJson(['message' => 'Session ended successfully.']);
    }

    public function test_falls_back_to_pt_when_language_is_not_supported(): void
    {
        Passport::actingAs($this->user);

        $response = $this->withHeader('Accept-Language', 'es')
            ->postJson('/api/v1/auth/logout');

        $response->assertOk()
            ->assertJson(['message' => 'Sessão encerrada com sucesso.']);
    }

    public function test_negotiates_preferred_language_from_complex_header(): void
    {
        Passport::actingAs($this->user);

        $response = $this->withHeader('Accept-Language', 'en-US,en;q=0.9,pt;q=0.8')
            ->postJson('/api/v1/auth/logout');

        $response->assertOk()
            ->assertJson(['message' => 'Session ended successfully.']);
    }

    // == locale.switch route ==

    public function test_locale_switch_stores_locale_in_session(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('locale.switch', 'en'));

        $response->assertRedirect();
        $response->assertSessionHas('locale', 'en');
    }

    public function test_locale_switch_stores_pt_in_session(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('locale.switch', 'pt'));

        $response->assertRedirect();
        $response->assertSessionHas('locale', 'pt');
    }

    public function test_locale_switch_ignores_unsupported_locale(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('locale.switch', 'es'));

        $response->assertRedirect();
        $response->assertSessionMissing('locale');
    }

    public function test_session_locale_takes_priority_over_accept_language_header(): void
    {
        // Web route with PT Accept-Language header, but session stores 'en'
        // Session middleware runs on web routes, so $request->hasSession() = true
        $this->actingAs($this->user)
            ->withHeader('Accept-Language', 'pt')
            ->withSession(['locale' => 'en'])
            ->get(route('home'));

        $this->assertEquals('en', app()->getLocale());
    }
}
