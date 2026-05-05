<?php

namespace Modules\Authorization\Http\Controllers\Web;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Modules\Authorization\Http\Requests\StorePermissionRequest;
use Modules\Authorization\Http\Requests\UpdatePermissionRequest;
use Modules\Authorization\Models\Permission;
use Modules\Authorization\Services\PermissionService;

class PermissionController
{
    public function __construct(private PermissionService $permissionService) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Permission::class);

        $permissions = $this->permissionService->getAll(
            perPage: $this->resolvePerPage($request),
            search: $request->query('search'),
            sort: $request->query('sort', 'name'),
            direction: $request->query('direction', 'asc'),
        );

        return view('authorization::permissions.index', compact('permissions'));
    }

    public function create(): View
    {
        Gate::authorize('create', Permission::class);

        return view('authorization::permissions.create');
    }

    public function store(StorePermissionRequest $request): RedirectResponse
    {
        Gate::authorize('create', Permission::class);

        $this->permissionService->create($request->validated());

        return redirect()->route('permissions.index')->with('success', __('permissions.permission_created'));
    }

    public function show(Permission $permission): View
    {
        Gate::authorize('view', $permission);

        return view('authorization::permissions.show', compact('permission'));
    }

    public function edit(Permission $permission): View
    {
        Gate::authorize('update', $permission);

        return view('authorization::permissions.edit', compact('permission'));
    }

    public function update(UpdatePermissionRequest $request, Permission $permission): RedirectResponse
    {
        Gate::authorize('update', $permission);

        $this->permissionService->update($permission->id, $request->validated());

        return redirect()->route('permissions.index')->with('success', __('permissions.permission_updated'));
    }

    public function destroy(Permission $permission): RedirectResponse
    {
        Gate::authorize('delete', $permission);

        $this->permissionService->delete($permission->id);

        return redirect()->route('permissions.index')->with('success', __('permissions.permission_deleted'));
    }

    public function trashed(Request $request): View
    {
        Gate::authorize('viewTrashed', Permission::class);

        $permissions = $this->permissionService->getTrashed(perPage: $this->resolvePerPage($request));

        return view('authorization::permissions.trashed', compact('permissions'));
    }

    public function restore(string $ulid): RedirectResponse
    {
        $permission = Permission::withTrashed()->where('ulid', $ulid)->firstOrFail();

        Gate::authorize('restore', $permission);

        $this->permissionService->restore($ulid);

        return redirect()->route('permissions.trashed')->with('success', __('permissions.permission_restored'));
    }

    private function resolvePerPage(Request $request): int
    {
        $perPage = (int) $request->query('per_page', 15);

        return in_array($perPage, [5, 10, 15, 25, 50, 100], true) ? $perPage : 15;
    }
}
