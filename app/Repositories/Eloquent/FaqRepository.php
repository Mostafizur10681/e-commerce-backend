<?php

namespace App\Repositories\Eloquent;

use App\Models\Faq;
use App\Repositories\Interfaces\FaqRepositoryInterface;

class FaqRepository extends BaseRepository implements FaqRepositoryInterface
{
    public function __construct(Faq $model)
    {
        parent::__construct($model);
    }
}
