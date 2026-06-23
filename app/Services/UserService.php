<?php

namespace App\Services;

use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class UserService
{
    protected UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getAllUsers(): Collection
    {
        return $this->userRepository->all(['*'], ['roles']);
    }

    public function paginateUsers(int $perPage = 15): LengthAwarePaginator
    {
        return $this->userRepository->paginate($perPage, ['roles']);
    }

    public function getUserById(int|string $id): ?Model
    {
        return $this->userRepository->findOrFail($id, ['*'], ['roles']);
    }

    public function createUser(array $data): ?Model
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        $user = $this->userRepository->create($data);
        if ($user && isset($data['roles'])) {
            $user->syncRoles($data['roles']);
        }
        return $user;
    }

    public function updateUser(int|string $id, array $data): bool
    {
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        $updated = $this->userRepository->update($id, $data);
        if ($updated && isset($data['roles'])) {
            $user = $this->userRepository->find($id);
            $user->syncRoles($data['roles']);
        }
        return $updated;
    }

    public function deleteUser(int|string $id): bool
    {
        return $this->userRepository->delete($id);
    }
}
