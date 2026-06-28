<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\V1\DivisionRequest;
use App\Http\Resources\API\V1\DivisionResource;
use App\Services\DivisionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DivisionController extends Controller
{
    protected DivisionService $divisionService;

    public function __construct(DivisionService $divisionService)
    {
        $this->divisionService = $divisionService;
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', 10);
        $filters = $request->only(['search', 'sort_by', 'sort_order', 'status']);
        
        $divisions = $this->divisionService->paginateDivisions($filters, $perPage);
        
        return $this->success(
            DivisionResource::collection($divisions)->response()->getData(true),
            'Divisions retrieved successfully'
        );
    }

    public function store(DivisionRequest $request): JsonResponse
    {
        $data = $this->mapData($request->validated());
        $division = $this->divisionService->createDivision($data);
        return $this->success(new DivisionResource($division), 'Division created successfully', 201);
    }

    public function show(string $id): JsonResponse
    {
        $division = $this->divisionService->getDivisionById($id);
        return $this->success(new DivisionResource($division), 'Division retrieved successfully');
    }

    public function update(DivisionRequest $request, string $id): JsonResponse
    {
        $data = $this->mapData($request->validated());
        $updated = $this->divisionService->updateDivision($id, $data);
        if ($updated) {
            $division = $this->divisionService->getDivisionById($id);
            return $this->success(new DivisionResource($division), 'Division updated successfully');
        }
        return $this->error('Failed to update division');
    }

    public function destroy(string $id): JsonResponse
    {
        $deleted = $this->divisionService->deleteDivision($id);
        if ($deleted) {
            return $this->success([], 'Division deleted successfully');
        }
        return $this->error('Failed to delete division');
    }

    private function mapData(array $validated): array
    {
        return [
            'name' => $validated['division_name'],
            'bn_name' => $validated['division_name_bn'] ?? '',
            'code' => $validated['division_code'],
            'status' => $validated['status'] ?? 1,
        ];
    }
}
