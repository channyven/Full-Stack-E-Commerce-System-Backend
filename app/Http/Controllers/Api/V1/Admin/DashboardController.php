<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ReviewStatus;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Http\Requests\Admin\CouponRequest;
use App\Http\Requests\Admin\ProductRequest;
use App\Http\Requests\Admin\UserRequest;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function summary(): JsonResponse
    {
        $summary = [
            'total_users' => User::count(),
            'total_products' => Product::count(),
            'total_orders' => Order::count(),
            'total_revenue' => Order::query()->sum('total'),
            'pending_orders' => Order::where('status', OrderStatus::Pending)->count(),
            'low_stock_products' => Product::whereColumn('stock_quantity', '<=', 'low_stock_threshold')->count(),
        ];

        $summary['recent_orders'] = Order::query()
            ->latest()
            ->limit(5)
            ->get(['id', 'order_number', 'user_id', 'total', 'status', 'created_at']);

        $summary['recent_users'] = User::query()
            ->latest()
            ->limit(5)
            ->get(['id', 'name', 'email', 'role', 'created_at']);

        return response()->json([
            'success' => true,
            'data' => $summary,
        ], Response::HTTP_OK);
    }

    public function sales(Request $request): JsonResponse
    {
        $range = $request->query('range', 'daily');

        $query = Order::query()
            ->select(
                DB::raw('SUM(total) as revenue'),
                DB::raw($range === 'monthly' ? 'DATE_FORMAT(created_at, "%Y-%m") as period' : 'DATE(created_at) as period'),
            )
            ->groupBy('period')
            ->orderByDesc('period')
            ->limit(30);

        return response()->json([
            'success' => true,
            'data' => $query->get(),
        ], Response::HTTP_OK);
    }

    public function orderStatusCounts(): JsonResponse
    {
        $counts = [];
        foreach (OrderStatus::cases() as $case) {
            $counts[$case->value] = Order::where('status', $case->value)->count();
        }

        return response()->json([
            'success' => true,
            'data' => $counts,
        ], Response::HTTP_OK);
    }
}
