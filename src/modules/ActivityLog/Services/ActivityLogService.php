<?php

namespace Modules\ActivityLog\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Spatie\Activitylog\Models\Activity;

class ActivityLogService
{
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Activity::query()->latest();

        if (!empty($filters['causer_id'])) {
            $query->where('causer_id', $filters['causer_id']);
        }

        if (!empty($filters['subject_type'])) {
            $query->where('subject_type', $filters['subject_type']);
        }

        if (!empty($filters['log_name'])) {
            $query->where('log_name', $filters['log_name']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->paginate($perPage);
    }

    public function getForUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return Activity::query()
            ->where('causer_id', $userId)
            ->latest()
            ->paginate($perPage);
    }

    public function findById(int $id): Activity
    {
        return Activity::findOrFail($id);
    }
}
