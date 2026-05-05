<?php

namespace Modules\Export\Tests\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\Client;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Export\Jobs\ProcessExportJob;
use Modules\Export\Models\Export;
use Modules\Export\Services\ExportService;
use Modules\User\Models\User;
use Tests\TestCase;

class ExportTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        if (! file_exists(storage_path('oauth-private.key'))) {
            $this->artisan('passport:keys', ['--force' => true]);
        }

        Client::create([
            'name' => 'Test Personal Client',
            'secret' => null,
            'redirect_uris' => [],
            'grant_types' => ['personal_access'],
            'provider' => 'users',
            'revoked' => false,
        ]);

        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test')->accessToken;
    }

    private function authHeaders(): array
    {
        return ['Authorization' => 'Bearer '.$this->token];
    }

    // == SYNC EXPORTS ==

    public function test_unauthenticated_user_cannot_export(): void
    {
        $response = $this->postJson('/api/v1/exports', [
            'module' => 'users',
            'format' => 'csv',
        ]);

        $response->assertUnauthorized();
    }

    public function test_export_fails_with_invalid_module(): void
    {
        $response = $this->postJson('/api/v1/exports', [
            'module' => 'invalid',
            'format' => 'csv',
        ], $this->authHeaders());

        $response->assertUnprocessable();
    }

    public function test_export_fails_with_invalid_format(): void
    {
        $response = $this->postJson('/api/v1/exports', [
            'module' => 'users',
            'format' => 'doc',
        ], $this->authHeaders());

        $response->assertUnprocessable();
    }

    public function test_sync_csv_export_returns_file_when_under_limit(): void
    {
        Excel::fake();
        config(['export.sync_limit' => 5000]);

        User::factory()->count(3)->create();

        $this->postJson('/api/v1/exports', [
            'module' => 'users',
            'format' => 'csv',
        ], $this->authHeaders());

        Excel::assertDownloaded('users_'.now()->format('Y-m-d').'_'.now()->format('His').'.csv');
    }

    public function test_sync_xlsx_export_returns_file_when_under_limit(): void
    {
        Excel::fake();
        config(['export.sync_limit' => 5000]);

        User::factory()->count(3)->create();

        $response = $this->postJson('/api/v1/exports', [
            'module' => 'users',
            'format' => 'xlsx',
        ], $this->authHeaders());

        $response->assertOk();

        Excel::assertDownloaded('users_'.now()->format('Y-m-d').'_'.now()->format('His').'.xlsx');
    }

    // == ASYNC EXPORTS ==

    public function test_async_export_dispatches_job_when_over_limit(): void
    {
        Queue::fake();
        config(['export.sync_limit' => 0]);

        $response = $this->postJson('/api/v1/exports', [
            'module' => 'users',
            'format' => 'xlsx',
        ], $this->authHeaders());

        $response->assertAccepted();
        $response->assertJsonStructure(['message', 'ulid', 'status']);
        $response->assertJsonPath('status', 'pending');

        Queue::assertPushed(ProcessExportJob::class);
    }

    public function test_async_export_creates_export_record(): void
    {
        Queue::fake();
        config(['export.sync_limit' => 0]);

        $this->postJson('/api/v1/exports', [
            'module' => 'users',
            'format' => 'xlsx',
        ], $this->authHeaders());

        $this->assertDatabaseHas('exports', [
            'user_id' => $this->user->id,
            'module' => 'users',
            'format' => 'xlsx',
            'status' => 'pending',
        ]);
    }

    // == STATUS ==

    public function test_can_check_status_of_own_export(): void
    {
        $export = Export::create([
            'user_id' => $this->user->id,
            'module' => 'users',
            'format' => 'xlsx',
            'status' => 'completed',
            'path' => 'exports/test/users.xlsx',
            'filename' => 'users.xlsx',
            'expires_at' => now()->addHours(24),
        ]);

        $response = $this->getJson("/api/v1/exports/{$export->ulid}/status", $this->authHeaders());

        $response->assertOk();
        $response->assertJsonPath('status', 'completed');
        $response->assertJsonPath('ulid', $export->ulid);
    }

    public function test_cannot_check_status_of_another_users_export(): void
    {
        $otherUser = User::factory()->create();
        $export = Export::create([
            'user_id' => $otherUser->id,
            'module' => 'users',
            'format' => 'xlsx',
            'status' => 'completed',
            'expires_at' => now()->addHours(24),
        ]);

        $response = $this->getJson("/api/v1/exports/{$export->ulid}/status", $this->authHeaders());

        $response->assertNotFound();
    }

    // == DOWNLOAD ==

    public function test_cannot_download_pending_export(): void
    {
        $export = Export::create([
            'user_id' => $this->user->id,
            'module' => 'users',
            'format' => 'xlsx',
            'status' => 'pending',
            'expires_at' => now()->addHours(24),
        ]);

        $response = $this->getJson("/api/v1/exports/{$export->ulid}/download", $this->authHeaders());

        $response->assertUnprocessable();
    }

    public function test_cannot_download_expired_export(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('exports/test/users.xlsx', 'fake content');

        $export = Export::create([
            'user_id' => $this->user->id,
            'module' => 'users',
            'format' => 'xlsx',
            'status' => 'completed',
            'path' => 'exports/test/users.xlsx',
            'filename' => 'users.xlsx',
            'expires_at' => now()->subHour(),
        ]);

        $response = $this->getJson("/api/v1/exports/{$export->ulid}/download", $this->authHeaders());

        $response->assertStatus(410);
    }

    public function test_can_download_completed_non_expired_export(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('exports/test/users.xlsx', 'fake xlsx content');

        $export = Export::create([
            'user_id' => $this->user->id,
            'module' => 'users',
            'format' => 'xlsx',
            'status' => 'completed',
            'path' => 'exports/test/users.xlsx',
            'filename' => 'users.xlsx',
            'expires_at' => now()->addHours(24),
        ]);

        $response = $this->get("/api/v1/exports/{$export->ulid}/download", $this->authHeaders());

        $response->assertOk();
        $response->assertHeader('content-disposition');
    }

    // == PURGE COMMAND ==

    public function test_purge_command_deletes_expired_exports(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('exports/abc/users.xlsx', 'content');

        $expired = Export::create([
            'user_id' => $this->user->id,
            'module' => 'users',
            'format' => 'xlsx',
            'status' => 'completed',
            'path' => 'exports/abc/users.xlsx',
            'filename' => 'users.xlsx',
            'expires_at' => now()->subHour(),
        ]);

        $valid = Export::create([
            'user_id' => $this->user->id,
            'module' => 'users',
            'format' => 'csv',
            'status' => 'completed',
            'expires_at' => now()->addHours(24),
        ]);

        $this->artisan('exports:purge')->assertSuccessful();

        $this->assertDatabaseMissing('exports', ['id' => $expired->id]);
        $this->assertDatabaseHas('exports', ['id' => $valid->id]);
    }

    // == ACTIVITY LOG ==

    public function test_sync_csv_export_activity_log(): void
    {
        Excel::fake();
        config(['export.sync_limit' => 5000]);

        $response = $this->postJson('/api/v1/exports', [
            'module' => 'activity_log',
            'format' => 'csv',
        ], $this->authHeaders());

        $response->assertOk();

        Excel::assertDownloaded('activity_log_'.now()->format('Y-m-d').'_'.now()->format('His').'.csv');
    }

    public function test_sync_xlsx_export_activity_log(): void
    {
        Excel::fake();
        config(['export.sync_limit' => 5000]);

        $response = $this->postJson('/api/v1/exports', [
            'module' => 'activity_log',
            'format' => 'xlsx',
        ], $this->authHeaders());

        $response->assertOk();

        Excel::assertDownloaded('activity_log_'.now()->format('Y-m-d').'_'.now()->format('His').'.xlsx');
    }

    public function test_sync_pdf_export_users(): void
    {
        config(['export.sync_limit' => 5000]);
        User::factory()->count(2)->create();

        $this->mock(ExportService::class, function ($mock) {
            $mock->shouldReceive('handle')->once()->andReturn(
                response('%PDF-1.4 fake', 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="users_test.pdf"',
                ])
            );
        });

        $response = $this->postJson('/api/v1/exports', [
            'module' => 'users',
            'format' => 'pdf',
        ], $this->authHeaders());

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_sync_pdf_export_activity_log(): void
    {
        config(['export.sync_limit' => 5000]);

        $this->mock(ExportService::class, function ($mock) {
            $mock->shouldReceive('handle')->once()->andReturn(
                response('%PDF-1.4 fake', 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="activity_log_test.pdf"',
                ])
            );
        });

        $response = $this->postJson('/api/v1/exports', [
            'module' => 'activity_log',
            'format' => 'pdf',
        ], $this->authHeaders());

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    // == ASYNC EXTRAS ==

    public function test_async_csv_export_creates_record(): void
    {
        Queue::fake();
        config(['export.sync_limit' => 0]);

        $response = $this->postJson('/api/v1/exports', [
            'module' => 'users',
            'format' => 'csv',
        ], $this->authHeaders());

        $response->assertAccepted();
        Queue::assertPushed(ProcessExportJob::class);
        $this->assertDatabaseHas('exports', [
            'user_id' => $this->user->id,
            'module' => 'users',
            'format' => 'csv',
            'status' => 'pending',
        ]);
    }

    public function test_async_pdf_export_creates_record(): void
    {
        Queue::fake();
        config(['export.sync_limit' => 0]);

        $response = $this->postJson('/api/v1/exports', [
            'module' => 'users',
            'format' => 'pdf',
        ], $this->authHeaders());

        $response->assertAccepted();
        Queue::assertPushed(ProcessExportJob::class);
        $this->assertDatabaseHas('exports', [
            'user_id' => $this->user->id,
            'module' => 'users',
            'format' => 'pdf',
            'status' => 'pending',
        ]);
    }

    // == DOWNLOAD EXTRAS ==

    public function test_cannot_download_another_users_export(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('exports/other/users.xlsx', 'fake content');

        $otherUser = User::factory()->create();
        $export = Export::create([
            'user_id' => $otherUser->id,
            'module' => 'users',
            'format' => 'xlsx',
            'status' => 'completed',
            'path' => 'exports/other/users.xlsx',
            'filename' => 'users.xlsx',
            'expires_at' => now()->addHours(24),
        ]);

        $response = $this->getJson("/api/v1/exports/{$export->ulid}/download", $this->authHeaders());

        $response->assertNotFound();
    }

    public function test_failed_export_shows_failed_status(): void
    {
        $export = Export::create([
            'user_id' => $this->user->id,
            'module' => 'users',
            'format' => 'xlsx',
            'status' => 'failed',
            'error' => 'Chrome process failed',
            'expires_at' => now()->addHours(24),
        ]);

        $response = $this->getJson("/api/v1/exports/{$export->ulid}/status", $this->authHeaders());

        $response->assertOk();
        $response->assertJsonPath('status', 'failed');
    }
}
