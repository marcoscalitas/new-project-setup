<?php

namespace Modules\Permission\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Modules\Permission\Http\Requests\StoreRoleRequest;
use Modules\Permission\Http\Requests\UpdateRoleRequest;
use Modules\Permission\Http\Resources\RoleResource;
use Modules\Permission\Models\Role;
use Modules\Permission\Services\RoleService;
use Modules\Permission\Models\Permission;

class RoleController
{
    public function __construct(private RoleService $roleService) {}

    public function index(Request $request): JsonResponse|\Illuminate\View\View
    {
        Gate::authorize('viewAny', Role::class);

        $perPage = min((int) $request->query('per_page', 15), 100);
        $roles = $this->roleService->getAll($perPage);

        if ($request->expectsJson()) {
            return RoleResource::collection($roles)->response();
        }

        return view('permission::roles.index', compact('roles'));
    }

    public function create(): \Illuminate\View\View
    {
        Gate::authorize('create', Role::class);

        $permissions = Permission::where('guard_name', 'web')->get();

        return view('permission::roles.create', compact('permissions'));
    }

    public function store(StoreRoleRequest $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        Gate::authorize('create', Role::class);

        $role = $this->roleService->create($request->validated());

        if (request()->expectsJson()) {
            return response()->json(new RoleResource($role), 201);
        }

        return redirect()->route('roles.index')->with('success', __('permissions.role_created'));
    }

    public function show(int $id): JsonResponse|\Illuminate\View\View
    {
        $role = $this->roleService->findById($id);

        Gate::authorize('view', $role);

        if (request()->expectsJson()) {
            return response()->json(new RoleResource($role));
        }

        return view('permission::roles.show', compact('role'));
    }

    public function edit(int $id): \Illuminate\View\View
    {
        $role = Role::findOrFail($id);

        Gate::authorize('update', $role);

        $permissions = Permission::where('guard_name', 'web')->get();

        return view('permission::roles.edit', compact('role', 'permissions'));
    }

    public function update(UpdateRoleRequest $request, int $id): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        Gate::authorize('update', Role::findOrFail($id));

        $role = $this->roleService->update($id, $request->validated());

        if (request()->expectsJson()) {
            return response()->json(new RoleResource($role));
        }

        return redirect()->route('roles.index')->with('success', __('permissions.role_updated'));
    }

    public function destroy(int $id): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        Gate::authorize('delete', Role::findOrFail($id));

        $this->roleService->delete($id);

        if (request()->expectsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('roles.index')->with('success', __('permissions.role_deleted'));
    }
}
