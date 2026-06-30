<?php

namespace App\Repositories;

use App\Models\Category;

class CategoryRepository
{
    public function activeTree(): \Illuminate\Database\Eloquent\Collection
    {
        return Category::active()
            ->orderBy('position')
            ->orderBy('name')
            ->get();
    }
}
