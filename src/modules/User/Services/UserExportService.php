<?php

namespace Modules\User\Services;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Modules\User\Exports\UsersExport;
use Modules\User\Models\User;
use Shared\Contracts\Export\Exporter;

class UserExportService implements Exporter
{
    public function key(): string
    {
        return 'users';
    }

    public function allowedFormats(): array
    {
        return ['csv', 'xlsx', 'pdf'];
    }

    public function getQuery(array $filters = []): Builder
    {
        $query = User::query()->withoutTrashed();

        if (! empty($filters['role'])) {
            $query->whereHas('roles', fn ($q) => $q->where('name', $filters['role']));
        }

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%'.$filters['search'].'%')
                    ->orWhere('email', 'like', '%'.$filters['search'].'%');
            });
        }

        return $query;
    }

    public function getExportClass(array $filters = []): FromQuery
    {
        return new UsersExport($filters);
    }

    public function getPdfView(): string
    {
        return 'user::exports.pdf';
    }

    public function getFilename(): string
    {
        return 'users';
    }

    public function getPdfData(array $filters = []): array
    {
        return [
            'users' => $this->getQuery($filters)->with('roles')->latest()->get(),
            'generated_at' => now()->format('d/m/Y H:i'),
        ];
    }
}
