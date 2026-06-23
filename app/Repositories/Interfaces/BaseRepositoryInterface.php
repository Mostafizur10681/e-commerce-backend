<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface BaseRepositoryInterface
{
    public function all(array $columns = ['*'], array $relations = []): Collection;

    public function paginate(int $perPage = 15, array $relations = [], array $columns = ['*']): LengthAwarePaginator;

    public function find(int|string $id, array $columns = ['*'], array $relations = []): ?Model;

    public function findOrFail(int|string $id, array $columns = ['*'], array $relations = []): Model;

    public function create(array $payload): ?Model;

    public function update(int|string $id, array $payload): bool;

    public function delete(int|string $id): bool;
}
