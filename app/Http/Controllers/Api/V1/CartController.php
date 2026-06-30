<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\V1\CartResource;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CartController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        return $this->respondOk(CartResource::make($this->resolveCart($request)->load('items.product')));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:999'],
        ]);

        $quantity = (int) ($data['quantity'] ?? 1);
        $cart = $this->resolveCart($request)->load('items.product');
        $product = Product::query()->published()->findOrFail($data['product_id']);
        $existing = $cart->items->firstWhere('product_id', $product->id);
        $requestedQuantity = $quantity + ((int) ($existing?->quantity ?? 0));

        if (! $product->isInStock($requestedQuantity)) {
            return $this->respondError('Not enough stock for this product.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $item = $cart->items()->firstOrNew([
            'product_id' => $product->id,
        ]);

        $item->quantity = $requestedQuantity;
        $item->unit_price = $product->currentPrice();
        $item->save();

        return $this->respondCreated(CartResource::make($cart->refresh()->load('items.product')));
    }

    public function update(Request $request, int $productId): JsonResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
        ]);

        $cart = $this->resolveCart($request)->load('items.product');
        $product = Product::query()->published()->findOrFail($productId);

        if (! $product->isInStock($data['quantity'])) {
            return $this->respondError('Not enough stock for this product.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $item = $cart->items()->where('product_id', $product->id)->firstOrFail();
        $item->update([
            'quantity' => $data['quantity'],
            'unit_price' => $product->currentPrice(),
        ]);

        return $this->respondOk(CartResource::make($cart->refresh()->load('items.product')));
    }

    public function destroy(Request $request, int $productId): JsonResponse
    {
        $cart = $this->resolveCart($request);
        $cart->items()->where('product_id', $productId)->delete();

        return $this->respondOk(CartResource::make($cart->refresh()->load('items.product')));
    }

    public function clear(Request $request): JsonResponse
    {
        $cart = $this->resolveCart($request);
        $cart->items()->delete();

        return $this->respondOk(CartResource::make($cart->refresh()->load('items.product')));
    }

    private function resolveCart(Request $request): Cart
    {
        return Cart::query()->firstOrCreate(['user_id' => $request->user()->id]);
    }
}
