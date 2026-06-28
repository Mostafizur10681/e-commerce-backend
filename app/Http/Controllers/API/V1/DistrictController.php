<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\V1\DistrictRequest;
use App\Http\Resources\API\V1\DistrictResource;
use App\Services\DistrictService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DistrictController extends Controller
{
    protected DistrictService $districtService;

    public function __construct(DistrictService $districtService)
    {
        $this->districtService = $districtService;
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', 10);
        $filters = $request->only(['search', 'sort_by', 'sort_order', 'status', 'division_id']);
        
        $districts = $this->districtService->paginateDistricts($filters, $perPage);
        
        return $this->success(
            DistrictResource::collection($districts)->response()->getData(true),
            'Districts retrieved successfully'
        );
    }

    public function store(DistrictRequest $request): JsonResponse
    {
        $data = $this->mapData($request->validated());
        $district = $this->districtService->createDistrict($data);
        return $this->success(new DistrictResource($district), 'District created successfully', 201);
    }

    public function show(string $id): JsonResponse
    {
        $district = $this->districtService->getDistrictById($id);
        return $this->success(new DistrictResource($district), 'District retrieved successfully');
    }

    public function update(DistrictRequest $request, string $id): JsonResponse
    {
        $data = $this->mapData($request->validated());
        $updated = $this->districtService->updateDistrict($id, $data);
        if ($updated) {
            $district = $this->districtService->getDistrictById($id);
            return $this->success(new DistrictResource($district), 'District updated successfully');
        }
        return $this->error('Failed to update district');
    }

    public function destroy(string $id): JsonResponse
    {
        $deleted = $this->districtService->deleteDistrict($id);
        if ($deleted) {
            return $this->success([], 'District deleted successfully');
        }
        return $this->error('Failed to delete district');
    }

    private function mapData(array $validated): array
    {
        return [
            'division_id' => $validated['division_id'],
            'name' => $validated['district_name'],
            'bn_name' => $validated['district_name_bn'] ?? '',
            'code' => $validated['district_code'],
            'status' => $validated['status'] ?? 1,
        ];
    }
}
