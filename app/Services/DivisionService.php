<?php

namespace App\Services;

use App\Repositories\Interfaces\DivisionRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Exception;

class DivisionService
{
    protected DivisionRepositoryInterface $divisionRepository;

    public function __construct(DivisionRepositoryInterface $divisionRepository)
    {
        $this->divisionRepository = $divisionRepository;
    }

    public function paginateDivisions(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->divisionRepository->advancedPaginate($filters, $perPage);
    }

    public function getDivisionById(int|string $id): Model
    {
        return $this->divisionRepository->findOrFail($id);
    }

    public function createDivision(array $data): ?Model
    {
        DB::beginTransaction();
        try {
            $division = $this->divisionRepository->create($data);
            DB::commit();
            return $division;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateDivision(int|string $id, array $data): bool
    {
        DB::beginTransaction();
        try {
            $updated = $this->divisionRepository->update($id, $data);
            DB::commit();
            return $updated;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteDivision(int|string $id): bool
    {
        DB::beginTransaction();
        try {
            $deleted = $this->divisionRepository->delete($id);
            DB::commit();
            return $deleted;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
