<?php

namespace Modules\Permission\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\Permission\Http\Requests\StoreRoleRequest;
use Modules\Permission\Http\Requests\UpdateRoleRequest;
use Modules\Permission\Http\Resources\RoleResource;
use Modules\Permission\Services\RoleService;

class RoleController
{
    public function __construct(private RoleService $roleService) {}

    public function index(): JsonResponse
    {
        $roles = $this->roleService->getAll();

        return response()->json(RoleResource::collection($roles));
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        $role = $this->roleService->create($request->validated());

        return response()->json(new RoleResource($role), 201);
    }

    public function show(int $id): JsonResponse
    {
        $role = $this->roleService->findById($id);

        return response()->json(new RoleResource($role));
    }

    public function update(UpdateRoleRequest $request, int $id): JsonResponse
    {
        $role = $this->roleService->update($id, $request->validated());

        return response()->json(new RoleResource($role));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->roleService->delete($id);

        return response()->json(null, 204);
    }
}
