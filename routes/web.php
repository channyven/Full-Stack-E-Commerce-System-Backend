<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use App\Models\User;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/email/verify', function () {
    return response()->json([
        'success' => true,
        'message' => 'Verification required.',
    ]);
})->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
    $user = User::query()->findOrFail($id);

    if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
        abort(403);
    }

    if (! $user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
    }

    return response()->json([
        'success' => true,
        'message' => 'Email verified.',
    ]);
})->middleware('signed')->name('verification.verify');

Route::middleware(['web', 'auth:sanctum', 'admin'])->prefix('v1/api/v1')->group(function () {
    Route::get('/admin/dashboard', [\App\Http\Controllers\Api\V1\Admin\DashboardController::class, 'summary']);

    Route::prefix('products')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\V1\Admin\ProductController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Api\V1\Admin\ProductController::class, 'store']);
        Route::get('/{product}', [\App\Http\Controllers\Api\V1\Admin\ProductController::class, 'show']);
        Route::put('/{product}', [\App\Http\Controllers\Api\V1\Admin\ProductController::class, 'update']);
        Route::delete('/{product}', [\App\Http\Controllers\Api\V1\Admin\ProductController::class, 'destroy']);
    });

    Route::prefix('orders')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\V1\Admin\OrderController::class, 'index']);
        Route::get('/{order}', [\App\Http\Controllers\Api\V1\Admin\OrderController::class, 'show']);
        Route::patch('/{order}', [\App\Http\Controllers\Api\V1\Admin\OrderController::class, 'update']);
    });

    Route::prefix('users')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\V1\Admin\UserController::class, 'index']);
        Route::patch('/{user}', [\App\Http\Controllers\Api\V1\Admin\UserController::class, 'update']);
    });

    Route::prefix('categories')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\V1\Admin\CategoryController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Api\V1\Admin\CategoryController::class, 'store']);
        Route::get('/{category}', [\App\Http\Controllers\Api\V1\Admin\CategoryController::class, 'show']);
        Route::put('/{category}', [\App\Http\Controllers\Api\V1\Admin\CategoryController::class, 'update']);
        Route::delete('/{category}', [\App\Http\Controllers\Api\V1\Admin\CategoryController::class, 'destroy']);
    });

    Route::prefix('coupons')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\V1\Admin\CouponController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Api\V1\Admin\CouponController::class, 'store']);
        Route::get('/{coupon}', [\App\Http\Controllers\Api\V1\Admin\CouponController::class, 'show']);
        Route::put('/{coupon}', [\App\Http\Controllers\Api\V1\Admin\CouponController::class, 'update']);
        Route::delete('/{coupon}', [\App\Http\Controllers\Api\V1\Admin\CouponController::class, 'destroy']);
    });

    Route::prefix('reviews')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\V1\Admin\ReviewController::class, 'index']);
        Route::patch('/{review}/approve', [\App\Http\Controllers\Api\V1\Admin\ReviewController::class, 'approve']);
        Route::delete('/{review}', [\App\Http\Controllers\Api\V1\Admin\ReviewController::class, 'destroy']);
    });
});

