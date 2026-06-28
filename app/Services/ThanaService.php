<?php

namespace App\Services;

use App\Repositories\Interfaces\ThanaRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Exception;

class ThanaService
{
    protected ThanaRepositoryInterface $thanaRepository;

    public function __construct(ThanaRepositoryInterface $thanaRepository)
    {
        $this->thanaRepository = $thanaRepository;
    }

    public function paginateThanas(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->thanaRepository->advancedPaginate($filters, $perPage);
    }

    public function getThanaById(int|string $id): Model
    {
        return $this->thanaRepository->findOrFail($id, ['*'], ['division', 'district']);
    }

    public function createThana(array $data): ?Model
    {
        DB::beginTransaction();
        try {
            $thana = $this->thanaRepository->create($data);
            DB::commit();
            return $thana;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateThana(int|string $id, array $data): bool
    {
        DB::beginTransaction();
        try {
            $updated = $this->thanaRepository->update($id, $data);
            DB::commit();
            return $updated;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteThana(int|string $id): bool
    {
        DB::beginTransaction();
        try {
            $deleted = $this->thanaRepository->delete($id);
            DB::commit();
            return $deleted;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
