<?php

namespace Modules\Permission\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\Permission\Http\Requests\StorePermissionRequest;
use Modules\Permission\Http\Requests\UpdatePermissionRequest;
use Modules\Permission\Http\Resources\PermissionResource;
use Modules\Permission\Services\PermissionService;

class PermissionController
{
    public function __construct(private PermissionService $permissionService) {}

    public function index(): JsonResponse
    {
        $permissions = $this->permissionService->getAll();

        return response()->json(PermissionResource::collection($permissions));
    }

    public function store(StorePermissionRequest $request): JsonResponse
    {
        $permission = $this->permissionService->create($request->validated());

        return response()->json(new PermissionResource($permission), 201);
    }

    public function show(int $id): JsonResponse
    {
        $permission = $this->permissionService->findById($id);

        return response()->json(new PermissionResource($permission));
    }

    public function update(UpdatePermissionRequest $request, int $id): JsonResponse
    {
        $permission = $this->permissionService->update($id, $request->validated());

        return response()->json(new PermissionResource($permission));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->permissionService->delete($id);

        return response()->json(null, 204);
    }
}
