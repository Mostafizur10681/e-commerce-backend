<?php

namespace App\Services;

use App\Repositories\Interfaces\CustomerRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class CustomerService
{
    protected CustomerRepositoryInterface $customerRepository;

    public function __construct(CustomerRepositoryInterface $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    public function getAllCustomers(array $relations = ['user']): Collection
    {
        return $this->customerRepository->all(['*'], $relations);
    }

    public function paginateCustomers(int $perPage = 15, array $relations = ['user']): LengthAwarePaginator
    {
        return $this->customerRepository->paginate($perPage, $relations);
    }

    public function getCustomerById(int|string $id, array $relations = ['user']): ?Model
    {
        return $this->customerRepository->findOrFail($id, ['*'], $relations);
    }

    public function createCustomer(array $data): ?Model
    {
        return $this->customerRepository->create($data);
    }

    public function updateCustomer(int|string $id, array $data): bool
    {
        return $this->customerRepository->update($id, $data);
    }

    public function deleteCustomer(int|string $id): bool
    {
        return $this->customerRepository->delete($id);
    }
}
