<?php

namespace Modules\User\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Modules\ActivityLog\Http\Resources\ActivityLogResource;
use Modules\ActivityLog\Services\ActivityLogService;
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
        private ActivityLogService $activityLogService,
    ) {}

    public function index(Request $request): JsonResponse|\Illuminate\View\View
    {
        Gate::authorize('viewAny', User::class);

        $perPage = min((int) $request->query('per_page', 15), 100);
        $users = $this->userService->getAll($perPage);

        if ($request->expectsJson()) {
            return UserResource::collection($users)->response();
        }

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

        return redirect()->route('users.index')->with('success', 'User created.');
    }

    public function show(int $id): JsonResponse|\Illuminate\View\View
    {
        $user = $this->userService->findById($id);

        Gate::authorize('view', $user);

        if (request()->expectsJson()) {
            return response()->json(new UserResource($user));
        }

        return view('user::users.show', compact('user'));
    }

    public function edit(int $id): \Illuminate\View\View
    {
        $user = User::findOrFail($id);

        Gate::authorize('update', $user);

        $roles = Role::where('guard_name', 'web')->get();

        return view('user::users.edit', compact('user', 'roles'));
    }

    public function update(UpdateUserRequest $request, int $id): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        Gate::authorize('update', User::findOrFail($id));

        $user = $this->userService->update($id, $request->validated());

        if (request()->expectsJson()) {
            return response()->json(new UserResource($user));
        }

        return redirect()->route('users.index')->with('success', 'User updated.');
    }

    public function destroy(int $id): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        Gate::authorize('delete', User::findOrFail($id));

        $this->userService->delete($id);

        if (request()->expectsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('users.index')->with('success', 'User deleted.');
    }

    public function uploadAvatar(UploadAvatarRequest $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        Gate::authorize('update', $user);

        $user->addMediaFromRequest('avatar')
            ->toMediaCollection('avatar');

        return (new UserResource($user->fresh()))->response();
    }

    public function deleteAvatar(int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        Gate::authorize('update', $user);

        $user->clearMediaCollection('avatar');

        return response()->json(null, 204);
    }

    public function activity(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        if ($request->user()->id !== $user->id) {
            Gate::authorize('viewAny', \Spatie\Activitylog\Models\Activity::class);
        }

        $perPage = min((int) $request->query('per_page', 15), 100);
        $logs = $this->activityLogService->getForUser($user->id, $perPage);

        return ActivityLogResource::collection($logs)->response();
    }
}
