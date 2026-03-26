<?php

namespace Modules\User\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\User\Http\Requests\StoreUserRequest;
use Modules\User\Http\Requests\UpdateUserRequest;
use Modules\User\Http\Resources\UserResource;
use Modules\User\Services\UserService;

class UserController
{
    public function __construct(private UserService $userService) {}

    public function index(): JsonResponse
    {
        $users = $this->userService->getAll();

        return response()->json(UserResource::collection($users));
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->userService->create($request->validated());

        return response()->json(new UserResource($user), 201);
    }

    public function show(int $id): JsonResponse
    {
        $user = $this->userService->findById($id);

        return response()->json(new UserResource($user));
    }

    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        $user = $this->userService->update($id, $request->validated());

        return response()->json(new UserResource($user));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->userService->delete($id);

        return response()->json(null, 204);
    }
}
