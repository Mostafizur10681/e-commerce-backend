<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\V1\StoreProductRequest;
use App\Http\Requests\API\V1\UpdateProductRequest;
use App\Http\Resources\API\V1\ProductResource;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', 15);
        $products = $this->productService->paginateProducts($perPage);
        return $this->success(ProductResource::collection($products)->response()->getData(true), 'Products retrieved successfully');
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->productService->createProduct($request->validated());
        return $this->success(new ProductResource($product), 'Product created successfully', 201);
    }

    public function show(string $id): JsonResponse
    {
        $product = $this->productService->getProductById($id);
        return $this->success(new ProductResource($product), 'Product retrieved successfully');
    }

    public function update(UpdateProductRequest $request, string $id): JsonResponse
    {
        $updated = $this->productService->updateProduct($id, $request->validated());
        if ($updated) {
            $product = $this->productService->getProductById($id);
            return $this->success(new ProductResource($product), 'Product updated successfully');
        }
        return $this->error('Failed to update product');
    }

    public function destroy(string $id): JsonResponse
    {
        $deleted = $this->productService->deleteProduct($id);
        if ($deleted) {
            return $this->success([], 'Product deleted successfully');
        }
        return $this->error('Failed to delete product');
    }
}
