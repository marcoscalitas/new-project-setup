<?php

namespace Modules\Export\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Modules\Export\Http\Requests\ExportRequest;
use Modules\Export\Models\Export;
use Modules\Export\Services\ExportService;
use Shared\Contracts\Export\ExportRegistry;
use Shared\Data\Export\ExportRequestData;
use Shared\Data\Export\ExportResultData;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController
{
    public function __construct(
        private readonly ExportService $exportService,
        private readonly ExportRegistry $exportRegistry,
    ) {}

    public function export(ExportRequest $request): JsonResponse|BinaryFileResponse|StreamedResponse|Response
    {
        $data = new ExportRequestData(
            module: $request->input('module'),
            format: $request->input('format'),
            filters: $request->input('filters', []),
            userId: Auth::id(),
        );

        $result = $this->exportService->handle(
            $this->exportRegistry->get($data->module),
            $data,
        );

        if ($result instanceof ExportResultData) {
            return response()->json([
                'message' => 'Exportação em processamento. Receberás uma notificação quando estiver pronto.',
                'ulid' => $result->ulid,
                'status' => $result->status,
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
            'ulid' => $export->ulid,
            'module' => $export->module,
            'format' => $export->format,
            'status' => $export->status,
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
