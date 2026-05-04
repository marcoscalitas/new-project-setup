<?php

namespace Modules\Authorization\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Modules\Authorization\Http\Requests\StorePermissionRequest;
use Modules\Authorization\Http\Requests\UpdatePermissionRequest;
use Modules\Authorization\Http\Resources\PermissionResource;
use Modules\Authorization\Models\Permission;
use Modules\Authorization\Services\PermissionService;

class PermissionController
{
    public function __construct(private PermissionService $permissionService) {}

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Permission::class);

        $perPage     = min((int) $request->query('per_page', 15), 100);
        $permissions = $this->permissionService->getAll(
            perPage:   $perPage,
            search:    $request->query('search'),
            sort:      $request->query('sort', 'name'),
            direction: $request->query('direction', 'asc'),
        );

        return PermissionResource::collection($permissions)->response();
    }

    public function store(StorePermissionRequest $request): JsonResponse
    {
        Gate::authorize('create', Permission::class);

        $permission = $this->permissionService->create($request->validated());

        return response()->json(new PermissionResource($permission), 201);
    }

    public function show(Permission $permission): JsonResponse
    {
        Gate::authorize('view', $permission);

        return response()->json(new PermissionResource($permission));
    }

    public function update(UpdatePermissionRequest $request, Permission $permission): JsonResponse
    {
        Gate::authorize('update', $permission);

        $permission = $this->permissionService->update($permission->id, $request->validated());

        return response()->json(new PermissionResource($permission));
    }

    public function destroy(Permission $permission): JsonResponse
    {
        Gate::authorize('delete', $permission);

        $this->permissionService->delete($permission->id);

        return response()->json(null, 204);
    }

    public function trashed(Request $request): JsonResponse
    {
        Gate::authorize('viewTrashed', Permission::class);

        $perPage     = min((int) $request->query('per_page', 15), 100);
        $permissions = Permission::onlyTrashed()->paginate($perPage);

        return PermissionResource::collection($permissions)->response();
    }

    public function restore(string $ulid): JsonResponse
    {
        $permission = Permission::onlyTrashed()->where('ulid', $ulid)->firstOrFail();

        Gate::authorize('restore', $permission);

        $this->permissionService->restore($ulid);

        return response()->json(new PermissionResource($permission->fresh()));
    }
}
