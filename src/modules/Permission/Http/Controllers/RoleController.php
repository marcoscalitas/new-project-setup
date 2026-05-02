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

        if ($request->expectsJson()) {
            $roles = $this->roleService->getAll($perPage);
            return RoleResource::collection($roles)->response();
        }

        $roles = $this->roleService->getAll(null);
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

    public function show(Role $role): JsonResponse|\Illuminate\View\View
    {
        Gate::authorize('view', $role);

        if (request()->expectsJson()) {
            return response()->json(new RoleResource($role));
        }

        return view('permission::roles.show', compact('role'));
    }

    public function edit(Role $role): \Illuminate\View\View
    {
        Gate::authorize('update', $role);

        $permissions = Permission::where('guard_name', 'web')->get();

        return view('permission::roles.edit', compact('role', 'permissions'));
    }

    public function update(UpdateRoleRequest $request, Role $role): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        Gate::authorize('update', $role);

        $role = $this->roleService->update($role->id, $request->validated());

        if (request()->expectsJson()) {
            return response()->json(new RoleResource($role));
        }

        return redirect()->route('roles.index')->with('success', __('permissions.role_updated'));
    }

    public function destroy(Role $role): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        Gate::authorize('delete', $role);

        $this->roleService->delete($role->id);

        if (request()->expectsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('roles.index')->with('success', __('permissions.role_deleted'));
    }

    public function trashed(): \Illuminate\View\View
    {
        Gate::authorize('viewTrashed', Role::class);

        $roles = $this->roleService->getTrashed();

        return view('permission::roles.trashed', compact('roles'));
    }

    public function restore(string $ulid): \Illuminate\Http\RedirectResponse
    {
        $role = Role::withTrashed()->where('ulid', $ulid)->firstOrFail();

        Gate::authorize('restore', $role);

        $this->roleService->restore($ulid);

        return redirect()->route('roles.trashed')->with('success', __('permissions.role_restored'));
    }
}
