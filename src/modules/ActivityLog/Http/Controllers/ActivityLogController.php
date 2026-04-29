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

    public function forUser(Request $request, int $userId): JsonResponse
    {
        if ($request->user()?->id !== $userId) {
            Gate::authorize('viewAny', Activity::class);
        }

        $perPage = min((int) $request->query('per_page', 15), 100);
        $logs = $this->service->getForUser($userId, $perPage);

        return ActivityLogResource::collection($logs)->response();
    }

    public function index(Request $request): JsonResponse|\Illuminate\View\View
    {
        Gate::authorize('viewAny', Activity::class);

        $filters = $request->only(['causer_id', 'subject_type', 'log_name', 'date_from', 'date_to']);

        if ($request->expectsJson()) {
            $perPage = min((int) $request->query('per_page', 15), 100);
            $logs = $this->service->getAll($filters, $perPage);
            return ActivityLogResource::collection($logs)->response();
        }

        $logs = $this->service->getAll($filters, 50);
        return view('activitylog::activity-log.index', compact('logs', 'filters'));
    }

    public function show(int $id): JsonResponse|\Illuminate\View\View
    {
        $activity = $this->service->findById($id);

        Gate::authorize('view', $activity);

        if (request()->expectsJson()) {
            return response()->json(new ActivityLogResource($activity));
        }

        return view('activitylog::activity-log.show', compact('activity'));
    }
}
