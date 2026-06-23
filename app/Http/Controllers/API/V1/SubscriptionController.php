<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\V1\StoreSubscriptionRequest;
use App\Http\Requests\API\V1\UpdateSubscriptionRequest;
use App\Http\Resources\API\V1\SubscriptionResource;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    protected SubscriptionService $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user && $user->hasRole(['Admin', 'Editor'])) {
            if ($request->boolean('all')) {
                $subscriptions = $this->subscriptionService->getAllSubscriptions();
                return $this->success(SubscriptionResource::collection($subscriptions), 'Subscriptions retrieved successfully');
            }
            $perPage = $request->query('per_page', 15);
            $subscriptions = $this->subscriptionService->paginateSubscriptions($perPage);
            return $this->success(SubscriptionResource::collection($subscriptions)->response()->getData(true), 'Subscriptions retrieved successfully');
        }

        return $this->error('Unauthorized to view subscriptions', [], 403);
    }

    public function store(StoreSubscriptionRequest $request): JsonResponse
    {
        $subscription = $this->subscriptionService->createSubscription($request->validated());
        return $this->success(new SubscriptionResource($subscription), 'Subscribed successfully', 201);
    }

    public function show(string $id, Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user || !$user->hasRole(['Admin', 'Editor'])) {
            return $this->error('Unauthorized to view subscription', [], 403);
        }

        $subscription = $this->subscriptionService->getSubscriptionById($id);
        return $this->success(new SubscriptionResource($subscription), 'Subscription retrieved successfully');
    }

    public function update(UpdateSubscriptionRequest $request, string $id): JsonResponse
    {
        $user = $request->user();
        if (!$user || !$user->hasRole(['Admin', 'Editor'])) {
            return $this->error('Unauthorized to update subscription', [], 403);
        }

        $updated = $this->subscriptionService->updateSubscription($id, $request->validated());
        if ($updated) {
            $subscription = $this->subscriptionService->getSubscriptionById($id);
            return $this->success(new SubscriptionResource($subscription), 'Subscription updated successfully');
        }
        return $this->error('Failed to update subscription');
    }

    public function destroy(string $id, Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user || !$user->hasRole(['Admin', 'Editor'])) {
            return $this->error('Unauthorized to delete subscription', [], 403);
        }

        $deleted = $this->subscriptionService->deleteSubscription($id);
        if ($deleted) {
            return $this->success([], 'Subscription deleted successfully');
        }
        return $this->error('Failed to delete subscription');
    }
}
