<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\V1\StoreCustomerRequest;
use App\Http\Requests\API\V1\UpdateCustomerRequest;
use App\Http\Resources\API\V1\CustomerResource;
use App\Services\CustomerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    protected CustomerService $customerService;

    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    public function index(Request $request): JsonResponse
    {
        // Admin or Editor can view all customer profiles
        $user = $request->user();
        if (!$user->hasRole(['Admin', 'Editor'])) {
            return $this->error('Unauthorized to view customer list', [], 403);
        }

        $perPage = $request->query('per_page', 15);
        $customers = $this->customerService->paginateCustomers($perPage);
        return $this->success(CustomerResource::collection($customers)->response()->getData(true), 'Customers retrieved successfully');
    }

    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $customer = $this->customerService->createCustomer($request->validated());
        return $this->success(new CustomerResource($customer), 'Customer created successfully', 201);
    }

    public function show(string $id, Request $request): JsonResponse
    {
        $customer = $this->customerService->getCustomerById($id);
        
        // Authorization check: User can only see their own customer profile, unless Admin or Editor
        $user = $request->user();
        if ($customer->user_id !== $user->id && !$user->hasRole(['Admin', 'Editor'])) {
            return $this->error('Unauthorized to view this customer profile', [], 403);
        }

        return $this->success(new CustomerResource($customer), 'Customer retrieved successfully');
    }

    public function update(UpdateCustomerRequest $request, string $id): JsonResponse
    {
        $customer = $this->customerService->getCustomerById($id);
        
        // Authorization check: User can only see their own customer profile, unless Admin or Editor
        $user = $request->user();
        if ($customer->user_id !== $user->id && !$user->hasRole(['Admin', 'Editor'])) {
            return $this->error('Unauthorized to update this customer profile', [], 403);
        }

        $updated = $this->customerService->updateCustomer($id, $request->validated());
        if ($updated) {
            $customer = $this->customerService->getCustomerById($id);
            return $this->success(new CustomerResource($customer), 'Customer updated successfully');
        }
        return $this->error('Failed to update customer');
    }

    public function destroy(string $id, Request $request): JsonResponse
    {
        // Admin only can delete customer records
        $user = $request->user();
        if (!$user->hasRole('Admin')) {
            return $this->error('Unauthorized to delete customer profiles', [], 403);
        }

        $deleted = $this->customerService->deleteCustomer($id);
        if ($deleted) {
            return $this->success([], 'Customer deleted successfully');
        }
        return $this->error('Failed to delete customer');
    }
}
