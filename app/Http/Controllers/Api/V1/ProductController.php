<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\V1\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $products = Product::query()
            ->with(['category', 'productImages'])
            ->when($request->filled('category_id'), function ($query) use ($request): void {
                $query->where('category_id', $request->integer('category_id'));
            })
            ->when($request->boolean('featured'), fn ($query) => $query->featured())
            ->search($request->input('q'))
            ->when($request->input('sort') === 'price_asc', fn ($query) => $query->orderBy('price'))
            ->when($request->input('sort') === 'price_desc', fn ($query) => $query->orderByDesc('price'))
            ->when($request->input('sort') === 'oldest', fn ($query) => $query->orderBy('created_at'))
            ->when(! in_array($request->input('sort'), ['price_asc', 'price_desc', 'oldest'], true), fn ($query) => $query->latest())
            ->paginate((int) $request->input('per_page', 12))
            ->withQueryString();

        return $this->respondOk(ProductResource::collection($products));
    }

    public function featured(Request $request): JsonResponse
    {
        $products = Product::query()
            ->with(['category', 'productImages'])
            ->published()
            ->featured()
            ->latest()
            ->limit((int) $request->input('limit', 8))
            ->get();

        return $this->respondOk(ProductResource::collection($products));
    }

    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => ['required', 'string', 'min:2'],
        ]);

        $products = Product::query()
            ->with(['category', 'productImages'])
            ->search($request->input('q'))
            ->latest()
            ->paginate((int) $request->input('per_page', 12))
            ->withQueryString();

        return $this->respondOk(ProductResource::collection($products));
    }

    public function show(Product $product): JsonResponse
    {
        $product = Product::query()
            ->with(['category', 'productImages'])
            ->findOrFail($product->id);

        return $this->respondOk(ProductResource::make($product));
    }

    public function showBySlug(Product $product): JsonResponse
    {
        $product = Product::query()
            ->with(['category', 'productImages'])
            ->findOrFail($product->id);

        return $this->respondOk(ProductResource::make($product));
    }
}
