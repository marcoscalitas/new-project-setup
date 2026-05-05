<?php

namespace Modules\ActivityLog\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Modules\ActivityLog\Models\ActivityLog;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ActivityLogExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(private readonly array $filters = []) {}

    public function query(): Builder
    {
        $query = ActivityLog::query()->with('causer');

        if (! empty($this->filters['causer_id'])) {
            $query->where('causer_id', $this->filters['causer_id']);
        }

        if (! empty($this->filters['log_name'])) {
            $query->where('log_name', $this->filters['log_name']);
        }

        if (! empty($this->filters['subject_type'])) {
            $query->where('subject_type', $this->filters['subject_type']);
        }

        if (! empty($this->filters['date_from'])) {
            $query->whereDate('created_at', '>=', $this->filters['date_from']);
        }

        if (! empty($this->filters['date_to'])) {
            $query->whereDate('created_at', '<=', $this->filters['date_to']);
        }

        return $query->latest();
    }

    public function headings(): array
    {
        return ['ID', 'Log', 'Descrição', 'Utilizador', 'Modelo', 'ID Modelo', 'Data'];
    }

    public function map(mixed $activity): array
    {
        return [
            $activity->id,
            $activity->log_name,
            $activity->description,
            $activity->causer?->name ?? '—',
            class_basename($activity->subject_type ?? '—'),
            $activity->subject_id ?? '—',
            $activity->created_at->format('d/m/Y H:i'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '7C3AED'],
                ],
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            ],
        ];
    }

    public function title(): string
    {
        return 'Activity Log';
    }
}
