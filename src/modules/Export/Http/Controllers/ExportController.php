<?php

namespace Modules\Export\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Modules\Export\Http\Requests\ExportRequest;
use Modules\Export\Models\Export;
use Modules\Export\Services\ExportService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController
{
    public function __construct(private readonly ExportService $exportService) {}

    public function export(ExportRequest $request): JsonResponse|BinaryFileResponse|StreamedResponse|Response
    {
        $module = $request->input('module');
        $key    = "export.{$module}";

        abort_unless(app()->bound($key), 422, 'Módulo de exportação inválido.');

        $exporter = app($key);
        $filters  = $request->input('filters', []);
        $format   = $request->input('format');

        $result = $this->exportService->handle($exporter, $format, $filters);

        if (is_array($result)) {
            return response()->json([
                'message' => 'Exportação em processamento. Receberás uma notificação quando estiver pronto.',
                'ulid'    => $result['ulid'],
                'status'  => $result['status'],
            ], 202);
        }

        return $result;
    }

    public function status(string $ulid): JsonResponse
    {
        $export = Export::where('ulid', $ulid)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return response()->json([
            'ulid'       => $export->ulid,
            'module'     => $export->module,
            'format'     => $export->format,
            'status'     => $export->status,
            'expires_at' => $export->expires_at?->toISOString(),
        ]);
    }

    public function download(string $ulid): BinaryFileResponse|JsonResponse
    {
        $export = Export::where('ulid', $ulid)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        abort_unless($export->isCompleted(), 422, 'Exportação ainda não está pronta.');
        abort_if($export->isExpired(), 410, 'O link de download expirou.');

        $fullPath = Storage::disk('local')->path($export->path);

        return response()->download($fullPath, $export->filename);
    }
}
