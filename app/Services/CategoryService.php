<?php

namespace App\Services;

use App\Repositories\CategoryRepository;

class CategoryService
{
    public function __construct(private CategoryRepository $categories) {}

    public function listActive(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->categories->activeTree();
    }
}
