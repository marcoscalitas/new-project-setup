<?php

namespace Modules\Export\Tests\Web;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Export\Models\Export;
use Modules\User\Models\User;
use Tests\TestCase;

class ExportWebTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_authenticated_user_can_view_exports_index(): void
    {
        $response = $this->actingAs($this->user)->get('/exports');

        $response->assertOk()
            ->assertViewIs('export::exports.index');
    }

    public function test_unauthenticated_user_is_redirected_from_exports(): void
    {
        $response = $this->get('/exports');

        $response->assertRedirect();
    }

    public function test_user_only_sees_own_exports(): void
    {
        $otherUser = User::factory()->create();

        Export::factory()->create(['user_id' => $this->user->id, 'module' => 'users', 'format' => 'csv', 'status' => 'completed']);
        Export::factory()->create(['user_id' => $otherUser->id, 'module' => 'users', 'format' => 'csv', 'status' => 'completed']);

        $response = $this->actingAs($this->user)->get('/exports');

        $response->assertOk();
        $this->assertCount(1, $response->viewData('exports'));
    }

    public function test_export_request_requires_module_and_format(): void
    {
        $response = $this->actingAs($this->user)->post('/exports', []);

        $response->assertSessionHasErrors(['module', 'format']);
    }

    public function test_download_returns_404_for_other_users_export(): void
    {
        $otherUser = User::factory()->create();

        $export = Export::factory()->create([
            'user_id' => $otherUser->id,
            'status'  => 'completed',
            'path'    => 'exports/fake.csv',
            'module'  => 'users',
            'format'  => 'csv',
        ]);

        $response = $this->actingAs($this->user)->get('/exports/' . $export->uuid . '/download');

        $response->assertNotFound();
    }
}
