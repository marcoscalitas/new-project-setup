<?php

namespace Modules\Export\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Modules\Export\Http\Requests\ExportRequest;
use Modules\Export\Models\Export;
use Modules\Export\Services\ExportService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Http\Response;

class ExportController
{
    public function __construct(private readonly ExportService $exportService) {}

    public function index(Request $request): JsonResponse|\Illuminate\View\View
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Use POST /api/v1/exports to request an export.']);
        }

        $exports = Export::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('export::exports.index', compact('exports'));
    }

    public function export(ExportRequest $request): JsonResponse|BinaryFileResponse|StreamedResponse|Response|\Illuminate\Http\RedirectResponse
    {
        $module = $request->input('module');
        $key    = "export.{$module}";

        abort_unless(app()->bound($key), 422, 'Módulo de exportação inválido.');

        $exporter = app($key);
        $filters  = $request->input('filters', []);
        $format   = $request->input('format');

        $result = $this->exportService->handle($exporter, $format, $filters);

        if ($request->expectsJson()) {
            if (is_array($result)) {
                return response()->json([
                    'message' => 'Exportação em processamento. Receberás uma notificação quando estiver pronto.',
                    'uuid'    => $result['uuid'],
                    'status'  => $result['status'],
                ], 202);
            }
            return $result;
        }

        if (is_array($result)) {
            return redirect()->route('exports.index')
                ->with('success', __('ui.export_requested'));
        }

        return $result;
    }

    public function status(string $uuid): JsonResponse
    {
        $export = Export::where('uuid', $uuid)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return response()->json([
            'uuid'       => $export->uuid,
            'module'     => $export->module,
            'format'     => $export->format,
            'status'     => $export->status,
            'expires_at' => $export->expires_at?->toISOString(),
        ]);
    }

    public function download(string $uuid): BinaryFileResponse|JsonResponse
    {
        $export = Export::where('uuid', $uuid)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        abort_unless($export->isCompleted(), 422, 'Exportação ainda não está pronta.');
        abort_if($export->isExpired(), 410, 'O link de download expirou.');

        $fullPath = Storage::disk('local')->path($export->path);

        return response()->download($fullPath, $export->filename);
    }
}
