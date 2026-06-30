<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ProductResource;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['category', 'productImages']);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%");
            });
        }

        $products = $query->latest()->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $products->items(),
            'meta' => [
                'total' => $products->total(),
                'per_page' => $products->perPage(),
            ],
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric'],
            'stock_quantity' => ['required', 'integer'],
            'category_id' => ['nullable', 'integer'],
            'status' => ['nullable', 'string', 'in:published,draft,archived'],
            'images.*' => ['nullable', 'image', 'max:2048'],
        ]);

        $baseSlug = Str::slug($validated['name']);
        $slug = $baseSlug;
        $suffix = 1;
        while (Product::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $suffix++;
        }

        $data = [
            'name' => $validated['name'],
            'slug' => $slug,
            'sku' => 'PRD-' . Carbon::now()->format('Ymd') . '-' . strtoupper(Str::random(6)),
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'stock_quantity' => $validated['stock_quantity'],
            'status' => $validated['status'] ?? 'published',
            'is_active' => true,
        ];

        $defaultCategory = Category::firstOrCreate(
            ['name' => 'Uncategorized'],
            ['slug' => 'uncategorized']
        );
        $data['category_id'] = (int) ($validated['category_id'] ?? $defaultCategory->id);

        $product = new Product($data);
        $product->save();

        $this->saveImages($product, $request->file('images', []));

        return response()->json([
            'success' => true,
            'data' => ProductResource::make($product->load('productImages')),
        ], 201);
    }

    public function show(Product $product): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => ProductResource::make($product->load(['category', 'productImages'])),
        ], 200);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric'],
            'stock_quantity' => ['required', 'integer'],
            'category_id' => ['nullable', 'integer'],
            'status' => ['nullable', 'string', 'in:published,draft,archived'],
            'images.*' => ['nullable', 'image', 'max:2048'],
        ]);

        $data = [
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? $product->description,
            'price' => $validated['price'],
            'stock_quantity' => $validated['stock_quantity'],
            'status' => $validated['status'] ?? $product->status,
        ];

        if (empty($product->sku)) {
            $data['sku'] = 'PRD-' . Carbon::now()->format('Ymd') . '-' . strtoupper(Str::random(6));
        }

        if (!empty($validated['category_id'])) {
            $data['category_id'] = (int) $validated['category_id'];
        }

        $product->update($data);

        $this->saveImages($product, $request->file('images', []));

        return response()->json([
            'success' => true,
            'data' => ProductResource::make($product->load('productImages')),
        ], 200);
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted.',
        ], 200);
    }

    private function saveImages(Product $product, array $images): void
    {
        foreach ($images as $image) {
            $path = $image->store('products', 'public');

            ProductImage::create([
                'product_id' => $product->id,
                'path' => $path,
            ]);
        }
    }
}
