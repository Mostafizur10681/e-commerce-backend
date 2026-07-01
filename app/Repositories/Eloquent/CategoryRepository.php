<?php

namespace App\Repositories\Eloquent;

use App\Models\Category;
use App\Repositories\Interfaces\CategoryRepositoryInterface;

class CategoryRepository extends BaseRepository implements CategoryRepositoryInterface
{
    public function __construct(Category $model)
    {
        parent::__construct($model);
    }

    public function getActive(array $relations = []): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->where('status', true)->with($relations)->get();
    }

    public function paginateActive(int $perPage = 15, array $relations = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $this->model->where('status', true)->with($relations)->paginate($perPage);
    }
}
