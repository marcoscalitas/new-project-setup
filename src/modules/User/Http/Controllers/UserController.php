<?php

namespace Modules\User\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Modules\User\Http\Requests\StoreUserRequest;
use Modules\User\Http\Requests\UpdateUserRequest;
use Modules\User\Http\Resources\UserResource;
use Modules\User\Models\User;
use Modules\User\Services\UserService;

class UserController
{
    public function __construct(private UserService $userService) {}

    public function index(): JsonResponse
    {
        Gate::authorize('viewAny', User::class);

        $users = $this->userService->getAll();

        return response()->json(UserResource::collection($users));
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        Gate::authorize('create', User::class);

        $user = $this->userService->create($request->validated());

        return response()->json(new UserResource($user), 201);
    }

    public function show(int $id): JsonResponse
    {
        $user = $this->userService->findById($id);

        Gate::authorize('view', $user);

        return response()->json(new UserResource($user));
    }

    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        Gate::authorize('update', User::findOrFail($id));

        $user = $this->userService->update($id, $request->validated());

        return response()->json(new UserResource($user));
    }

    public function destroy(int $id): JsonResponse
    {
        Gate::authorize('delete', User::findOrFail($id));

        $this->userService->delete($id);

        return response()->json(null, 204);
    }
}
