<?php

namespace App\Services;

use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Traits\UploadImageTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class CategoryService
{
    use UploadImageTrait;

    protected CategoryRepositoryInterface $categoryRepository;

    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function getAllCategories(): Collection
    {
        return $this->categoryRepository->all(['*'], ['parent', 'children']);
    }

    public function paginateCategories(int $perPage = 15): LengthAwarePaginator
    {
        return $this->categoryRepository->paginate($perPage, ['parent', 'children']);
    }

    public function getCategoryById(int|string $id): ?Model
    {
        return $this->categoryRepository->findOrFail($id, ['*'], ['parent', 'children']);
    }

    public function createCategory(array $data): ?Model
    {
        if (isset($data['image_file'])) {
            $data['image'] = $this->uploadImage($data['image_file'], 'categories');
            unset($data['image_file']);
        }
        return $this->categoryRepository->create($data);
    }

    public function updateCategory(int|string $id, array $data): bool
    {
        if (isset($data['image_file'])) {
            $data['image'] = $this->uploadImage($data['image_file'], 'categories');
            unset($data['image_file']);
        }
        return $this->categoryRepository->update($id, $data);
    }

    public function deleteCategory(int|string $id): bool
    {
        return $this->categoryRepository->delete($id);
    }
}
