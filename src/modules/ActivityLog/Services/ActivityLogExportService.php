<?php

namespace Modules\ActivityLog\Services;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Modules\ActivityLog\Exports\ActivityLogExport;
use Modules\ActivityLog\Models\ActivityLog;
use Shared\Contracts\Export\Exporter;

class ActivityLogExportService implements Exporter
{
    public function key(): string
    {
        return 'activity_log';
    }

    public function allowedFormats(): array
    {
        return ['csv', 'xlsx', 'pdf'];
    }

    public function getQuery(array $filters = []): Builder
    {
        $query = ActivityLog::query();

        if (! empty($filters['causer_id'])) {
            $query->where('causer_id', $filters['causer_id']);
        }

        if (! empty($filters['log_name'])) {
            $query->where('log_name', $filters['log_name']);
        }

        if (! empty($filters['subject_type'])) {
            $query->where('subject_type', $filters['subject_type']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
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
            'activities' => $this->getQuery($filters)->with('causer')->latest()->get(),
            'generated_at' => now()->format('d/m/Y H:i'),
        ];
    }
}
