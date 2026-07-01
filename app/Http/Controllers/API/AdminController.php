<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\AdminService;
use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use App\Models\ProductImage;
use App\Models\Partner;
use App\Models\FAQCategory;
use App\Traits\UploadImageTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    use UploadImageTrait;

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
        $products = Product::with(['category', 'images'])->paginate(15);
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
            'category_id' => 'nullable|exists:categories,id',
            'status' => 'nullable|boolean',
            'image' => 'nullable|string',
            'gallery' => 'nullable|array',
            'sub_category' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:255',
            'tax' => 'nullable|numeric|min:0|max:100',
            'discount' => 'nullable|numeric|min:0|max:100',
            'unit' => 'nullable|string|max:50',
            'stock_status' => 'nullable|string|max:50',
            'featured' => 'nullable|boolean',
            'best_seller' => 'nullable|boolean',
            'organic' => 'nullable|boolean',
            'new_arrival' => 'nullable|boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string|max:255',
        ]);

        $galleryPaths = [];
        $galleryInput = $request->input('gallery', []);
        if (is_array($galleryInput)) {
            foreach ($galleryInput as $imgItem) {
                if (empty($imgItem)) continue;
                if (str_starts_with($imgItem, 'data:image/')) {
                    $galleryPaths[] = $this->uploadBase64Image($imgItem, 'products/gallery');
                } else {
                    $path = $imgItem;
                    if (str_contains($imgItem, '/storage/')) {
                        $path = substr($imgItem, strpos($imgItem, '/storage/') + 9);
                    }
                    $galleryPaths[] = $path;
                }
            }
        }

        $mainImagePath = !empty($galleryPaths) ? $galleryPaths[0] : null;

        unset($validated['image'], $validated['gallery']);
        $validated['slug'] = Str::slug($validated['name']) . '-' . uniqid();
        $product = Product::create($validated);

        if ($product && !empty($galleryPaths)) {
            foreach ($galleryPaths as $path) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $path,
                ]);
            }
        }

        return $this->success($product->load('images'), 'Product created successfully', 201);
    }

    public function productsShow(string $id): JsonResponse
    {
        $product = Product::with(['category', 'images'])->findOrFail($id);
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
            'image' => 'nullable|string',
            'gallery' => 'nullable|array',
            'sub_category' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:255',
            'tax' => 'nullable|numeric|min:0|max:100',
            'discount' => 'nullable|numeric|min:0|max:100',
            'unit' => 'nullable|string|max:50',
            'stock_status' => 'nullable|string|max:50',
            'featured' => 'nullable|boolean',
            'best_seller' => 'nullable|boolean',
            'organic' => 'nullable|boolean',
            'new_arrival' => 'nullable|boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string|max:255',
        ]);

        $galleryPaths = [];
        $galleryInput = $request->input('gallery', []);
        if (is_array($galleryInput)) {
            foreach ($galleryInput as $imgItem) {
                if (empty($imgItem)) continue;
                if (str_starts_with($imgItem, 'data:image/')) {
                    $galleryPaths[] = $this->uploadBase64Image($imgItem, 'products/gallery');
                } else {
                    $path = $imgItem;
                    if (str_contains($imgItem, '/storage/')) {
                        $path = substr($imgItem, strpos($imgItem, '/storage/') + 9);
                    }
                    $galleryPaths[] = $path;
                }
            }
        }

        $mainImagePath = !empty($galleryPaths) ? $galleryPaths[0] : null;

        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']) . '-' . uniqid();
        }

        unset($validated['image'], $validated['gallery']);
        $product->update(array_filter($validated, function ($val) {
            return $val !== null;
        }));

        ProductImage::where('product_id', $product->id)->delete();
        foreach ($galleryPaths as $path) {
            ProductImage::create([
                'product_id' => $product->id,
                'image_path' => $path,
            ]);
        }

        return $this->success($product->load('images'), 'Product updated successfully');
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

        if (!empty($validated['image'])) {
            if (str_starts_with($validated['image'], 'data:image/') || strlen($validated['image']) > 100) {
                $validated['image'] = $this->uploadBase64Image($validated['image'], 'categories');
            }
        }

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

        if (!empty($validated['image'])) {
            if (str_starts_with($validated['image'], 'data:image/') || strlen($validated['image']) > 100) {
                $validated['image'] = $this->uploadBase64Image($validated['image'], 'categories');
            }
        }

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

    // FAQ Category Management
    public function faqCategoriesIndex(): JsonResponse
    {
        $faqCategories = FAQCategory::all();
        return $this->success($faqCategories, 'FAQ Categories retrieved successfully');
    }

    public function faqCategoriesStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|boolean',
        ]);

        $faqCategory = FAQCategory::create($validated);

        return $this->success($faqCategory, 'FAQ Category created successfully', 201);
    }

    public function faqCategoriesShow(string $id): JsonResponse
    {
        $faqCategory = FAQCategory::findOrFail($id);
        return $this->success($faqCategory, 'FAQ Category details retrieved');
    }

    public function faqCategoriesUpdate(Request $request, string $id): JsonResponse
    {
        $faqCategory = FAQCategory::findOrFail($id);
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|boolean',
        ]);

        $faqCategory->update(array_filter($validated, function ($val) {
            return $val !== null;
        }));
        return $this->success($faqCategory, 'FAQ Category updated successfully');
    }

    public function faqCategoriesDestroy(string $id): JsonResponse
    {
        $faqCategory = FAQCategory::findOrFail($id);
        $faqCategory->delete();
        return $this->success([], 'FAQ Category deleted successfully');
    }

    // Partner Management
    public function partnersIndex(): JsonResponse
    {
        $partners = Partner::all();
        return $this->success($partners, 'Partners retrieved successfully');
    }

    public function partnersStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'website' => 'nullable|string|url|max:255',
            'logo' => 'nullable|string',
            'image' => 'nullable|string',
            'description' => 'nullable|string',
            'status' => 'nullable|boolean',
        ]);

        if (!empty($validated['logo'])) {
            if (str_starts_with($validated['logo'], 'data:image/') || strlen($validated['logo']) > 100) {
                $validated['logo'] = $this->uploadBase64Image($validated['logo'], 'partners/logos');
            }
        }

        if (!empty($validated['image'])) {
            if (str_starts_with($validated['image'], 'data:image/') || strlen($validated['image']) > 100) {
                $validated['image'] = $this->uploadBase64Image($validated['image'], 'partners/images');
            }
        }

        $partner = Partner::create($validated);

        return $this->success($partner, 'Partner created successfully', 201);
    }

    public function partnersShow(string $id): JsonResponse
    {
        $partner = Partner::findOrFail($id);
        return $this->success($partner, 'Partner details retrieved');
    }

    public function partnersUpdate(Request $request, string $id): JsonResponse
    {
        $partner = Partner::findOrFail($id);
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'website' => 'nullable|string|url|max:255',
            'logo' => 'nullable|string',
            'image' => 'nullable|string',
            'description' => 'nullable|string',
            'status' => 'nullable|boolean',
        ]);

        if (!empty($validated['logo'])) {
            if (str_starts_with($validated['logo'], 'data:image/') || strlen($validated['logo']) > 100) {
                $validated['logo'] = $this->uploadBase64Image($validated['logo'], 'partners/logos');
            }
        }

        if (!empty($validated['image'])) {
            if (str_starts_with($validated['image'], 'data:image/') || strlen($validated['image']) > 100) {
                $validated['image'] = $this->uploadBase64Image($validated['image'], 'partners/images');
            }
        }

        $partner->update(array_filter($validated, function ($val) {
            return $val !== null;
        }));

        return $this->success($partner, 'Partner updated successfully');
    }

    public function partnersDestroy(string $id): JsonResponse
    {
        $partner = Partner::findOrFail($id);
        $partner->delete();
        return $this->success([], 'Partner deleted successfully');
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
