<?php

use Illuminate\Support\Facades\Route;

Route::middleware('api')->group(function () {
    require base_path('routes/api/v1.php');
});
