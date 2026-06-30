<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\V1\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends ApiController
{
    public function index(): JsonResponse
    {
        $categories = Category::query()
            ->active()
            ->with(['children' => fn ($query) => $query->ordered()])
            ->whereNull('parent_id')
            ->ordered()
            ->get();

        return $this->respondOk(CategoryResource::collection($categories));
    }

    public function show(Category $category): JsonResponse
    {
        $category = Category::query()
            ->active()
            ->with(['children' => fn ($query) => $query->ordered(), 'products' => fn ($query) => $query->published()->latest()])
            ->findOrFail($category->id);

        return $this->respondOk(CategoryResource::make($category));
    }
}
