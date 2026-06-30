<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Resources\V1\OrderResource;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $orders = Order::query()
            ->forUser($request->user()->id)
            ->with(['items.product.category', 'items.product.productImages'])
            ->latest()
            ->paginate((int) $request->input('per_page', 10))
            ->withQueryString();

        return $this->respondOk(OrderResource::collection($orders));
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        if ($order->user_id !== $request->user()->id && ! $request->user()->is_admin) {
            return $this->respondError('You do not have access to this order.', Response::HTTP_FORBIDDEN);
        }

        return $this->respondOk(OrderResource::make($order->load(['items.product.category', 'items.product.productImages'])));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'shipping_address.full_name' => ['required', 'string', 'max:255'],
            'shipping_address.phone' => ['nullable', 'string', 'max:20'],
            'shipping_address.address' => ['required', 'string', 'max:255'],
            'shipping_address.city' => ['required', 'string', 'max:100'],
            'shipping_address.state' => ['nullable', 'string', 'max:100'],
            'shipping_address.country' => ['required', 'string', 'max:100'],
            'shipping_address.postal_code' => ['required', 'string', 'max:20'],
            'billing_address' => ['nullable', 'array'],
            'payment_method' => ['required', 'string', 'max:100'],
            'coupon_code' => ['nullable', 'string', 'max:50'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'shipping_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $user = $request->user();
        $cart = Cart::query()->with('items.product')->firstOrCreate(['user_id' => $user->id]);

        if ($cart->items->isEmpty()) {
            return $this->respondError('Cart is empty.', Response::HTTP_BAD_REQUEST);
        }

        $order = DB::transaction(function () use ($cart, $data, $user) {
            $cart->load('items.product');

            $subtotal = 0.0;
            $orderItems = [];

            foreach ($cart->items as $cartItem) {
                $product = Product::query()->lockForUpdate()->findOrFail($cartItem->product_id);

                if (! $product->isInStock($cartItem->quantity)) {
                    throw ValidationException::withMessages([
                        'cart' => ["Product {$product->name} does not have enough stock."],
                    ]);
                }

                $lineTotal = (float) $cartItem->unit_price * (int) $cartItem->quantity;
                $subtotal += $lineTotal;

                $orderItems[] = [
                    'product' => $product,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'price' => $cartItem->unit_price,
                    'quantity' => $cartItem->quantity,
                    'total' => $lineTotal,
                ];
            }

            $discountAmount = 0.0;
            if (! empty($data['coupon_code'])) {
                $discountAmount = $this->resolveCouponDiscount($data['coupon_code'], $subtotal, $user->id);
            }

            $taxAmount = (float) ($data['tax_amount'] ?? 0);
            $shippingAmount = (float) ($data['shipping_amount'] ?? 0);
            $total = max(0, $subtotal + $taxAmount + $shippingAmount - $discountAmount);

            $order = Order::create([
                'order_number' => sprintf('ORD-%s-%s', now()->format('YmdHis'), Str::upper(Str::random(6))),
                'user_id' => $user->id,
                'status' => OrderStatus::Pending->value,
                'payment_method' => $data['payment_method'],
                'payment_status' => PaymentStatus::Pending->value,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'shipping_amount' => $shippingAmount,
                'discount_amount' => $discountAmount,
                'total' => $total,
                'currency' => 'USD',
                'shipping_address' => $data['shipping_address'],
                'billing_address' => $data['billing_address'] ?? $data['shipping_address'],
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($orderItems as $item) {
                $order->items()->create([
                    'product_id' => $item['product']->id,
                    'name' => $item['name'],
                    'sku' => $item['sku'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'total' => $item['total'],
                ]);

                $item['product']->decrement('stock_quantity', $item['quantity']);
            }

            $cart->items()->delete();

            if (! empty($data['coupon_code'])) {
                Coupon::query()->where('code', $data['coupon_code'])->increment('used_count');
            }

            return $order->load(['items.product.category', 'items.product.productImages']);
        });

        return $this->respondCreated(OrderResource::make($order));
    }

    private function resolveCouponDiscount(string $code, float $subtotal, int $userId): float
    {
        $coupon = Coupon::query()->where('code', $code)->first();

        if (! $coupon || ! $coupon->is_active) {
            throw ValidationException::withMessages([
                'coupon_code' => ['The coupon code is invalid.'],
            ]);
        }

        if ($coupon->starts_at && $coupon->starts_at->isFuture()) {
            throw ValidationException::withMessages([
                'coupon_code' => ['The coupon is not active yet.'],
            ]);
        }

        if ($coupon->expires_at && $coupon->expires_at->isPast()) {
            throw ValidationException::withMessages([
                'coupon_code' => ['The coupon has expired.'],
            ]);
        }

        if ((float) $coupon->min_amount > $subtotal) {
            throw ValidationException::withMessages([
                'coupon_code' => ['The order does not meet the minimum amount for this coupon.'],
            ]);
        }

        if ($coupon->max_uses !== null && $coupon->used_count >= $coupon->max_uses) {
            throw ValidationException::withMessages([
                'coupon_code' => ['This coupon has reached its usage limit.'],
            ]);
        }

        $percentage = strtolower((string) $coupon->type) === 'percent';

        if ($percentage) {
            return round($subtotal * ((float) $coupon->value / 100), 2);
        }

        return min((float) $coupon->value, $subtotal);
    }
}
