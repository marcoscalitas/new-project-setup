<?php

namespace Modules\Permission\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Modules\Permission\Http\Requests\StoreRoleRequest;
use Modules\Permission\Http\Requests\UpdateRoleRequest;
use Modules\Permission\Http\Resources\RoleResource;
use Modules\Permission\Models\Role;
use Modules\Permission\Services\RoleService;

class RoleController
{
    public function __construct(private RoleService $roleService) {}

    public function index(): JsonResponse
    {
        Gate::authorize('viewAny', Role::class);

        $roles = $this->roleService->getAll();

        return response()->json(RoleResource::collection($roles));
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        Gate::authorize('create', Role::class);

        $role = $this->roleService->create($request->validated());

        return response()->json(new RoleResource($role), 201);
    }

    public function show(int $id): JsonResponse
    {
        $role = $this->roleService->findById($id);

        Gate::authorize('view', $role);

        return response()->json(new RoleResource($role));
    }

    public function update(UpdateRoleRequest $request, int $id): JsonResponse
    {
        Gate::authorize('update', Role::findOrFail($id));

        $role = $this->roleService->update($id, $request->validated());

        return response()->json(new RoleResource($role));
    }

    public function destroy(int $id): JsonResponse
    {
        Gate::authorize('delete', Role::findOrFail($id));

        $this->roleService->delete($id);

        return response()->json(null, 204);
    }
}
