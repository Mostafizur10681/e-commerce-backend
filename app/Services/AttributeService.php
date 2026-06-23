<?php

namespace App\Services;

use App\Repositories\Interfaces\AttributeRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class AttributeService
{
    protected AttributeRepositoryInterface $attributeRepository;

    public function __construct(AttributeRepositoryInterface $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    public function getAllAttributes(): Collection
    {
        return $this->attributeRepository->all();
    }

    public function paginateAttributes(int $perPage = 15): LengthAwarePaginator
    {
        return $this->attributeRepository->paginate($perPage);
    }

    public function getAttributeById(int|string $id): ?Model
    {
        return $this->attributeRepository->findOrFail($id);
    }

    public function createAttribute(array $data): ?Model
    {
        return $this->attributeRepository->create($data);
    }

    public function updateAttribute(int|string $id, array $data): bool
    {
        return $this->attributeRepository->update($id, $data);
    }

    public function deleteAttribute(int|string $id): bool
    {
        return $this->attributeRepository->delete($id);
    }
}
