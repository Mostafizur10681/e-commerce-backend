<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\V1\PermissionResource;
use App\Services\RolePermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    protected RolePermissionService $rolePermissionService;

    public function __construct(RolePermissionService $rolePermissionService)
    {
        $this->rolePermissionService = $rolePermissionService;
    }

    public function index(): JsonResponse
    {
        $permissions = $this->rolePermissionService->getAllPermissions();
        return $this->success(PermissionResource::collection($permissions), 'Permissions retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
        ]);
        $permission = $this->rolePermissionService->createPermission($request->only('name'));
        return $this->success(new PermissionResource($permission), 'Permission created successfully', 201);
    }

    public function destroy(string $id): JsonResponse
    {
        $deleted = $this->rolePermissionService->deletePermission($id);
        if ($deleted) {
            return $this->success([], 'Permission deleted successfully');
        }
        return $this->error('Failed to delete permission');
    }
}
