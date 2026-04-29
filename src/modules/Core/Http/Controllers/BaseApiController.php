<?php

namespace Modules\Core\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Modules\Core\Services\BaseService;

abstract class BaseApiController
{
    public function __construct(protected BaseService $service) {}

    abstract protected function modelClass(): string;

    abstract protected function resourceClass(): string;

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', $this->modelClass());

        $perPage = min((int) $request->query('per_page', 15), 100);
        $items   = $this->service->getAll($perPage);

        return $this->resourceClass()::collection($items)->response();
    }

    public function show(int $id): JsonResponse
    {
        $item = $this->service->findById($id);

        Gate::authorize('view', $item);

        return response()->json(new ($this->resourceClass())($item));
    }

    public function destroy(int $id): JsonResponse
    {
        Gate::authorize('delete', $this->modelClass()::findOrFail($id));

        $this->service->delete($id);

        return response()->json(null, 204);
    }
}
