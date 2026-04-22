<?php

namespace Modules\ActivityLog\Services;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Modules\ActivityLog\Exports\ActivityLogExport;
use Modules\Export\Contracts\ExportableInterface;
use Spatie\Activitylog\Models\Activity;

class ActivityLogExportService implements ExportableInterface
{
    public function getQuery(array $filters = []): Builder
    {
        $query = Activity::query();

        if (!empty($filters['causer_id'])) {
            $query->where('causer_id', $filters['causer_id']);
        }

        if (!empty($filters['log_name'])) {
            $query->where('log_name', $filters['log_name']);
        }

        if (!empty($filters['subject_type'])) {
            $query->where('subject_type', $filters['subject_type']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query;
    }

    public function getExportClass(array $filters = []): FromQuery
    {
        return new ActivityLogExport($filters);
    }

    public function getPdfView(): string
    {
        return 'activitylog::exports.pdf';
    }

    public function getFilename(): string
    {
        return 'activity_log';
    }

    public function getPdfData(array $filters = []): array
    {
        return [
            'activities'   => $this->getQuery($filters)->with('causer')->latest()->get(),
            'generated_at' => now()->format('d/m/Y H:i'),
        ];
    }
}
