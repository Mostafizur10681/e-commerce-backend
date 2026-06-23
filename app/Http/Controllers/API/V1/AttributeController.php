<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\V1\StoreAttributeRequest;
use App\Http\Requests\API\V1\UpdateAttributeRequest;
use App\Http\Resources\API\V1\AttributeResource;
use App\Services\AttributeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttributeController extends Controller
{
    protected AttributeService $attributeService;

    public function __construct(AttributeService $attributeService)
    {
        $this->attributeService = $attributeService;
    }

    public function index(Request $request): JsonResponse
    {
        if ($request->boolean('all')) {
            $attributes = $this->attributeService->getAllAttributes();
            return $this->success(AttributeResource::collection($attributes), 'Attributes retrieved successfully');
        }

        $perPage = $request->query('per_page', 15);
        $attributes = $this->attributeService->paginateAttributes($perPage);
        return $this->success(AttributeResource::collection($attributes)->response()->getData(true), 'Attributes retrieved successfully');
    }

    public function store(StoreAttributeRequest $request): JsonResponse
    {
        $attribute = $this->attributeService->createAttribute($request->validated());
        return $this->success(new AttributeResource($attribute), 'Attribute created successfully', 201);
    }

    public function show(string $id): JsonResponse
    {
        $attribute = $this->attributeService->getAttributeById($id);
        return $this->success(new AttributeResource($attribute), 'Attribute retrieved successfully');
    }

    public function update(UpdateAttributeRequest $request, string $id): JsonResponse
    {
        $updated = $this->attributeService->updateAttribute($id, $request->validated());
        if ($updated) {
            $attribute = $this->attributeService->getAttributeById($id);
            return $this->success(new AttributeResource($attribute), 'Attribute updated successfully');
        }
        return $this->error('Failed to update attribute');
    }

    public function destroy(string $id): JsonResponse
    {
        $deleted = $this->attributeService->deleteAttribute($id);
        if ($deleted) {
            return $this->success([], 'Attribute deleted successfully');
        }
        return $this->error('Failed to delete attribute');
    }
}
