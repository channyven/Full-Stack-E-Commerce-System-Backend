<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ProductController;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->group(function () {
    Route::prefix('v1')->group(function () {
        Route::post('/auth/register', [AuthController::class, 'register']);
        Route::post('/auth/login', [AuthController::class, 'login']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        Route::get('/products', [ProductController::class, 'index']);
        Route::get('/products/search', [ProductController::class, 'search']);
        Route::get('/products/featured', [ProductController::class, 'featured']);
        Route::get('/products/{product:slug}', [ProductController::class, 'showBySlug']);
        Route::get('/products/{product}', [ProductController::class, 'show']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/auth/me', [AuthController::class, 'me']);

            Route::prefix('cart')->group(function () {
                Route::get('/', [\App\Http\Controllers\Api\V1\CartController::class, 'index']);
                Route::post('/', [\App\Http\Controllers\Api\V1\CartController::class, 'store']);
                Route::patch('/items/{cartItem}', [\App\Http\Controllers\Api\V1\CartController::class, 'update']);
                Route::delete('/items/{cartItem}', [\App\Http\Controllers\Api\V1\CartController::class, 'destroy']);
                Route::delete('', [\App\Http\Controllers\Api\V1\CartController::class, 'clear']);
            });

            Route::prefix('wishlist')->group(function () {
                Route::get('/', [\App\Http\Controllers\Api\V1\WishlistController::class, 'index']);
                Route::post('/items', [\App\Http\Controllers\Api\V1\WishlistController::class, 'store']);
                Route::delete('/items/{wishlistItem}', [\App\Http\Controllers\Api\V1\WishlistController::class, 'destroy']);
                Route::post('/items/{wishlistItem}/cart', [\App\Http\Controllers\Api\V1\WishlistController::class, 'moveToCart']);
            });

            Route::prefix('orders')->group(function () {
                Route::get('/', [\App\Http\Controllers\Api\V1\OrderController::class, 'index']);
                Route::post('/checkout', [\App\Http\Controllers\Api\V1\OrderController::class, 'checkout']);
                Route::get('/{order}', [\App\Http\Controllers\Api\V1\OrderController::class, 'show']);
            });

            Route::middleware('admin')->prefix('admin')->group(function () {
                Route::get('/dashboard', [\App\Http\Controllers\Api\V1\Admin\DashboardController::class, 'summary']);
                Route::get('/dashboard/sales', [\App\Http\Controllers\Api\V1\Admin\DashboardController::class, 'sales']);
                Route::get('/dashboard/orders/status-counts', [\App\Http\Controllers\Api\V1\Admin\DashboardController::class, 'orderStatusCounts']);

                Route::apiResource('products', \App\Http\Controllers\Api\V1\Admin\ProductController::class)->names([
                    'index' => 'admin.products.index',
                    'store' => 'admin.products.store',
                    'show' => 'admin.products.show',
                    'update' => 'admin.products.update',
                    'destroy' => 'admin.products.destroy',
                ]);
                Route::patch('orders/{order}/status', [\App\Http\Controllers\Api\V1\Admin\OrderController::class, 'updateStatus']);
                Route::get('orders', [\App\Http\Controllers\Api\V1\Admin\OrderController::class, 'index']);
                Route::get('users', [\App\Http\Controllers\Api\V1\Admin\UserController::class, 'index']);
                Route::patch('users/{user}', [\App\Http\Controllers\Api\V1\Admin\UserController::class, 'update']);
                Route::post('users/{user}/block', [\App\Http\Controllers\Api\V1\Admin\UserController::class, 'toggleBlock']);

                Route::resource('categories', \App\Http\Controllers\Api\V1\Admin\CategoryController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
                Route::resource('coupons', \App\Http\Controllers\Api\V1\Admin\CouponController::class)->only(['index', 'store', 'show', 'update', 'destroy']);

                Route::get('reviews', [\App\Http\Controllers\Api\V1\Admin\ReviewController::class, 'index']);
                Route::patch('reviews/{review}/approve', [\App\Http\Controllers\Api\V1\Admin\ReviewController::class, 'approve']);
                Route::delete('reviews/{review}', [\App\Http\Controllers\Api\V1\Admin\ReviewController::class, 'destroy']);
            });
        });
    });
});
