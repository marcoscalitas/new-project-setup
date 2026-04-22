<?php

namespace Modules\ActivityLog\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Modules\ActivityLog\Http\Resources\ActivityLogResource;
use Modules\ActivityLog\Services\ActivityLogService;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController
{
    public function __construct(private ActivityLogService $service) {}

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Activity::class);

        $perPage = min((int) $request->query('per_page', 15), 100);
        $filters = $request->only(['causer_id', 'subject_type', 'log_name', 'date_from', 'date_to']);

        $logs = $this->service->getAll($filters, $perPage);

        return ActivityLogResource::collection($logs)->response();
    }

    public function show(int $id): JsonResponse
    {
        $activity = $this->service->findById($id);

        Gate::authorize('view', $activity);

        return response()->json(new ActivityLogResource($activity));
    }
}
