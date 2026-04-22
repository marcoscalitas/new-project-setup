<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class HealthTest extends TestCase
{
    public function test_returns_ok_when_all_checks_pass(): void
    {
        $response = $this->getJson('/health');

        $response->assertOk()
            ->assertJsonStructure([
                'status',
                'checks' => ['database', 'cache', 'queue'],
            ])
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('checks.database.status', 'ok')
            ->assertJsonPath('checks.cache.status', 'ok')
            ->assertJsonPath('checks.queue.status', 'ok');
    }

    public function test_returns_503_when_database_fails(): void
    {
        DB::shouldReceive('connection->getPdo')->andThrow(new \RuntimeException('Connection refused'));

        $response = $this->getJson('/health');

        $response->assertStatus(503)
            ->assertJsonPath('status', 'degraded')
            ->assertJsonPath('checks.database.status', 'fail');
    }

    public function test_returns_503_when_cache_fails(): void
    {
        Cache::shouldReceive('put')->andThrow(new \RuntimeException('Redis down'));

        $response = $this->getJson('/health');

        $response->assertStatus(503)
            ->assertJsonPath('status', 'degraded')
            ->assertJsonPath('checks.cache.status', 'fail');
    }

    public function test_returns_503_when_queue_fails(): void
    {
        Queue::shouldReceive('size')->andThrow(new \RuntimeException('Queue unreachable'));

        $response = $this->getJson('/health');

        $response->assertStatus(503)
            ->assertJsonPath('status', 'degraded')
            ->assertJsonPath('checks.queue.status', 'fail');
    }

    public function test_accessible_without_authentication(): void
    {
        $response = $this->getJson('/health');

        $response->assertStatus(200);
    }

    public function test_response_has_correct_structure_on_degraded(): void
    {
        Queue::shouldReceive('size')->andThrow(new \RuntimeException('Queue unreachable'));

        $response = $this->getJson('/health');

        $response->assertStatus(503)
            ->assertJsonStructure([
                'status',
                'checks' => [
                    'database' => ['status'],
                    'cache'    => ['status'],
                    'queue'    => ['status', 'message'],
                ],
            ]);
    }
}
