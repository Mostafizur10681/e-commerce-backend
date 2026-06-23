<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\V1\StoreUserRequest;
use App\Http\Requests\API\V1\UpdateUserRequest;
use App\Http\Resources\API\V1\UserResource;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', 15);
        $users = $this->userService->paginateUsers($perPage);
        return $this->success(UserResource::collection($users)->response()->getData(true), 'Users retrieved successfully');
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->userService->createUser($request->validated());
        return $this->success(new UserResource($user->load('roles')), 'User created successfully', 201);
    }

    public function show(string $id): JsonResponse
    {
        $user = $this->userService->getUserById($id);
        return $this->success(new UserResource($user), 'User retrieved successfully');
    }

    public function update(UpdateUserRequest $request, string $id): JsonResponse
    {
        $updated = $this->userService->updateUser($id, $request->validated());
        if ($updated) {
            $user = $this->userService->getUserById($id);
            return $this->success(new UserResource($user), 'User updated successfully');
        }
        return $this->error('Failed to update user');
    }

    public function destroy(string $id): JsonResponse
    {
        $deleted = $this->userService->deleteUser($id);
        if ($deleted) {
            return $this->success([], 'User deleted successfully');
        }
        return $this->error('Failed to delete user');
    }
}
