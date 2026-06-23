<?php

namespace App\Services;

use App\Repositories\Interfaces\ReviewRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class ReviewService
{
    protected ReviewRepositoryInterface $reviewRepository;

    public function __construct(ReviewRepositoryInterface $reviewRepository)
    {
        $this->reviewRepository = $reviewRepository;
    }

    public function getAllReviews(array $relations = ['user', 'product']): Collection
    {
        return $this->reviewRepository->all(['*'], $relations);
    }

    public function getProductReviews(int|string $productId, int $perPage = 15): LengthAwarePaginator
    {
        // Only active/approved reviews should be returned to frontends
        return \App\Models\Review::where('product_id', $productId)
            ->where('status', true)
            ->with('user')
            ->paginate($perPage);
    }

    public function paginateReviews(int $perPage = 15, array $relations = ['user', 'product']): LengthAwarePaginator
    {
        return $this->reviewRepository->paginate($perPage, $relations);
    }

    public function getReviewById(int|string $id, array $relations = ['user', 'product']): ?Model
    {
        return $this->reviewRepository->findOrFail($id, ['*'], $relations);
    }

    public function createReview(array $data): ?Model
    {
        return $this->reviewRepository->create($data);
    }

    public function updateReviewStatus(int|string $id, bool $status): bool
    {
        return $this->reviewRepository->update($id, ['status' => $status]);
    }

    public function deleteReview(int|string $id): bool
    {
        return $this->reviewRepository->delete($id);
    }
}
