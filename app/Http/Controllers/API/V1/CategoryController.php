<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\V1\StoreCategoryRequest;
use App\Http\Requests\API\V1\UpdateCategoryRequest;
use App\Http\Resources\API\V1\CategoryResource;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    protected CategoryService $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function index(Request $request): JsonResponse
    {
        if ($request->boolean('all')) {
            $categories = $this->categoryService->getAllCategories();
            return $this->success(CategoryResource::collection($categories), 'Categories retrieved successfully');
        }

        $perPage = $request->query('per_page', 15);
        $categories = $this->categoryService->paginateCategories($perPage);
        return $this->success(CategoryResource::collection($categories)->response()->getData(true), 'Categories retrieved successfully');
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = $this->categoryService->createCategory($request->validated());
        return $this->success(new CategoryResource($category), 'Category created successfully', 201);
    }

    public function show(string $id): JsonResponse
    {
        $category = $this->categoryService->getCategoryById($id);
        return $this->success(new CategoryResource($category), 'Category retrieved successfully');
    }

    public function update(UpdateCategoryRequest $request, string $id): JsonResponse
    {
        $updated = $this->categoryService->updateCategory($id, $request->validated());
        if ($updated) {
            $category = $this->categoryService->getCategoryById($id);
            return $this->success(new CategoryResource($category), 'Category updated successfully');
        }
        return $this->error('Failed to update category');
    }

    public function destroy(string $id): JsonResponse
    {
        $deleted = $this->categoryService->deleteCategory($id);
        if ($deleted) {
            return $this->success([], 'Category deleted successfully');
        }
        return $this->error('Failed to delete category');
    }
}
