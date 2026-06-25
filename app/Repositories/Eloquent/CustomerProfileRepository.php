<?php

namespace App\Repositories\Eloquent;

use App\Models\CustomerProfile;
use App\Repositories\Interfaces\CustomerProfileRepositoryInterface;

class CustomerProfileRepository extends BaseRepository implements CustomerProfileRepositoryInterface
{
    public function __construct(CustomerProfile $model)
    {
        parent::__construct($model);
    }
}
