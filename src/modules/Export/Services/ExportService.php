<?php

namespace Modules\Export\Services;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Export\Contracts\ExportableInterface;
use Modules\Export\Jobs\ProcessExportJob;
use Modules\Export\Models\Export;
use Spatie\Browsershot\Browsershot;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportService
{
    private const SYNC_LIMIT = 5000;

    public function handle(
        ExportableInterface $exporter,
        string $format,
        array $filters = [],
    ): BinaryFileResponse|StreamedResponse|Response|array {
        $count = $exporter->getQuery($filters)->count();

        if ($count > self::getSyncLimit()) {
            return $this->dispatchAsync($exporter, $format, $filters);
        }

        return $this->generateSync($exporter, $format, $filters);
    }

    private function generateSync(
        ExportableInterface $exporter,
        string $format,
        array $filters,
    ): BinaryFileResponse|StreamedResponse|Response {
        $filename = $exporter->getFilename() . '_' . now()->format('Y-m-d_His') . '.' . $format;

        if ($format === 'pdf') {
            return $this->generatePdfResponse($exporter, $filters, $filename);
        }

        $excelFormat = $format === 'xlsx'
            ? \Maatwebsite\Excel\Excel::XLSX
            : \Maatwebsite\Excel\Excel::CSV;

        return Excel::download($exporter->getExportClass($filters), $filename, $excelFormat);
    }

    private function generatePdfResponse(
        ExportableInterface $exporter,
        array $filters,
        string $filename,
    ): Response {
        $html = view($exporter->getPdfView(), $exporter->getPdfData($filters))->render();

        $pdf = Browsershot::html($html)
            ->format('A4')
            ->landscape(false)
            ->margins(10, 10, 10, 10)
            ->pdf();

        return response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    private function dispatchAsync(
        ExportableInterface $exporter,
        string $format,
        array $filters,
    ): array {
        $export = Export::create([
            'user_id'    => Auth::id(),
            'module'     => $exporter->getFilename(),
            'format'     => $format,
            'status'     => 'pending',
            'expires_at' => now()->addHours(self::getExpirationHours()),
        ]);

        ProcessExportJob::dispatch($export, $exporter, $format, $filters);

        return ['uuid' => $export->uuid, 'status' => 'pending'];
    }

    public static function getSyncLimit(): int
    {
        return (int) config('export.sync_limit', self::SYNC_LIMIT);
    }

    public static function getExpirationHours(): int
    {
        return (int) config('export.expiration_hours', 24);
    }
}
