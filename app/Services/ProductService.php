<?php

namespace App\Services;

use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Traits\UploadImageTrait;
use App\Models\ProductImage;
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
        return $this->productRepository->all(['*'], array_merge($relations, ['images']));
    }

    public function paginateProducts(int $perPage = 15, array $relations = ['category']): LengthAwarePaginator
    {
        return $this->productRepository->paginate($perPage, array_merge($relations, ['images']));
    }

    public function getProductById(int|string $id, array $relations = ['category']): ?Model
    {
        return $this->productRepository->findOrFail($id, ['*'], array_merge($relations, ['images']));
    }

    public function createProduct(array $data): ?Model
    {
        // Handle single main image upload
        if (isset($data['image_file'])) {
            $data['image'] = $this->uploadImage($data['image_file'], 'products');
            unset($data['image_file']);
        }

        // Handle gallery images upload and store in separate table
        $galleryPaths = [];
        if (isset($data['gallery_files']) && is_array($data['gallery_files'])) {
            foreach ($data['gallery_files'] as $file) {
                $galleryPaths[] = $this->uploadImage($file, 'products/gallery');
            }
            unset($data['gallery_files']);
        }

        $product = $this->productRepository->create($data);
        if ($product && !empty($galleryPaths)) {
            $this->storeImages($product->id, $galleryPaths);
        }
        return $product;
    }

    public function updateProduct(int|string $id, array $data): bool
    {
        // Handle single main image upload
        if (isset($data['image_file'])) {
            $data['image'] = $this->uploadImage($data['image_file'], 'products');
            unset($data['image_file']);
        }

        // Handle gallery images upload
        $galleryPaths = [];
        if (isset($data['gallery_files']) && is_array($data['gallery_files'])) {
            foreach ($data['gallery_files'] as $file) {
                $galleryPaths[] = $this->uploadImage($file, 'products/gallery');
            }
            unset($data['gallery_files']);
        }

        // Update product data
        $updated = $this->productRepository->update($id, $data);
        if ($updated) {
            // Remove old images
            ProductImage::where('product_id', $id)->delete();
            if (!empty($galleryPaths)) {
                $this->storeImages($id, $galleryPaths);
            }
        }
        return $updated;
    }

    /**
     * Store images for a product.
     *
     * @param int $productId
     * @param array $images
     */
    public function storeImages(int $productId, array $images): void
    {
        foreach ($images as $path) {
            ProductImage::create([
                'product_id' => $productId,
                'image_path' => $path,
            ]);
        }
    }

}
