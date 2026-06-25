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
}
