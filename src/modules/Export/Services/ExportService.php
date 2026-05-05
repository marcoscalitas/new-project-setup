<?php

namespace Modules\Export\Services;

use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Export\Jobs\ProcessExportJob;
use Modules\Export\Models\Export;
use Shared\Contracts\Export\Exporter;
use Shared\Data\Export\ExportRequestData;
use Shared\Data\Export\ExportResultData;
use Spatie\Browsershot\Browsershot;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportService
{
    private const SYNC_LIMIT = 5000;

    public function handle(
        Exporter $exporter,
        ExportRequestData $data,
    ): BinaryFileResponse|StreamedResponse|Response|ExportResultData {
        abort_unless(in_array($data->format, $exporter->allowedFormats(), true), 422, 'Formato de exportação inválido.');

        $count = $exporter->getQuery($data->filters)->count();

        if ($count > self::getSyncLimit()) {
            return $this->dispatchAsync($exporter, $data);
        }

        return $this->generateSync($exporter, $data);
    }

    private function generateSync(
        Exporter $exporter,
        ExportRequestData $data,
    ): BinaryFileResponse|StreamedResponse|Response {
        $filename = $exporter->getFilename().'_'.now()->format('Y-m-d_His').'.'.$data->format;

        if ($data->format === 'pdf') {
            return $this->generatePdfResponse($exporter, $data->filters, $filename);
        }

        $excelFormat = $data->format === 'xlsx'
            ? \Maatwebsite\Excel\Excel::XLSX
            : \Maatwebsite\Excel\Excel::CSV;

        return Excel::download($exporter->getExportClass($data->filters), $filename, $excelFormat);
    }

    private function generatePdfResponse(
        Exporter $exporter,
        array $filters,
        string $filename,
    ): Response {
        $html = view($exporter->getPdfView(), $exporter->getPdfData($filters))->render();

        $pdf = Browsershot::html($html)
            ->format('A4')
            ->landscape(false)
            ->margins(10, 10, 10, 10)
            ->addChromiumArguments(['no-sandbox', 'disable-setuid-sandbox'])
            ->pdf();

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    private function dispatchAsync(
        Exporter $exporter,
        ExportRequestData $data,
    ): ExportResultData {
        $export = Export::create([
            'user_id' => $data->userId,
            'module' => $exporter->key(),
            'format' => $data->format,
            'status' => 'pending',
            'expires_at' => now()->addHours(self::getExpirationHours()),
        ]);

        ProcessExportJob::dispatch($export, $exporter->key(), $data->format, $data->filters);

        return new ExportResultData($export->ulid, 'pending');
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
