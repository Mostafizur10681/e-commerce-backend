<?php

namespace App\Services;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Order;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Repositories\Interfaces\BrandRepositoryInterface;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class AdminService
{
    protected CategoryRepositoryInterface $categoryRepository;
    protected ProductRepositoryInterface $productRepository;
    protected BrandRepositoryInterface $brandRepository;
    protected OrderRepositoryInterface $orderRepository;
    protected UserRepositoryInterface $userRepository;

    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        ProductRepositoryInterface $productRepository,
        BrandRepositoryInterface $brandRepository,
        OrderRepositoryInterface $orderRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->productRepository = $productRepository;
        $this->brandRepository = $brandRepository;
        $this->orderRepository = $orderRepository;
        $this->userRepository = $userRepository;
    }

    public function getDashboardStats(): array
    {
        return [
            'total_users' => User::count(),
            'total_customers' => User::where('role', 'customer')->count(),
            'total_products' => Product::count(),
            'total_categories' => Category::count(),
            'total_brands' => Brand::count(),
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'completed_orders' => Order::where('status', 'completed')->count(),
        ];
    }

    // Brands CRUD
    public function paginateBrands(int $perPage = 15): LengthAwarePaginator
    {
        return $this->brandRepository->paginate($perPage);
    }

    public function createBrand(array $data): Brand
    {
        /** @var Brand */
        return $this->brandRepository->create($data);
    }

    public function updateBrand(int|string $id, array $data): bool
    {
        return $this->brandRepository->update($id, $data);
    }

    public function deleteBrand(int|string $id): bool
    {
        return $this->brandRepository->delete($id);
    }

    // Customer status (Block/Unblock)
    public function paginateCustomers(int $perPage = 15): LengthAwarePaginator
    {
        return User::where('role', 'customer')->paginate($perPage);
    }

    public function getCustomerDetails(int|string $id): User
    {
        return User::where('role', 'customer')->with('customerProfile', 'addresses')->findOrFail($id);
    }

    public function updateCustomer(int|string $id, array $data): bool
    {
        $user = User::where('role', 'customer')->findOrFail($id);
        return $user->update($data);
    }

    public function toggleCustomerBlock(int|string $id): User
    {
        $user = User::where('role', 'customer')->findOrFail($id);
        $newStatus = $user->status === 'blocked' ? 'active' : 'blocked';
        $user->update(['status' => $newStatus]);
        return $user;
    }

    // General Users CRUD
    public function paginateUsers(int $perPage = 15): LengthAwarePaginator
    {
        return User::with('customerProfile', 'adminProfile')->paginate($perPage);
    }

    public function updateUser(int|string $id, array $data): User
    {
        $user = User::findOrFail($id);
        
        // Handle password hashing if updated
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        
        $user->update(array_filter($data, function($val) {
            return $val !== null;
        }));

        // Handle role syncing if Spatie HasRoles trait is used
        if (isset($data['role'])) {
            $roleName = ucfirst($data['role']);
            if (strtolower($roleName) === 'vendor' || strtolower($roleName) === 'seller') {
                $roleName = 'Seller';
            }
            $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $user->syncRoles([$role]);
        }

        return $user->load('customerProfile', 'adminProfile');
    }

    public function deleteUser(int|string $id): bool
    {
        $user = User::findOrFail($id);
        
        // Revoke tokens
        $user->tokens()->delete();
        
        // Delete profile associations if necessary
        $user->customerProfile()->delete();
        $user->adminProfile()->delete();
        
        return $user->delete();
    }
}
