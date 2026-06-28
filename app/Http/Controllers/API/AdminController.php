<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\AdminService;
use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    protected AdminService $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }

    public function dashboard(): JsonResponse
    {
        $stats = $this->adminService->getDashboardStats();
        return $this->success($stats, 'Dashboard statistics retrieved successfully');
    }

    // Product Management
    public function productsIndex(Request $request): JsonResponse
    {
        $products = Product::with('category')->paginate(15);
        return $this->success($products, 'Products retrieved successfully');
    }

    public function productsStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'SKU' => 'required|string|unique:products',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'status' => 'nullable|boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']) . '-' . uniqid();
        $product = Product::create($validated);

        return $this->success($product, 'Product created successfully', 201);
    }

    public function productsShow(string $id): JsonResponse
    {
        $product = Product::with('category')->findOrFail($id);
        return $this->success($product, 'Product details retrieved');
    }

    public function productsUpdate(Request $request, string $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'SKU' => 'nullable|string|unique:products,SKU,' . $id,
            'stock' => 'nullable|integer|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'status' => 'nullable|boolean',
        ]);

        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']) . '-' . uniqid();
        }

        $product->update(array_filter($validated));
        return $this->success($product, 'Product updated successfully');
    }

    public function productsDestroy(string $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return $this->success([], 'Product deleted successfully');
    }

    // Category Management
    public function categoriesIndex(): JsonResponse
    {
        $categories = Category::with('parent')->get();
        return $this->success($categories, 'Categories retrieved successfully');
    }

    public function categoriesStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'status' => 'nullable|boolean',
        ]);

        $category = Category::create($validated);

        return $this->success($category, 'Category created successfully', 201);
    }

    public function categoriesShow(string $id): JsonResponse
    {
        $category = Category::with('parent')->findOrFail($id);
        return $this->success($category, 'Category details retrieved');
    }

    public function categoriesUpdate(Request $request, string $id): JsonResponse
    {
        $category = Category::findOrFail($id);
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'status' => 'nullable|boolean',
        ]);

        $category->update(array_filter($validated, function ($val) {
            return $val !== null;
        }));
        return $this->success($category, 'Category updated successfully');
    }

    public function categoriesDestroy(string $id): JsonResponse
    {
        $category = Category::findOrFail($id);
        $category->delete();
        return $this->success([], 'Category deleted successfully');
    }

    // Brand Management
    public function brandsIndex(): JsonResponse
    {
        $brands = $this->adminService->paginateBrands(50);
        return $this->success($brands, 'Brands retrieved successfully');
    }

    public function brandsStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'nullable|string',
            'status' => 'nullable|boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $brand = $this->adminService->createBrand($validated);

        return $this->success($brand, 'Brand created successfully', 201);
    }

    public function brandsUpdate(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'logo' => 'nullable|string',
            'status' => 'nullable|boolean',
        ]);

        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $this->adminService->updateBrand($id, array_filter($validated));
        return $this->success([], 'Brand updated successfully');
    }

    public function brandsDestroy(string $id): JsonResponse
    {
        $this->adminService->deleteBrand($id);
        return $this->success([], 'Brand deleted successfully');
    }

    // Order Management
    public function ordersIndex(): JsonResponse
    {
        $orders = Order::with('user')->paginate(15);
        return $this->success($orders, 'Orders retrieved successfully');
    }

    public function ordersShow(string $id): JsonResponse
    {
        $order = Order::with('user', 'items.product')->findOrFail($id);
        return $this->success($order, 'Order details retrieved');
    }

    public function ordersUpdate(Request $request, string $id): JsonResponse
    {
        $order = Order::findOrFail($id);
        $validated = $request->validate([
            'status' => 'required|string',
            'payment_status' => 'nullable|string',
        ]);

        $order->update($validated);
        return $this->success($order, 'Order updated successfully');
    }

    public function ordersUpdateStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'status' => 'required|string',
        ]);

        $order = Order::findOrFail($validated['order_id']);
        $order->update(['status' => $validated['status']]);

        return $this->success($order, 'Order status updated successfully');
    }

    // Customer Management
    public function customersIndex(): JsonResponse
    {
        $customers = $this->adminService->paginateCustomers();
        return $this->success($customers, 'Customers list retrieved');
    }

    public function customersShow(string $id): JsonResponse
    {
        $customer = $this->adminService->getCustomerDetails($id);
        return $this->success($customer, 'Customer details retrieved');
    }

    public function customersUpdate(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $this->adminService->updateCustomer($id, array_filter($validated));
        return $this->success([], 'Customer updated successfully');
    }

    public function customersToggleBlock(string $id): JsonResponse
    {
        $user = $this->adminService->toggleCustomerBlock($id);
        return $this->success($user, "Customer block status toggled. Current status: {$user->status}");
    }

    // General Users CRUD
    public function usersIndex(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', 15);
        $users = $this->adminService->paginateUsers($perPage);
        return $this->success($users, 'Users retrieved successfully');
    }

    public function usersUpdate(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'nullable|string|in:admin,customer,vendor,staff',
            'status' => 'nullable|string|in:active,blocked',
            'phone' => 'nullable|string|max:20',
        ]);

        $user = $this->adminService->updateUser($id, $validated);
        return $this->success($user, 'User updated successfully');
    }

    public function usersDestroy(string $id): JsonResponse
    {
        $this->adminService->deleteUser($id);
        return $this->success([], 'User deleted successfully');
    }
}
