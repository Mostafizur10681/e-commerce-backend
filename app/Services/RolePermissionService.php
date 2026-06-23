<?php

namespace App\Services;

use App\Repositories\Interfaces\RoleRepositoryInterface;
use App\Repositories\Interfaces\PermissionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class RolePermissionService
{
    protected RoleRepositoryInterface $roleRepository;
    protected PermissionRepositoryInterface $permissionRepository;

    public function __construct(
        RoleRepositoryInterface $roleRepository,
        PermissionRepositoryInterface $permissionRepository
    ) {
        $this->roleRepository = $roleRepository;
        $this->permissionRepository = $permissionRepository;
    }

    // Role methods
    public function getAllRoles(): Collection
    {
        return $this->roleRepository->all(['*'], ['permissions']);
    }

    public function getRoleById(int|string $id): ?Model
    {
        return $this->roleRepository->findOrFail($id, ['*'], ['permissions']);
    }

    public function createRole(array $data): ?Model
    {
        $role = $this->roleRepository->create([
            'name' => $data['name'],
            'guard_name' => $data['guard_name'] ?? 'web',
        ]);
        if ($role && isset($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }
        return $role;
    }

    public function updateRole(int|string $id, array $data): bool
    {
        $updated = $this->roleRepository->update($id, [
            'name' => $data['name']
        ]);
        if ($updated && isset($data['permissions'])) {
            $role = $this->roleRepository->find($id);
            $role->syncPermissions($data['permissions']);
        }
        return $updated;
    }

    public function deleteRole(int|string $id): bool
    {
        return $this->roleRepository->delete($id);
    }

    // Permission methods
    public function getAllPermissions(): Collection
    {
        return $this->permissionRepository->all();
    }

    public function createPermission(array $data): ?Model
    {
        return $this->permissionRepository->create([
            'name' => $data['name'],
            'guard_name' => $data['guard_name'] ?? 'web',
        ]);
    }

    public function deletePermission(int|string $id): bool
    {
        return $this->permissionRepository->delete($id);
    }
}
