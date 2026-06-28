<?php

namespace App\Repositories\Eloquent;

use App\Models\Thana;
use App\Repositories\Interfaces\ThanaRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class ThanaRepository extends BaseRepository implements ThanaRepositoryInterface
{
    public function __construct(Thana $model)
    {
        parent::__construct($model);
    }

    public function advancedPaginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        // Define relationship joining divisions via districts
        $query = $this->model->newQuery()->with(['division', 'district']);

        if (!empty($filters['district_id'])) {
            $query->where('district_id', $filters['district_id']);
        }

        if (!empty($filters['division_id'])) {
            $query->whereHas('district', function ($dq) use ($filters) {
                $dq->where('division_id', $filters['division_id']);
            });
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('bn_name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhereHas('district', function ($dq) use ($search) {
                      $dq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('division', function ($divQ) use ($search) {
                      $divQ->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        $sortBy = $filters['sort_by'] ?? 'name';
        $sortOrder = $filters['sort_order'] ?? 'asc';
        $allowedSort = ['name', 'created_at'];
        if (in_array($sortBy, $allowedSort)) {
            $query->orderBy($sortBy, $sortOrder === 'desc' ? 'desc' : 'asc');
        } else {
            $query->orderBy('name', 'asc');
        }

        return $query->paginate($perPage);
    }
}
