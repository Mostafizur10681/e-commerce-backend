<?php

namespace App\Services;

use App\Repositories\Interfaces\SubscriptionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class SubscriptionService
{
    protected SubscriptionRepositoryInterface $subscriptionRepository;

    public function __construct(SubscriptionRepositoryInterface $subscriptionRepository)
    {
        $this->subscriptionRepository = $subscriptionRepository;
    }

    public function getAllSubscriptions(): Collection
    {
        return $this->subscriptionRepository->all();
    }

    public function getActiveSubscriptions(): Collection
    {
        return \App\Models\Subscription::where('status', true)->get();
    }

    public function paginateSubscriptions(int $perPage = 15): LengthAwarePaginator
    {
        return $this->subscriptionRepository->paginate($perPage);
    }

    public function getSubscriptionById(int|string $id): ?Model
    {
        return $this->subscriptionRepository->findOrFail($id);
    }

    public function createSubscription(array $data): ?Model
    {
        return $this->subscriptionRepository->create($data);
    }

    public function updateSubscription(int|string $id, array $data): bool
    {
        return $this->subscriptionRepository->update($id, $data);
    }

    public function deleteSubscription(int|string $id): bool
    {
        return $this->subscriptionRepository->delete($id);
    }
}
