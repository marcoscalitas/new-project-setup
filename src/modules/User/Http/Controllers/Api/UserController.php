<?php

namespace Modules\User\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Modules\User\Http\Requests\StoreUserRequest;
use Modules\User\Http\Requests\UpdateUserRequest;
use Modules\User\Http\Requests\UploadAvatarRequest;
use Modules\User\Http\Resources\UserResource;
use Modules\User\Models\User;
use Modules\User\Services\UserService;
use Shared\Media\Contracts\MediaRemover;
use Shared\Media\Contracts\MediaUploader;

class UserController
{
    public function __construct(
        private UserService $userService,
        private MediaUploader $mediaUploader,
        private MediaRemover $mediaRemover,
    ) {}

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', User::class);

        $perPage = min((int) $request->query('per_page', 15), 100);
        $users   = $this->userService->getAll(
            perPage:   $perPage,
            search:    $request->query('search'),
            sort:      $request->query('sort', 'name'),
            direction: $request->query('direction', 'asc'),
        );

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

    public function trashed(Request $request): JsonResponse
    {
        Gate::authorize('viewTrashed', User::class);

        $perPage = min((int) $request->query('per_page', 15), 100);
        $users   = User::onlyTrashed()->with('roles')->paginate($perPage);

        return UserResource::collection($users)->response();
    }

    public function restore(string $ulid): JsonResponse
    {
        $user = User::onlyTrashed()->where('ulid', $ulid)->firstOrFail();

        Gate::authorize('restore', $user);

        $this->userService->restore($ulid);

        return response()->json(new UserResource($user->fresh()));
    }

    public function uploadAvatar(UploadAvatarRequest $request, User $user): JsonResponse
    {
        Gate::authorize('update', $user);

        $this->mediaRemover->remove($user, 'avatar');
        $this->mediaUploader->upload($user, $request->file('avatar'), 'avatar');

        return (new UserResource($user->fresh()))->response();
    }

    public function deleteAvatar(User $user): JsonResponse
    {
        Gate::authorize('update', $user);

        $this->mediaRemover->remove($user, 'avatar');

        return response()->json(null, 204);
    }
}
