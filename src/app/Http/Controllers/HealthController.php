<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Throwable;

class HealthController
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache'    => $this->checkCache(),
            'queue'    => $this->checkQueue(),
        ];

        $status = collect($checks)->every(fn ($c) => $c['status'] === 'ok') ? 'ok' : 'degraded';
        $httpStatus = $status === 'ok' ? 200 : 503;

        return response()->json([
            'status' => $status,
            'checks' => $checks,
        ], $httpStatus);
    }

    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();

            return ['status' => 'ok'];
        } catch (Throwable $e) {
            return ['status' => 'fail', 'message' => 'Database unreachable'];
        }
    }

    private function checkCache(): array
    {
        try {
            $key = '_health_check_' . uniqid();
            Cache::put($key, true, 5);

            if (!Cache::get($key)) {
                return ['status' => 'fail', 'message' => 'Cache read/write failed'];
            }

            Cache::forget($key);

            return ['status' => 'ok'];
        } catch (Throwable $e) {
            return ['status' => 'fail', 'message' => 'Cache unreachable'];
        }
    }

    private function checkQueue(): array
    {
        try {
            Queue::size();

            return ['status' => 'ok'];
        } catch (Throwable $e) {
            return ['status' => 'fail', 'message' => 'Queue unreachable'];
        }
    }
}
