<?php

namespace Modules\Permission\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Modules\Permission\Http\Requests\StoreRoleRequest;
use Modules\Permission\Http\Requests\UpdateRoleRequest;
use Modules\Permission\Http\Resources\RoleResource;
use Modules\Permission\Models\Role;
use Modules\Permission\Services\RoleService;

class RoleController
{
    public function __construct(private RoleService $roleService) {}

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Role::class);

        $perPage = min((int) $request->query('per_page', 15), 100);
        $roles   = $this->roleService->getAll($perPage);

        return RoleResource::collection($roles)->response();
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        Gate::authorize('create', Role::class);

        $role = $this->roleService->create($request->validated());

        return response()->json(new RoleResource($role), 201);
    }

    public function show(Role $role): JsonResponse
    {
        Gate::authorize('view', $role);

        return response()->json(new RoleResource($role));
    }

    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        Gate::authorize('update', $role);

        $role = $this->roleService->update($role->id, $request->validated());

        return response()->json(new RoleResource($role));
    }

    public function destroy(Role $role): JsonResponse
    {
        Gate::authorize('delete', $role);

        try {
            $this->roleService->delete($role->id);
        } catch (ValidationException $e) {
            throw $e;
        }

        return response()->json(null, 204);
    }
}
