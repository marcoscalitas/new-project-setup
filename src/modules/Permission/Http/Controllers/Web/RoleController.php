<?php

namespace Modules\Permission\Http\Controllers\Web;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Modules\Permission\Http\Requests\StoreRoleRequest;
use Modules\Permission\Http\Requests\UpdateRoleRequest;
use Modules\Permission\Models\Permission;
use Modules\Permission\Models\Role;
use Modules\Permission\Services\RoleService;

class RoleController
{
    public function __construct(private RoleService $roleService) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Role::class);

        $roles = $this->roleService->getAll(
            perPage:   15,
            search:    $request->query('search'),
            sort:      $request->query('sort', 'name'),
            direction: $request->query('direction', 'asc'),
        );

        return view('permission::roles.index', compact('roles'));
    }

    public function create(): View
    {
        Gate::authorize('create', Role::class);

        $permissions = Permission::where('guard_name', 'web')->get();

        return view('permission::roles.create', compact('permissions'));
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        Gate::authorize('create', Role::class);

        $this->roleService->create($request->validated());

        return redirect()->route('roles.index')->with('success', __('permissions.role_created'));
    }

    public function show(Role $role): View
    {
        Gate::authorize('view', $role);

        return view('permission::roles.show', compact('role'));
    }

    public function edit(Role $role): View
    {
        Gate::authorize('update', $role);

        $permissions = Permission::where('guard_name', 'web')->get();

        return view('permission::roles.edit', compact('role', 'permissions'));
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        Gate::authorize('update', $role);

        $this->roleService->update($role->id, $request->validated());

        return redirect()->route('roles.index')->with('success', __('permissions.role_updated'));
    }

    public function destroy(Role $role): RedirectResponse
    {
        Gate::authorize('delete', $role);

        try {
            $this->roleService->delete($role->id);
        } catch (ValidationException $e) {
            return redirect()->back()->with('error', collect($e->errors())->flatten()->first());
        }

        return redirect()->route('roles.index')->with('success', __('permissions.role_deleted'));
    }

    public function trashed(): View
    {
        Gate::authorize('viewTrashed', Role::class);

        $roles = $this->roleService->getTrashed();

        return view('permission::roles.trashed', compact('roles'));
    }

    public function restore(string $ulid): RedirectResponse
    {
        $role = Role::withTrashed()->where('ulid', $ulid)->firstOrFail();

        Gate::authorize('restore', $role);

        $this->roleService->restore($ulid);

        return redirect()->route('roles.trashed')->with('success', __('permissions.role_restored'));
    }
}
