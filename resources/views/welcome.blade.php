<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\User;

Route::get('/', function () {
    return response()->json([
        'success' => true,
        'message' => 'Ecommerce API is running.',
    ]);
});

Route::get('/email/verify', function () {
    return response()->json([
        'success' => true,
        'message' => 'Verification required.',
    ]);
})->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (Request $request) {

    $user = User::findOrFail($request->id);

    if (! hash_equals(
        (string) $request->hash,
        sha1($user->getEmailForVerification())
    )) {
        abort(403);
    }

    if (! $user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
    }

    return response()->json([
        'success' => true,
        'message' => 'Email verified successfully.',
    ]);
})->middleware('signed')->name('verification.verify');
