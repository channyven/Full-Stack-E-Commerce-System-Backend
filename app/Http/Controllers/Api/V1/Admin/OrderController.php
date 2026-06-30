<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $orders = Order::query()
            ->with(['user', 'items.product'])
            ->when($request->filled('status'), function ($query) use ($request): void {
                $query->where('status', $request->string('status'));
            })
            ->latest()
            ->paginate((int) $request->input('per_page', 20))
            ->withQueryString();

        return response()->json([
            'success' => true,
            'data' => $orders->items(),
            'meta' => [
                'total' => $orders->total(),
                'per_page' => $orders->perPage(),
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
            ],
        ], Response::HTTP_OK);
    }

    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'string', Rule::in(collect(OrderStatus::cases())->map(fn ($s) => $s->value)->all())],
        ]);

        $order->update([
            'status' => $request->string('status'),
        ]);

        return response()->json([
            'success' => true,
            'data' => $order->load('items.product'),
            'message' => 'Order status updated.',
        ], Response::HTTP_OK);
    }
}
