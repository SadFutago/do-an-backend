<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReviewController;
use App\Http\Middleware\SortMiddleware;
use App\Http\Middleware\AuthenticateWithJWT;

Route::prefix('reviews')->group(function () {
    Route::get('/product/{slug}', [ReviewController::class, 'getByProductSlug'])->middleware(SortMiddleware::class);
    Route::post('/', [ReviewController::class, 'store'])->middleware(AuthenticateWithJWT::class);
});