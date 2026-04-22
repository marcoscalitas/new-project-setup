<?php

namespace Modules\User\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Modules\User\Models\User;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UsersExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(private readonly array $filters = []) {}

    public function query(): Builder
    {
        $query = User::query()->with('roles')->withoutTrashed();

        if (!empty($this->filters['role'])) {
            $query->whereHas('roles', fn ($q) => $q->where('name', $this->filters['role']));
        }

        if (!empty($this->filters['search'])) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->filters['search'] . '%')
                  ->orWhere('email', 'like', '%' . $this->filters['search'] . '%');
            });
        }

        return $query->latest();
    }

    public function headings(): array
    {
        return ['ID', 'Nome', 'Email', 'Roles', 'Verificado', 'Criado em'];
    }

    public function map(mixed $user): array
    {
        return [
            $user->id,
            $user->name,
            $user->email,
            $user->roles->pluck('name')->implode(', '),
            $user->email_verified_at ? 'Sim' : 'Não',
            $user->created_at->format('d/m/Y H:i'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font'      => ['bold' => true],
                'fill'      => [
                    'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2563EB'],
                ],
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            ],
        ];
    }

    public function title(): string
    {
        return 'Utilizadores';
    }
}
