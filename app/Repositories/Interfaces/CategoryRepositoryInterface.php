<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface CategoryRepositoryInterface extends BaseRepositoryInterface
{
    public function getActive(array $relations = []): Collection;
    public function paginateActive(int $perPage = 15, array $relations = []): LengthAwarePaginator;
}
