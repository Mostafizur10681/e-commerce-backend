<?php

namespace App\Services;

use App\Repositories\Interfaces\FaqRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class FaqService
{
    protected FaqRepositoryInterface $faqRepository;

    public function __construct(FaqRepositoryInterface $faqRepository)
    {
        $this->faqRepository = $faqRepository;
    }

    public function getAllFaqs(): Collection
    {
        return $this->faqRepository->all();
    }

    public function getActiveFaqs(): Collection
    {
        return \App\Models\Faq::where('status', true)->get();
    }

    public function paginateFaqs(int $perPage = 15): LengthAwarePaginator
    {
        return $this->faqRepository->paginate($perPage);
    }

    public function getFaqById(int|string $id): ?Model
    {
        return $this->faqRepository->findOrFail($id);
    }

    public function createFaq(array $data): ?Model
    {
        return $this->faqRepository->create($data);
    }

    public function updateFaq(int|string $id, array $data): bool
    {
        return $this->faqRepository->update($id, $data);
    }

    public function deleteFaq(int|string $id): bool
    {
        return $this->faqRepository->delete($id);
    }
}
