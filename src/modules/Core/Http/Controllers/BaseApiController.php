<?php

namespace Modules\Core\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
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

    public function show(Model $model): JsonResponse
    {
        Gate::authorize('view', $model);

        return response()->json(new ($this->resourceClass())($model));
    }

    public function destroy(Model $model): JsonResponse
    {
        Gate::authorize('delete', $model);

        $this->service->delete($model->id);

        return response()->json(null, 204);
    }
}
