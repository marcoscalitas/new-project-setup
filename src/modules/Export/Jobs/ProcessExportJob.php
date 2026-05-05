<?php

namespace Modules\Export\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Export\Models\Export;
use Modules\Export\Notifications\ExportReadyNotification;
use Shared\Contracts\Export\Exporter;
use Shared\Contracts\Export\ExportRegistry;
use Spatie\Browsershot\Browsershot;
use Throwable;

class ProcessExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 300;

    public function __construct(
        private readonly Export $export,
        private readonly string $exporterKey,
        private readonly string $format,
        private readonly array $filters = [],
    ) {}

    public function handle(): void
    {
        $this->export->update(['status' => 'processing']);

        $exporter = app(ExportRegistry::class)->get($this->exporterKey);

        $filename = $exporter->getFilename().'_'.now()->format('Y-m-d_His').'.'.$this->format;
        $path = 'exports/'.$this->export->uuid.'/'.$filename;

        if ($this->format === 'pdf') {
            $this->generatePdf($exporter, $path);
        } else {
            $this->generateSpreadsheet($exporter, $path);
        }

        $this->export->update([
            'status' => 'completed',
            'path' => $path,
            'filename' => $filename,
        ]);

        $this->export->user->notify(new ExportReadyNotification($this->export));
    }

    public function failed(Throwable $e): void
    {
        $this->export->update([
            'status' => 'failed',
            'error' => $e->getMessage(),
        ]);
    }

    private function generateSpreadsheet(Exporter $exporter, string $path): void
    {
        $excelFormat = $this->format === 'xlsx'
            ? \Maatwebsite\Excel\Excel::XLSX
            : \Maatwebsite\Excel\Excel::CSV;

        Excel::store(
            $exporter->getExportClass($this->filters),
            $path,
            'local',
            $excelFormat,
        );
    }

    private function generatePdf(Exporter $exporter, string $path): void
    {
        $html = view($exporter->getPdfView(), $exporter->getPdfData($this->filters))->render();

        $pdf = Browsershot::html($html)
            ->format('A4')
            ->margins(10, 10, 10, 10)
            ->addChromiumArguments(['no-sandbox', 'disable-setuid-sandbox'])
            ->pdf();

        Storage::disk('local')->put($path, $pdf);
    }
}
