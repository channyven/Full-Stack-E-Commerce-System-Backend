<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\V1\CartResource;
use App\Http\Resources\V1\WishlistResource;
use App\Models\Cart;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WishlistController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        return $this->respondOk(WishlistResource::make($this->wishlist($request)->load('items.product')));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
        ]);

        $wishlist = $this->wishlist($request);
        $product = Product::query()->published()->findOrFail($data['product_id']);

        $wishlist->items()->firstOrCreate([
            'product_id' => $product->id,
        ]);

        return $this->respondCreated(WishlistResource::make($wishlist->refresh()->load('items.product')));
    }

    public function destroy(Request $request, int $productId): JsonResponse
    {
        $wishlist = $this->wishlist($request);
        $wishlist->items()->where('product_id', $productId)->delete();

        return $this->respondOk(WishlistResource::make($wishlist->refresh()->load('items.product')));
    }

    public function moveToCart(Request $request, int $productId): JsonResponse
    {
        $wishlist = $this->wishlist($request);
        $product = Product::query()->published()->findOrFail($productId);
        $quantity = (int) $request->input('quantity', 1);

        $cart = Cart::query()->firstOrCreate(['user_id' => $request->user()->id]);
        $existing = $cart->items()->firstOrNew(['product_id' => $product->id]);
        $requestedQuantity = $quantity + ((int) ($existing->quantity ?? 0));

        if (! $product->isInStock($requestedQuantity)) {
            return $this->respondError('Not enough stock for this product.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $existing->quantity = $requestedQuantity;
        $existing->unit_price = $product->currentPrice();
        $existing->save();

        $wishlist->items()->where('product_id', $product->id)->delete();

        return $this->respondOk([
            'wishlist' => WishlistResource::make($wishlist->refresh()->load('items.product')),
            'cart' => CartResource::make($cart->refresh()->load('items.product')),
        ]);
    }

    private function wishlist(Request $request): Wishlist
    {
        return Wishlist::query()->firstOrCreate(['user_id' => $request->user()->id]);
    }
}
