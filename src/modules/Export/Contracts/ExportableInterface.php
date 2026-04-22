<?php

namespace Modules\Export\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;

interface ExportableInterface
{
    public function getQuery(array $filters = []): Builder;

    public function getExportClass(array $filters = []): FromQuery;

    public function getPdfView(): string;

    public function getFilename(): string;

    public function getPdfData(array $filters = []): array;
}
