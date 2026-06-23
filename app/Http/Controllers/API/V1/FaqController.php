<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\V1\StoreFaqRequest;
use App\Http\Requests\API\V1\UpdateFaqRequest;
use App\Http\Resources\API\V1\FaqResource;
use App\Services\FaqService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    protected FaqService $faqService;

    public function __construct(FaqService $faqService)
    {
        $this->faqService = $faqService;
    }

    public function index(Request $request): JsonResponse
    {
        // Public users can only see active FAQs
        $user = $request->user();
        if ($user && $user->hasRole(['Admin', 'Editor'])) {
            if ($request->boolean('all')) {
                $faqs = $this->faqService->getAllFaqs();
                return $this->success(FaqResource::collection($faqs), 'Faqs retrieved successfully');
            }
            $perPage = $request->query('per_page', 15);
            $faqs = $this->faqService->paginateFaqs($perPage);
            return $this->success(FaqResource::collection($faqs)->response()->getData(true), 'Faqs retrieved successfully');
        }

        // Public index
        $faqs = $this->faqService->getActiveFaqs();
        return $this->success(FaqResource::collection($faqs), 'Faqs retrieved successfully');
    }

    public function store(StoreFaqRequest $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->hasRole(['Admin', 'Editor'])) {
            return $this->error('Unauthorized to create FAQs', [], 403);
        }

        $faq = $this->faqService->createFaq($request->validated());
        return $this->success(new FaqResource($faq), 'Faq created successfully', 201);
    }

    public function show(string $id): JsonResponse
    {
        $faq = $this->faqService->getFaqById($id);
        return $this->success(new FaqResource($faq), 'Faq retrieved successfully');
    }

    public function update(UpdateFaqRequest $request, string $id): JsonResponse
    {
        $user = $request->user();
        if (!$user->hasRole(['Admin', 'Editor'])) {
            return $this->error('Unauthorized to update FAQs', [], 403);
        }

        $updated = $this->faqService->updateFaq($id, $request->validated());
        if ($updated) {
            $faq = $this->faqService->getFaqById($id);
            return $this->success(new FaqResource($faq), 'Faq updated successfully');
        }
        return $this->error('Failed to update faq');
    }

    public function destroy(string $id, Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->hasRole(['Admin', 'Editor'])) {
            return $this->error('Unauthorized to delete FAQs', [], 403);
        }

        $deleted = $this->faqService->deleteFaq($id);
        if ($deleted) {
            return $this->success([], 'Faq deleted successfully');
        }
        return $this->error('Failed to delete faq');
    }
}
