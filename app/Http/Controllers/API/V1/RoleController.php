<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\V1\StoreRoleRequest;
use App\Http\Resources\API\V1\RoleResource;
use App\Services\RolePermissionService;
use Illuminate\Http\JsonResponse;

class RoleController extends Controller
{
    protected RolePermissionService $rolePermissionService;

    public function __construct(RolePermissionService $rolePermissionService)
    {
        $this->rolePermissionService = $rolePermissionService;
    }

    public function index(): JsonResponse
    {
        $roles = $this->rolePermissionService->getAllRoles();
        return $this->success(RoleResource::collection($roles), 'Roles retrieved successfully');
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        $role = $this->rolePermissionService->createRole($request->validated());
        return $this->success(new RoleResource($role->load('permissions')), 'Role created successfully', 201);
    }

    public function show(string $id): JsonResponse
    {
        $role = $this->rolePermissionService->getRoleById($id);
        return $this->success(new RoleResource($role), 'Role retrieved successfully');
    }

    public function update(StoreRoleRequest $request, string $id): JsonResponse
    {
        $updated = $this->rolePermissionService->updateRole($id, $request->validated());
        if ($updated) {
            $role = $this->rolePermissionService->getRoleById($id);
            return $this->success(new RoleResource($role), 'Role updated successfully');
        }
        return $this->error('Failed to update role');
    }

    public function destroy(string $id): JsonResponse
    {
        $deleted = $this->rolePermissionService->deleteRole($id);
        if ($deleted) {
            return $this->success([], 'Role deleted successfully');
        }
        return $this->error('Failed to delete role');
    }
}
