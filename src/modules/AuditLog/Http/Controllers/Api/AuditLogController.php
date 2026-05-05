<?php

namespace Modules\AuditLog\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Modules\AuditLog\Http\Resources\AuditLogResource;
use Modules\AuditLog\Models\AuditLog;
use Modules\AuditLog\Services\AuditLogService;
use Modules\User\Models\User;

class AuditLogController
{
    public function __construct(private AuditLogService $service) {}

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', AuditLog::class);

        $perPage = min((int) $request->query('per_page', 15), 100);
        $filters = $request->only(['causer_id', 'subject_type', 'log_name', 'date_from', 'date_to']);
        $logs = $this->service->getAll($filters, $perPage);

        return AuditLogResource::collection($logs)->response();
    }

    public function forUser(Request $request, string $userUlid): JsonResponse
    {
        $target = User::where('ulid', $userUlid)->firstOrFail();

        if ($request->user()?->id !== $target->id) {
            Gate::authorize('viewAny', AuditLog::class);
        }

        $perPage = min((int) $request->query('per_page', 15), 100);
        $logs = $this->service->getForUser($target->id, $perPage);

        return AuditLogResource::collection($logs)->response();
    }

    public function show(int $id): JsonResponse
    {
        $activity = $this->service->findById($id);

        Gate::authorize('view', $activity);

        return response()->json(new AuditLogResource($activity));
    }
}
