<?php

namespace Modules\Media\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Modules\Media\Http\Resources\MediaResource;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaController
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Media::class);

        $perPage = min((int) $request->query('per_page', 15), 100);
        $media   = Media::orderByDesc('created_at')->paginate($perPage);

        return MediaResource::collection($media)->response();
    }

    public function show(int $id): JsonResponse
    {
        $media = Media::findOrFail($id);

        Gate::authorize('view', $media);

        return new MediaResource($media)->response();
    }

    public function destroy(int $id): JsonResponse
    {
        $media = Media::findOrFail($id);

        Gate::authorize('delete', $media);

        $media->delete();

        return response()->json(null, 204);
    }
}
