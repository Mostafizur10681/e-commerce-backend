<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\V1\StoreReviewRequest;
use App\Http\Requests\API\V1\UpdateReviewRequest;
use App\Http\Resources\API\V1\ReviewResource;
use App\Services\ReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    protected ReviewService $reviewService;

    public function __construct(ReviewService $reviewService)
    {
        $this->reviewService = $reviewService;
    }

    public function index(Request $request): JsonResponse
    {
        // Admin or Editor can see all reviews (including pending/disabled ones)
        $user = $request->user();
        if ($user && $user->hasRole(['Admin', 'Editor'])) {
            $perPage = $request->query('per_page', 15);
            $reviews = $this->reviewService->paginateReviews($perPage);
            return $this->success(ReviewResource::collection($reviews)->response()->getData(true), 'All reviews retrieved successfully');
        }

        // For public users, we can view reviews for a product (use query parameter product_id)
        $productId = $request->query('product_id');
        $perPage = $request->query('per_page', 15);

        if ($productId) {
            $reviews = $this->reviewService->getProductReviews($productId, $perPage);
            return $this->success(ReviewResource::collection($reviews)->response()->getData(true), 'Product reviews retrieved successfully');
        }

        // Return all approved/active reviews if product_id is not specified
        $reviews = \App\Models\Review::where('status', true)->with(['user', 'product'])->paginate($perPage);
        return $this->success(ReviewResource::collection($reviews)->response()->getData(true), 'Public reviews retrieved successfully');
    }

    public function store(StoreReviewRequest $request): JsonResponse
    {
        $review = $this->reviewService->createReview($request->validated());
        return $this->success(new ReviewResource($review->load('user')), 'Review submitted successfully', 201);
    }

    public function show(string $id): JsonResponse
    {
        $review = $this->reviewService->getReviewById($id);
        return $this->success(new ReviewResource($review), 'Review retrieved successfully');
    }

    public function update(UpdateReviewRequest $request, string $id): JsonResponse
    {
        // Admin or Editor can update status, user can update rating/comment
        $user = $request->user();
        $review = $this->reviewService->getReviewById($id);

        if ($review->user_id !== $user->id && !$user->hasRole(['Admin', 'Editor'])) {
            return $this->error('Unauthorized to edit this review', [], 403);
        }

        $validated = $request->validated();
        
        // If not Admin/Editor, prevent modifying status
        if (!$user->hasRole(['Admin', 'Editor'])) {
            unset($validated['status']);
        }

        $updated = $this->reviewService->updateReviewStatus($id, $validated['status'] ?? $review->status);
        if (isset($validated['rating']) || isset($validated['comment']) || isset($validated['image_path'])) {
            $review->update([
                'rating' => $validated['rating'] ?? $review->rating,
                'comment' => $validated['comment'] ?? $review->comment,
                'image_path' => $validated['image_path'] ?? $review->image_path,
            ]);
            $updated = true;
        }

        if ($updated) {
            $review = $this->reviewService->getReviewById($id);
            return $this->success(new ReviewResource($review), 'Review updated successfully');
        }
        return $this->error('Failed to update review');
    }

    public function destroy(string $id, Request $request): JsonResponse
    {
        $user = $request->user();
        $review = $this->reviewService->getReviewById($id);

        if ($review->user_id !== $user->id && !$user->hasRole('Admin')) {
            return $this->error('Unauthorized to delete this review', [], 403);
        }

        $deleted = $this->reviewService->deleteReview($id);
        if ($deleted) {
            return $this->success([], 'Review deleted successfully');
        }
        return $this->error('Failed to delete review');
    }
}
