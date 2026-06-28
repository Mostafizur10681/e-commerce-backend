<?php

namespace App\Services;

use App\Repositories\Interfaces\DistrictRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Exception;

class DistrictService
{
    protected DistrictRepositoryInterface $districtRepository;

    public function __construct(DistrictRepositoryInterface $districtRepository)
    {
        $this->districtRepository = $districtRepository;
    }

    public function paginateDistricts(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->districtRepository->advancedPaginate($filters, $perPage);
    }

    public function getDistrictById(int|string $id): Model
    {
        return $this->districtRepository->findOrFail($id, ['*'], ['division']);
    }

    public function createDistrict(array $data): ?Model
    {
        DB::beginTransaction();
        try {
            $district = $this->districtRepository->create($data);
            DB::commit();
            return $district;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateDistrict(int|string $id, array $data): bool
    {
        DB::beginTransaction();
        try {
            $updated = $this->districtRepository->update($id, $data);
            DB::commit();
            return $updated;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteDistrict(int|string $id): bool
    {
        DB::beginTransaction();
        try {
            $deleted = $this->districtRepository->delete($id);
            DB::commit();
            return $deleted;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
