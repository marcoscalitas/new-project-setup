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
        $permissions = $this->permissionService->getAll($perPage);

        if ($request->expectsJson()) {
            return PermissionResource::collection($permissions)->response();
        }

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

        return redirect()->route('permissions.index')->with('success', 'Permission created.');
    }

    public function show(int $id): JsonResponse|\Illuminate\View\View
    {
        $permission = $this->permissionService->findById($id);

        Gate::authorize('view', $permission);

        if (request()->expectsJson()) {
            return response()->json(new PermissionResource($permission));
        }

        return view('permission::permissions.show', compact('permission'));
    }

    public function edit(int $id): \Illuminate\View\View
    {
        $permission = Permission::findOrFail($id);

        Gate::authorize('update', $permission);

        return view('permission::permissions.edit', compact('permission'));
    }

    public function update(UpdatePermissionRequest $request, int $id): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        Gate::authorize('update', Permission::findOrFail($id));

        $permission = $this->permissionService->update($id, $request->validated());

        if (request()->expectsJson()) {
            return response()->json(new PermissionResource($permission));
        }

        return redirect()->route('permissions.index')->with('success', 'Permission updated.');
    }

    public function destroy(int $id): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        Gate::authorize('delete', Permission::findOrFail($id));

        $this->permissionService->delete($id);

        if (request()->expectsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('permissions.index')->with('success', 'Permission deleted.');
    }
}
