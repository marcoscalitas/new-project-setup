<?php

namespace Shared\Contracts\Export;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;

interface Exporter
{
    public function key(): string;

    public function allowedFormats(): array;

    public function getQuery(array $filters = []): Builder;

    public function getExportClass(array $filters = []): FromQuery;

    public function getPdfView(): string;

    public function getFilename(): string;

    public function getPdfData(array $filters = []): array;
}
