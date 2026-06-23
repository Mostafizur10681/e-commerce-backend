<?php

namespace App\Repositories\Eloquent;

use App\Models\Subscription;
use App\Repositories\Interfaces\SubscriptionRepositoryInterface;

class SubscriptionRepository extends BaseRepository implements SubscriptionRepositoryInterface
{
    public function __construct(Subscription $model)
    {
        parent::__construct($model);
    }
}
