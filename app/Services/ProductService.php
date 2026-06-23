<?php

namespace App\Services;

use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Traits\UploadImageTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductService
{
    use UploadImageTrait;

    protected ProductRepositoryInterface $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function getAllProducts(array $relations = ['category']): Collection
    {
        return $this->productRepository->all(['*'], $relations);
    }

    public function paginateProducts(int $perPage = 15, array $relations = ['category']): LengthAwarePaginator
    {
        return $this->productRepository->paginate($perPage, $relations);
    }

    public function getProductById(int|string $id, array $relations = ['category']): ?Model
    {
        return $this->productRepository->findOrFail($id, ['*'], $relations);
    }

    public function createProduct(array $data): ?Model
    {
        // Handle single main image upload
        if (isset($data['image_file'])) {
            $data['image'] = $this->uploadImage($data['image_file'], 'products');
            unset($data['image_file']);
        }

        // Handle gallery images upload
        if (isset($data['gallery_files']) && is_array($data['gallery_files'])) {
            $galleryPaths = [];
            foreach ($data['gallery_files'] as $file) {
                $galleryPaths[] = $this->uploadImage($file, 'products/gallery');
            }
            $data['gallery'] = $galleryPaths;
            unset($data['gallery_files']);
        }

        return $this->productRepository->create($data);
    }

    public function updateProduct(int|string $id, array $data): bool
    {
        // Handle single main image upload
        if (isset($data['image_file'])) {
            $data['image'] = $this->uploadImage($data['image_file'], 'products');
            unset($data['image_file']);
        }

        // Handle gallery images upload
        if (isset($data['gallery_files']) && is_array($data['gallery_files'])) {
            $galleryPaths = [];
            foreach ($data['gallery_files'] as $file) {
                $galleryPaths[] = $this->uploadImage($file, 'products/gallery');
            }
            $data['gallery'] = $galleryPaths;
            unset($data['gallery_files']);
        }

        return $this->productRepository->update($id, $data);
    }

    public function deleteProduct(int|string $id): bool
    {
        return $this->productRepository->delete($id);
    }
}
