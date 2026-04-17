<?php

namespace Modules\Permission\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Modules\Permission\Http\Requests\StorePermissionRequest;
use Modules\Permission\Http\Requests\UpdatePermissionRequest;
use Modules\Permission\Http\Resources\PermissionResource;
use Modules\Permission\Models\Permission;
use Modules\Permission\Services\PermissionService;

class PermissionController
{
    public function __construct(private PermissionService $permissionService) {}

    public function index(): JsonResponse
    {
        Gate::authorize('viewAny', Permission::class);

        $permissions = $this->permissionService->getAll();

        return response()->json(PermissionResource::collection($permissions));
    }

    public function store(StorePermissionRequest $request): JsonResponse
    {
        Gate::authorize('create', Permission::class);

        $permission = $this->permissionService->create($request->validated());

        return response()->json(new PermissionResource($permission), 201);
    }

    public function show(int $id): JsonResponse
    {
        $permission = $this->permissionService->findById($id);

        Gate::authorize('view', $permission);

        return response()->json(new PermissionResource($permission));
    }

    public function update(UpdatePermissionRequest $request, int $id): JsonResponse
    {
        Gate::authorize('update', Permission::findOrFail($id));

        $permission = $this->permissionService->update($id, $request->validated());

        return response()->json(new PermissionResource($permission));
    }

    public function destroy(int $id): JsonResponse
    {
        Gate::authorize('delete', Permission::findOrFail($id));

        $this->permissionService->delete($id);

        return response()->json(null, 204);
    }
}
