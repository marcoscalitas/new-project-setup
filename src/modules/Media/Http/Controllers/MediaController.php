<?php

namespace Modules\Media\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Modules\Media\Http\Resources\MediaResource;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaController
{
    public function index(Request $request): JsonResponse|\Illuminate\View\View
    {
        Gate::authorize('viewAny', Media::class);

        $perPage = min((int) $request->query('per_page', 15), 100);
        $media   = Media::orderByDesc('created_at')->paginate($perPage);

        if ($request->expectsJson()) {
            return MediaResource::collection($media)->response();
        }

        return view('media::media.index', compact('media'));
    }

    public function show(Request $request, int $id): JsonResponse|\Illuminate\View\View
    {
        Gate::authorize('view', Media::class);

        $media = Media::findOrFail($id);

        if ($request->expectsJson()) {
            return (new MediaResource($media))->response();
        }

        return view('media::media.show', compact('media'));
    }

    public function destroy(Request $request, int $id): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        Gate::authorize('delete', Media::class);

        $media = Media::findOrFail($id);
        $media->delete();

        if ($request->expectsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('media.index')->with('success', __('ui.media_deleted'));
    }
}
