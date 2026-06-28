<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\V1\ThanaRequest;
use App\Http\Resources\API\V1\ThanaResource;
use App\Services\ThanaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ThanaController extends Controller
{
    protected ThanaService $thanaService;

    public function __construct(ThanaService $thanaService)
    {
        $this->thanaService = $thanaService;
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', 10);
        $filters = $request->only(['search', 'sort_by', 'sort_order', 'status', 'division_id', 'district_id']);
        
        $thanas = $this->thanaService->paginateThanas($filters, $perPage);
        
        return $this->success(
            ThanaResource::collection($thanas)->response()->getData(true),
            'Thanas retrieved successfully'
        );
    }

    public function store(ThanaRequest $request): JsonResponse
    {
        $data = $this->mapData($request->validated());
        $thana = $this->thanaService->createThana($data);
        return $this->success(new ThanaResource($thana), 'Thana created successfully', 201);
    }

    public function show(string $id): JsonResponse
    {
        $thana = $this->thanaService->getThanaById($id);
        return $this->success(new ThanaResource($thana), 'Thana retrieved successfully');
    }

    public function update(ThanaRequest $request, string $id): JsonResponse
    {
        $data = $this->mapData($request->validated());
        $updated = $this->thanaService->updateThana($id, $data);
        if ($updated) {
            $thana = $this->thanaService->getThanaById($id);
            return $this->success(new ThanaResource($thana), 'Thana updated successfully');
        }
        return $this->error('Failed to update thana');
    }

    public function destroy(string $id): JsonResponse
    {
        $deleted = $this->thanaService->deleteThana($id);
        if ($deleted) {
            return $this->success([], 'Thana deleted successfully');
        }
        return $this->error('Failed to delete thana');
    }

    private function mapData(array $validated): array
    {
        return [
            'district_id' => $validated['district_id'],
            'name' => $validated['thana_name'],
            'bn_name' => $validated['thana_name_bn'] ?? '',
            'code' => $validated['thana_code'],
            'postal_code' => $validated['postal_code'] ?? null,
            'status' => $validated['status'] ?? 1,
        ];
    }
}
