<?php

namespace App\Repositories\Interfaces;

use Illuminate\Pagination\LengthAwarePaginator;

interface DivisionRepositoryInterface extends BaseRepositoryInterface
{
    public function advancedPaginate(array $filters, int $perPage = 15): LengthAwarePaginator;
}
