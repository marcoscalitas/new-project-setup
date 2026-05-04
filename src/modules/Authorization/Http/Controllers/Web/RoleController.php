<?php

namespace Modules\Authorization\Http\Controllers\Web;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Modules\Authorization\Http\Requests\StoreRoleRequest;
use Modules\Authorization\Http\Requests\UpdateRoleRequest;
use Modules\Authorization\Models\Permission;
use Modules\Authorization\Models\Role;
use Modules\Authorization\Services\RoleService;

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

        return view('authorization::roles.index', compact('roles'));
    }

    public function create(): View
    {
        Gate::authorize('create', Role::class);

        $permissions = Permission::where('guard_name', 'web')->get();

        return view('authorization::roles.create', compact('permissions'));
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

        return view('authorization::roles.show', compact('role'));
    }

    public function edit(Role $role): View
    {
        Gate::authorize('update', $role);

        $permissions = Permission::where('guard_name', 'web')->get();

        return view('authorization::roles.edit', compact('role', 'permissions'));
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

    public function trashed(Request $request): View
    {
        Gate::authorize('viewTrashed', Role::class);

        $roles = $this->roleService->getTrashed(perPage: 15);

        return view('authorization::roles.trashed', compact('roles'));
    }

    public function restore(string $ulid): RedirectResponse
    {
        $role = Role::withTrashed()->where('ulid', $ulid)->firstOrFail();

        Gate::authorize('restore', $role);

        $this->roleService->restore($ulid);

        return redirect()->route('roles.trashed')->with('success', __('permissions.role_restored'));
    }
}
