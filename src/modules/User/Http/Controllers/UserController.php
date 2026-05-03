<?php

namespace Modules\User\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Modules\Core\Contracts\FileUploadInterface;
use Modules\User\Http\Requests\StoreUserRequest;
use Modules\User\Http\Requests\UpdateUserRequest;
use Modules\User\Http\Requests\UploadAvatarRequest;
use Modules\User\Http\Resources\UserResource;
use Modules\User\Models\User;
use Modules\User\Services\UserService;
use Modules\Permission\Models\Role;

class UserController
{
    public function __construct(
        private UserService $userService,
        private FileUploadInterface $media,
    ) {}

    public function index(Request $request): JsonResponse|\Illuminate\View\View
    {
        Gate::authorize('viewAny', User::class);

        $perPage = min((int) $request->query('per_page', 15), 100);

        if ($request->expectsJson()) {
            $users = $this->userService->getAll($perPage);
            return UserResource::collection($users)->response();
        }

        $users = $this->userService->getAll(null);
        return view('user::users.index', compact('users'));
    }

    public function create(): \Illuminate\View\View
    {
        Gate::authorize('create', User::class);

        $roles = Role::where('guard_name', 'web')->get();

        return view('user::users.create', compact('roles'));
    }

    public function store(StoreUserRequest $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        Gate::authorize('create', User::class);

        $user = $this->userService->create($request->validated());

        if (request()->expectsJson()) {
            return response()->json(new UserResource($user), 201);
        }

        return redirect()->route('users.index')->with('success', __('users.created'));
    }

    public function show(User $user): JsonResponse|\Illuminate\View\View
    {
        Gate::authorize('view', $user);

        if (request()->expectsJson()) {
            return response()->json(new UserResource($user));
        }

        return view('user::users.show', compact('user'));
    }

    public function edit(User $user): \Illuminate\View\View
    {
        Gate::authorize('update', $user);

        $roles = Role::where('guard_name', 'web')->get();

        return view('user::users.edit', compact('user', 'roles'));
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        Gate::authorize('update', $user);

        try {
            $user = $this->userService->update($user->id, $request->validated());
        } catch (ValidationException $e) {
            if (request()->expectsJson()) {
                throw $e;
            }
            return redirect()->back()->with('error', collect($e->errors())->flatten()->first());
        }

        if (request()->expectsJson()) {
            return response()->json(new UserResource($user));
        }

        return redirect()->route('users.index')->with('success', __('users.updated'));
    }

    public function destroy(User $user): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        Gate::authorize('delete', $user);

        try {
            $this->userService->delete($user->id);
        } catch (ValidationException $e) {
            if (request()->expectsJson()) {
                throw $e;
            }
            return redirect()->back()->with('error', collect($e->errors())->flatten()->first());
        }

        if (request()->expectsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('users.index')->with('success', __('users.deleted'));
    }

    public function uploadAvatar(UploadAvatarRequest $request, User $user): JsonResponse
    {
        Gate::authorize('update', $user);

        $this->media->upload($request->file('avatar'), 'avatar', $user);

        return (new UserResource($user->fresh()))->response();
    }

    public function deleteAvatar(User $user): JsonResponse
    {
        Gate::authorize('update', $user);

        $this->media->delete($user, 'avatar');

        return response()->json(null, 204);
    }

    public function trashed(): \Illuminate\View\View
    {
        Gate::authorize('viewTrashed', User::class);

        $users = $this->userService->getTrashed();

        return view('user::users.trashed', compact('users'));
    }

    public function restore(string $ulid): \Illuminate\Http\RedirectResponse
    {
        $user = User::withTrashed()->where('ulid', $ulid)->firstOrFail();

        Gate::authorize('restore', $user);

        $this->userService->restore($ulid);

        return redirect()->route('users.trashed')->with('success', __('users.restored'));
    }
}
