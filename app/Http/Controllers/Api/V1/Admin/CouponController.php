<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $coupons = Coupon::query()
            ->latest()
            ->paginate((int) $request->input('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $coupons->items(),
            'meta' => [
                'total' => $coupons->total(),
                'per_page' => $coupons->perPage(),
            ],
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:100', 'unique:coupons,code'],
            'type' => ['required', 'string', 'in:fixed,percent'],
            'value' => ['required', 'numeric', 'min:0'],
            'min_amount' => ['nullable', 'numeric', 'min:0'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
            'max_uses_per_user' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $coupon = Coupon::create($validated);

        return response()->json([
            'success' => true,
            'data' => $coupon,
            'message' => 'Coupon created.',
        ], 201);
    }

    public function show(Request $request, Coupon $coupon): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $coupon,
        ], 200);
    }

    public function update(Request $request, Coupon $coupon): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['sometimes', 'string', 'max:100', 'unique:coupons,code,'.$coupon->id],
            'type' => ['sometimes', 'string', 'in:fixed,percent'],
            'value' => ['sometimes', 'numeric', 'min:0'],
            'min_amount' => ['nullable', 'numeric', 'min:0'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
            'max_uses_per_user' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $coupon->update($validated);

        return response()->json([
            'success' => true,
            'data' => $coupon->refresh(),
            'message' => 'Coupon updated.',
        ], 200);
    }

    public function destroy(Request $request, Coupon $coupon): JsonResponse
    {
        $coupon->delete();

        return response()->json([
            'success' => true,
            'message' => 'Coupon deleted.',
        ], 200);
    }
}
