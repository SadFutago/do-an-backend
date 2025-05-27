<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\AuthenticateWithJWT;

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::get('me', [AuthController::class, 'me'])->middleware(AuthenticateWithJWT::class);
    Route::post('logout', [AuthController::class, 'logout'])->middleware(AuthenticateWithJWT::class);
    Route::post('refresh', [AuthController::class, 'refresh']);
});
