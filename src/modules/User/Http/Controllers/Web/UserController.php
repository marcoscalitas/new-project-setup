<?php

namespace Modules\User\Http\Controllers\Web;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Modules\User\Http\Requests\StoreUserRequest;
use Modules\User\Http\Requests\UpdateUserRequest;
use Modules\User\Models\User;
use Modules\User\Services\UserService;
use Modules\Permission\Models\Role;

class UserController
{
    public function __construct(private UserService $userService) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', User::class);

        $users = $this->userService->getAll(
            perPage:   15,
            search:    $request->query('search'),
            sort:      $request->query('sort', 'name'),
            direction: $request->query('direction', 'asc'),
        );

        return view('user::users.index', compact('users'));
    }

    public function create(): View
    {
        Gate::authorize('create', User::class);

        $roles = Role::where('guard_name', 'web')->get();

        return view('user::users.create', compact('roles'));
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        Gate::authorize('create', User::class);

        $this->userService->create($request->validated());

        return redirect()->route('users.index')->with('success', __('users.created'));
    }

    public function show(User $user): View
    {
        Gate::authorize('view', $user);

        return view('user::users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        Gate::authorize('update', $user);

        $roles = Role::where('guard_name', 'web')->get();

        return view('user::users.edit', compact('user', 'roles'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        Gate::authorize('update', $user);

        try {
            $this->userService->update($user->id, $request->validated());
        } catch (ValidationException $e) {
            return redirect()->back()->with('error', collect($e->errors())->flatten()->first());
        }

        return redirect()->route('users.index')->with('success', __('users.updated'));
    }

    public function destroy(User $user): RedirectResponse
    {
        Gate::authorize('delete', $user);

        try {
            $this->userService->delete($user->id);
        } catch (ValidationException $e) {
            return redirect()->back()->with('error', collect($e->errors())->flatten()->first());
        }

        return redirect()->route('users.index')->with('success', __('users.deleted'));
    }

    public function trashed(Request $request): View
    {
        Gate::authorize('viewTrashed', User::class);

        $users = $this->userService->getTrashed(perPage: 15);

        return view('user::users.trashed', compact('users'));
    }

    public function restore(string $ulid): RedirectResponse
    {
        $user = User::withTrashed()->where('ulid', $ulid)->firstOrFail();

        Gate::authorize('restore', $user);

        $this->userService->restore($ulid);

        return redirect()->route('users.trashed')->with('success', __('users.restored'));
    }
}
