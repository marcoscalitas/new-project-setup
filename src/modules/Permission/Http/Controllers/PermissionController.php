<?php

namespace Modules\Permission\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Modules\Permission\Http\Requests\StorePermissionRequest;
use Modules\Permission\Http\Requests\UpdatePermissionRequest;
use Modules\Permission\Http\Resources\PermissionResource;
use Modules\Permission\Models\Permission;
use Modules\Permission\Services\PermissionService;

class PermissionController
{
    public function __construct(private PermissionService $permissionService) {}

    public function index(Request $request): JsonResponse|\Illuminate\View\View
    {
        Gate::authorize('viewAny', Permission::class);

        $perPage = min((int) $request->query('per_page', 15), 100);

        if ($request->expectsJson()) {
            $permissions = $this->permissionService->getAll($perPage);
            return PermissionResource::collection($permissions)->response();
        }

        $permissions = $this->permissionService->getAll(null);
        return view('permission::permissions.index', compact('permissions'));
    }

    public function create(): \Illuminate\View\View
    {
        Gate::authorize('create', Permission::class);

        return view('permission::permissions.create');
    }

    public function store(StorePermissionRequest $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        Gate::authorize('create', Permission::class);

        $permission = $this->permissionService->create($request->validated());

        if (request()->expectsJson()) {
            return response()->json(new PermissionResource($permission), 201);
        }

        return redirect()->route('permissions.index')->with('success', __('permissions.permission_created'));
    }

    public function show(Permission $permission): JsonResponse|\Illuminate\View\View
    {
        Gate::authorize('view', $permission);

        if (request()->expectsJson()) {
            return response()->json(new PermissionResource($permission));
        }

        return view('permission::permissions.show', compact('permission'));
    }

    public function edit(Permission $permission): \Illuminate\View\View
    {
        Gate::authorize('update', $permission);

        return view('permission::permissions.edit', compact('permission'));
    }

    public function update(UpdatePermissionRequest $request, Permission $permission): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        Gate::authorize('update', $permission);

        $permission = $this->permissionService->update($permission->id, $request->validated());

        if (request()->expectsJson()) {
            return response()->json(new PermissionResource($permission));
        }

        return redirect()->route('permissions.index')->with('success', __('permissions.permission_updated'));
    }

    public function destroy(Permission $permission): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        Gate::authorize('delete', $permission);

        $this->permissionService->delete($permission->id);

        if (request()->expectsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('permissions.index')->with('success', __('permissions.permission_deleted'));
    }

    public function trashed(): \Illuminate\View\View
    {
        Gate::authorize('viewTrashed', Permission::class);

        $permissions = $this->permissionService->getTrashed();

        return view('permission::permissions.trashed', compact('permissions'));
    }

    public function restore(string $ulid): \Illuminate\Http\RedirectResponse
    {
        $permission = Permission::withTrashed()->where('ulid', $ulid)->firstOrFail();

        Gate::authorize('restore', $permission);

        $this->permissionService->restore($ulid);

        return redirect()->route('permissions.trashed')->with('success', __('permissions.permission_restored'));
    }
}
