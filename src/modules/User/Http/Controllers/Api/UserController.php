<?php

namespace Modules\User\Http\Controllers\Api;

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

class UserController
{
    public function __construct(
        private UserService $userService,
        private FileUploadInterface $media,
    ) {}

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', User::class);

        $perPage = min((int) $request->query('per_page', 15), 100);
        $users   = $this->userService->getAll($perPage);

        return UserResource::collection($users)->response();
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        Gate::authorize('create', User::class);

        $user = $this->userService->create($request->validated());

        return response()->json(new UserResource($user), 201);
    }

    public function show(User $user): JsonResponse
    {
        Gate::authorize('view', $user);

        return response()->json(new UserResource($user));
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        Gate::authorize('update', $user);

        $user = $this->userService->update($user->id, $request->validated());

        return response()->json(new UserResource($user));
    }

    public function destroy(User $user): JsonResponse
    {
        Gate::authorize('delete', $user);

        $this->userService->delete($user->id);

        return response()->json(null, 204);
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
}
