<?php

namespace App\Repositories\Eloquent;

use App\Models\Wishlist;
use App\Repositories\Interfaces\WishlistRepositoryInterface;

class WishlistRepository extends BaseRepository implements WishlistRepositoryInterface
{
    public function __construct(Wishlist $model)
    {
        parent::__construct($model);
    }
}
